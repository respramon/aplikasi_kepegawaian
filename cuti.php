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

// Jika token CSRF belum ada di session, buat token baru
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifikasi token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Token CSRF tidak valid!');
    }

    $id_pegawai = $_SESSION['id_pegawai'];
    $tipe_izin = $_POST['tipe_izin'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $keterangan = $_POST['keterangan'];

    // Prepared statement to avoid SQL injection
    $sql = "INSERT INTO izin (id_pegawai, tipe_izin, tanggal_mulai, tanggal_selesai, keterangan)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $id_pegawai, $tipe_izin, $tanggal_mulai, $tanggal_selesai, $keterangan);

    if ($stmt->execute()) {
        $success_message = "Izin berhasil diajukan!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Izin</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --error-color: #e74c3c;
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
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            text-align: center;
            border-radius: 8px;
        }

        .nav-bar {
            display: flex;
            justify-content: center;
            background-color: var(--primary-color);
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
            background-color: #2980b9;
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
            width: 100%;
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

        input, textarea, select {
            width: 90%;
            padding: 0.875rem;
            border: 2px solid #e0e0e0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus, textarea:focus, select:focus {
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

        .error-message, .success-message {
            background: #f8d7da;
            color: var(--error-color);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #f5c6cb;
            position: relative;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Responsive design */
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
        <!-- Header -->
        <div class="header">
            <h1>Selamat Datang di Sistem Kepegawaian</h1>
        </div>

        <!-- Navigation Bar -->
        <div class="nav-bar">
            <a href="dashboard.php">Dashboard</a>
            <a href="update.php">Update Profil</a>
            <a href="update_password.php">Ganti Password</a>
            <a href="logout.php">Logout</a>
        </div>

        <!-- Form Section -->
        <div class="form-section">
            <div class="form-wrapper">
                <div class="form-card">
                    <h2 class="form-title">Ajukan Izin Anda</h2>

                    <!-- Display messages -->
                    <?php if (!empty($error_message)): ?>
                        <div class="error-message"><?php echo $error_message; ?></div>
                    <?php elseif (!empty($success_message)): ?>
                        <div class="success-message"><?php echo $success_message; ?></div>
                    <?php endif; ?>

                    <!-- Form to submit leave request -->
                    <form method="POST" action="">
                        <!-- CSRF Token Field -->
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />

                        <div class="form-group">
                            <label for="tipe_izin">Tipe Izin</label>
                            <select id="tipe_izin" name="tipe_izin" required>
                                <option value="Cuti">Cuti</option>
                                <option value="Sakit">Sakit</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="tanggal_mulai">Tanggal Mulai</label>
                            <input type="date" id="tanggal_mulai" name="tanggal_mulai" required>
                        </div>

                        <div class="form-group">
                            <label for="tanggal_selesai">Tanggal Selesai</label>
                            <input type="date" id="tanggal_selesai" name="tanggal_selesai" required>
                        </div>

                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea id="keterangan" name="keterangan" required></textarea>
                        </div>

                        <button type="submit">Ajukan Izin</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
