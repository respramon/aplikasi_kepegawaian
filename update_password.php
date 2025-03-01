<?php
// Cek apakah koneksi menggunakan HTTPS
if ($_SERVER['HTTPS'] !== 'on') {
    $redirect_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: " . $redirect_url);
    exit(); // Menghentikan eksekusi lebih lanjut
}

// Set parameter cookie sesi yang lebih aman
session_set_cookie_params([
    'secure' => true,       // Pastikan hanya dikirim melalui HTTPS
    'httponly' => true,     // Cegah akses melalui JavaScript
    'samesite' => 'Strict', // Cegah pengiriman cookie lintas situs
]);

// Jika token CSRF belum ada di session, buat token baru
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

session_start();
include 'db.php';

$error_message = "";
$success_message = "";
$password_error_message = "";

$id_pegawai = $_SESSION['id_pegawai'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifikasi token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Token CSRF tidak valid!');
    }

    // Penggantian Password
    if (isset($_POST['update_password'])) {
        $password_lama = $_POST['password_lama'];
        $password_baru = $_POST['password_baru'];
        $password_baru_konfirmasi = $_POST['password_baru_konfirmasi'];

        // Validasi: Pastikan password baru memenuhi syarat minimal (misalnya 8 karakter)
        if (strlen($password_baru) < 8) {
            $password_error_message = "Password baru harus memiliki minimal 8 karakter!";
        } elseif ($password_baru !== $password_baru_konfirmasi) {
            $password_error_message = "Password baru dan konfirmasi password tidak cocok!";
        } else {
            // Fetch current data before updating for logging
            $sql_fetch = "SELECT * FROM pegawai WHERE id=?";
            $stmt_fetch = $conn->prepare($sql_fetch);
            $stmt_fetch->bind_param("i", $id_pegawai);
            $stmt_fetch->execute();
            $result_fetch = $stmt_fetch->get_result();
            $current_data = $result_fetch->fetch_assoc();
            $stmt_fetch->close();

            // Check if the old password matches the stored password
            if (!password_verify($password_lama, $current_data['password'])) {
                $password_error_message = "Password lama yang dimasukkan tidak cocok!";
            } else {
                // Password baru akan disimpan dalam format terenkripsi
                $password = password_hash($password_baru, PASSWORD_DEFAULT);

                // Update password
                $sql_update_password = "UPDATE pegawai SET password=? WHERE id=?";
                $stmt_password = $conn->prepare($sql_update_password);
                $stmt_password->bind_param("si", $password, $id_pegawai);

                if ($stmt_password->execute()) {
                    // Log perubahan password
                    $log_sql = "INSERT INTO log_perubahan (id_pegawai, field_yang_diubah, nilai_sebelumnya, nilai_baru)
                                VALUES (?, 'password', ?, ?)";
                    $stmt_log = $conn->prepare($log_sql);
                    // Catat password lama dan baru
                    $stmt_log->bind_param("iss", $id_pegawai, $current_data['password'], $password);
                    $stmt_log->execute();
                    $stmt_log->close();

                    $success_message = "Password berhasil diperbarui!";
                } else {
                    $password_error_message = "Error: " . $stmt_password->error;
                }
                $stmt_password->close();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password Pegawai</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --error-color: #e74c3c;
            --success-color: #28a745;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            display: flex;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            padding: 2rem;
            flex-grow: 1;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--primary-color);
            color: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 15px;
        }

        nav ul li a {
            text-decoration: none;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background 0.3s;
        }

        nav ul li a:hover {
            background-color: #2980b9;
        }

        h2 {
            font-size: 1.8rem;
        }

        .form-section {
            padding: 2rem;
            display: flex;
            justify-content: center;
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .form-wrapper {
            width: 90%;
            max-width: 600px;
        }

        .form-card {
            background: white;
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .form-title {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 2rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #636363;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e0e0e0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        button {
            width: 100%;
            padding: 1rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            header {
                flex-direction: column;
                gap: 1rem;
            }

            .form-section {
                flex-direction: column;
            }

            .form-card {
                padding: 1.5rem;
            }
        }

    </style>
</head>
<body>
    <div class="container">
        <header>
            <h2>Ganti Password</h2>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="update.php">Update Profil</a></li>
                    <li><a href="cuti.php">Ajukan Cuti</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>

        <?php if (!empty($password_error_message)): ?>
            <div class="error-message"><?php echo $password_error_message; ?></div>
        <?php elseif (!empty($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <!-- Form Penggantian Password -->
        <div class="form-section">
            <div class="form-wrapper">
                <div class="form-card">
                    <form method="POST" action="">
                        <!-- CSRF Token Field -->
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />

                        <div class="form-group">
                            <label for="password_lama">Password Lama</label>
                            <input type="password" id="password_lama" name="password_lama" placeholder="Masukkan password lama" required>
                        </div>

                        <div class="form-group">
                            <label for="password_baru">Password Baru</label>
                            <input type="password" id="password_baru" name="password_baru" placeholder="Masukkan password baru" required>
                        </div>

                        <div class="form-group">
                            <label for="password_baru_konfirmasi">Konfirmasi Password Baru</label>
                            <input type="password" id="password_baru_konfirmasi" name="password_baru_konfirmasi" placeholder="Konfirmasi password baru" required>
                        </div>

                        <button type="submit" name="update_password">Ganti Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
