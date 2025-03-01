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

session_start();
include 'db.php';

// Cek jika user sudah login
if (!isset($_SESSION['id_pegawai'])) {
    header("Location: index.php"); // Arahkan ke halaman login jika belum login
    exit(); // Pastikan eksekusi dihentikan setelah redirect
}

$error_message = "";
$success_message = "";

$id_pegawai = $_SESSION['id_pegawai'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pembaruan Profil
    if (isset($_POST['update_profil'])) {
        $nama = htmlspecialchars($_POST['nama'], ENT_QUOTES, 'UTF-8'); // Sanitasi nama
        $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8'); // Sanitasi email
        $jabatan = htmlspecialchars($_POST['jabatan'], ENT_QUOTES, 'UTF-8'); // Sanitasi jabatan
        $alamat = htmlspecialchars($_POST['alamat'], ENT_QUOTES, 'UTF-8'); // Sanitasi alamat
        $telepon = htmlspecialchars($_POST['telepon'], ENT_QUOTES, 'UTF-8'); // Sanitasi telepon

        // Fetch current data before updating for logging
        $sql_fetch = "SELECT * FROM pegawai WHERE id=?";
        $stmt_fetch = $conn->prepare($sql_fetch);
        $stmt_fetch->bind_param("i", $id_pegawai);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();
        $current_data = $result_fetch->fetch_assoc();
        $stmt_fetch->close();

        // Update query untuk profil
        $sql = "UPDATE pegawai SET nama=?, email=?, jabatan=?, alamat=?, telepon=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $nama, $email, $jabatan, $alamat, $telepon, $id_pegawai);

        if ($stmt->execute()) {
            // Log perubahan data profil
            $fields = ['nama', 'email', 'jabatan', 'alamat', 'telepon'];
            foreach ($fields as $field) {
                if ($current_data[$field] != $$field) {
                    $log_sql = "INSERT INTO log_perubahan (id_pegawai, field_yang_diubah, nilai_sebelumnya, nilai_baru)
                                VALUES (?, ?, ?, ?)";
                    $stmt_log = $conn->prepare($log_sql);
                    $stmt_log->bind_param("isss", $id_pegawai, $field, $current_data[$field], $$field);
                    $stmt_log->execute();
                    $stmt_log->close();
                }
            }
            $success_message = "Profil berhasil diperbarui!";
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetching the employee data
$sql = "SELECT * FROM pegawai WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_pegawai);
$stmt->execute();
$result = $stmt->get_result();
$pegawai = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profil Pegawai</title>
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
            display: flex;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .container {
            flex: 1;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            padding: 2rem;
        }

        .header {
            background-color: #34495e;
            color: white;
            padding: 1rem;
            text-align: center;
            border-radius: 8px;
        }

        .nav-bar {
            display: flex;
            justify-content: center;
            background-color: #2c3e50;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .nav-bar a {
            color: white;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            margin: 0 1rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: background-color 0.3s ease;
        }

        .nav-bar a:hover {
            background-color: #3498db;
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

        input, textarea {
            width: 90%;
            padding: 0.875rem;
            border: 2px solid #e0e0e0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus, textarea:focus {
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

            .nav-bar {
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
        <div class="header">
            <h2>Update Profil Pegawai</h2>
        </div>

        <div class="nav-bar">
            <a href="dashboard.php">Dashboard</a>
            <a href="cuti.php">Ajukan Cuti</a>
            <a href="update_password.php">Ganti Password</a>
            <a href="logout.php">Logout</a>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php elseif (!empty($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <!-- Form Pembaruan Profil -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($pegawai['nama'], ENT_QUOTES, 'UTF-8') ?>" required><br>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($pegawai['email'], ENT_QUOTES, 'UTF-8') ?>" required><br>
            </div>

            <div class="form-group">
                <label for="jabatan">Jabatan</label>
                <select id="jabatan" name="jabatan" required>
		    <option value=""disabled selected><?= $pegawai['jabatan']; ?></option>
                    <option value="Golongan 1 A">Golongan 1 A</option>
                    <option value="Golongan 1 B">Golongan 1 B</option>
                    <option value="Golongan 1 C">Golongan 1 C</option>
                    <option value="Golongan 1 D">Golongan 1 D</option>
                    <option value="Golongan 2 A">Golongan 2 A</option>
                    <option value="Golongan 2 B">Golongan 2 B</option>
                    <option value="Golongan 2 C">Golongan 2 C</option>
                    <option value="Golongan 2 D">Golongan 2 D</option>
                    <option value="Golongan 3 A">Golongan 3 A</option>
                    <option value="Golongan 3 B">Golongan 3 B</option>
                    <option value="Golongan 3 C">Golongan 3 C</option>
                    <option value="Golongan 3 D">Golongan 3 D</option>
                    <option value="Golongan 4 A">Golongan 4 A</option>
                    <option value="Golongan 4 B">Golongan 4 B</option>
                    <option value="Golongan 4 C">Golongan 4 C</option>
                    <option value="Golongan 4 D">Golongan 4 D</option>
                </select>
            </div>

            <div class="form-group">
                <label for="alamat">Alamat</label>
                <textarea id="alamat" name="alamat" required><?= htmlspecialchars($pegawai['alamat'], ENT_QUOTES, 'UTF-8') ?></textarea><br>
            </div>

            <div class="form-group">
                <label for="telepon">Telepon</label>
                <input type="text" id="telepon" name="telepon" value="<?= htmlspecialchars($pegawai['telepon'], ENT_QUOTES, 'UTF-8') ?>" required><br>
            </div>

            <button type="submit" name="update_profil">Update Profil</button>
        </form>
    </div>
</body>
</html>
