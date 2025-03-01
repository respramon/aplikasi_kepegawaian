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

$error_message = "";

// Cek apakah pengguna sudah login
if (!isset($_SESSION['id_pegawai'])) {
    header("Location: index.php");
    exit();
}

// Ambil data pegawai dari database berdasarkan session
$id_pegawai = $_SESSION['id_pegawai'];
$sql = "SELECT * FROM pegawai WHERE id = '$id_pegawai'";
$result = $conn->query($sql);
$pegawai = $result->fetch_assoc();

// Ambil rekap kehadiran pegawai hari ini
$tanggal = date('Y-m-d');
$sql_kehadiran = "SELECT * FROM kehadiran WHERE id_pegawai = '$id_pegawai' AND tanggal = '$tanggal'";
$result_kehadiran = $conn->query($sql_kehadiran);
$kehadiran = $result_kehadiran->fetch_assoc();

// Ambil rekap kehadiran pegawai (5 hari terakhir)
$sql_kehadiran_all = "SELECT * FROM kehadiran WHERE id_pegawai = '$id_pegawai' ORDER BY tanggal DESC LIMIT 5";
$result_kehadiran_all = $conn->query($sql_kehadiran_all);

// Ambil data izin terakhir
$sql_izin = "SELECT * FROM izin WHERE id_pegawai = '$id_pegawai' ORDER BY tanggal_pengajuan DESC LIMIT 3";
$result_izin = $conn->query($sql_izin);

$success_message = isset($_SESSION['password_update_success']) ? $_SESSION['password_update_success'] : '';

// Hapus pesan setelah ditampilkan
unset($_SESSION['password_update_success']);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pegawai - Aplikasi Kepegawaian</title>
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
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            display: flex;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            padding: 2rem;
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
            font-size: 1.5rem;
        }

        section {
            margin-bottom: 2rem;
        }

        h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .profile, .kehadiran, .rekap, .izin {
            background-color: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .profile p, .kehadiran p {
            margin-bottom: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        table th {
            background-color: var(--primary-color);
            color: white;
        }

        .error-message {
            background-color: #f8d7da;
            color: var(--error-color);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        /* Responsif */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            header {
                flex-direction: column;
                gap: 1rem;
            }

            table th, table td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h2>Selamat datang, <?= $pegawai['nama']; ?>!</h2>
            <nav>
                <ul>
                    <li><a href="update.php">Update Profil</a></li>
                    <li><a href="cuti.php">Ajukan Cuti</a></li>
                    <li><a href="update_password.php">Ganti Password</a></li>
		    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?= $error_message; ?></div>
        <?php endif; ?>

        <section class="profile">
            <h3>Profil Saya</h3>
            <p><strong>Nama:</strong> <?= $pegawai['nama']; ?></p>
            <p><strong>Email:</strong> <?= $pegawai['email']; ?></p>
            <p><strong>Jabatan:</strong> <?= $pegawai['jabatan']; ?></p>
            <p><strong>Alamat:</strong> <?= $pegawai['alamat']; ?></p>
            <p><strong>Telepon:</strong> <?= $pegawai['telepon']; ?></p>
        </section>

        <section class="kehadiran">
            <h3>Status Kehadiran Hari Ini</h3>
            <?php if ($kehadiran): ?>
                <p><strong>Status:</strong> <?= $kehadiran['status_izin']; ?></p>
                <p><strong>Waktu Masuk:</strong> <?= $kehadiran['waktu_masuk']; ?></p>
                <?php if ($kehadiran['waktu_keluar']): ?>
                    <p><strong>Waktu Keluar:</strong> <?= $kehadiran['waktu_keluar']; ?></p>
                <?php else: ?>
                    <p><strong>Anda belum check-out!</strong></p>
                    <a href="checkout.php">Check-out</a>
                <?php endif; ?>
            <?php else: ?>
                <p><strong>Anda belum melakukan check-in hari ini.</strong></p>
                <a href="checkin.php">Check-in</a>
            <?php endif; ?>
        </section>

        <section class="rekap">
            <h3>Rekap Kehadiran Terakhir</h3>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Status Kehadiran</th>
                        <th>Waktu Masuk</th>
                        <th>Waktu Keluar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_kehadiran_all->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['tanggal']; ?></td>
                            <td><?= $row['status_izin']; ?></td>
                            <td><?= $row['waktu_masuk']; ?></td>
                            <td><?= $row['waktu_keluar']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <section class="izin">
            <h3>Izin Terakhir</h3>
            <table>
                <thead>
                    <tr>
                        <th>Tipe Izin</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_izin->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['tipe_izin']; ?></td>
                            <td><?= $row['tanggal_mulai']; ?></td>
                            <td><?= $row['tanggal_selesai']; ?></td>
                            <td><?= $row['status']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
