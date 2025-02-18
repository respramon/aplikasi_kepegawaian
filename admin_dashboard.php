<?php
session_start();
include 'db.php';

// Cek apakah pengguna adalah admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: login_admin.php");
    exit();
}

// Ambil data pegawai dan izin yang perlu disetujui
$sql_izin = "SELECT * FROM izin WHERE status = 'Diajukan'";
$result_izin = $conn->query($sql_izin);

// Ambil semua data pegawai
$sql_pegawai = "SELECT * FROM pegawai";
$result_pegawai = $conn->query($sql_pegawai);

$error_message = "";
$success_message = "";

// Update password untuk admin
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_admin_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $new_password_confirm = $_POST['new_password_confirm'];

    // Cek jika password baru dan konfirmasi password baru cocok
    if ($new_password !== $new_password_confirm) {
        $error_message = "Password baru dan konfirmasi password tidak cocok!";
    } else {
        // Cek apakah old password benar
        $admin_id = $_SESSION['id_user'];
        $sql = "SELECT password FROM users WHERE id = '$admin_id'";
        $result = $conn->query($sql);
        $user = $result->fetch_assoc();

        if (password_verify($old_password, $user['password'])) {
            // Update password
            $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET password = '$new_password_hashed' WHERE id = '$admin_id'";

            if ($conn->query($update_sql) === TRUE) {
                $success_message = "Password admin berhasil diperbarui!";
            } else {
                $error_message = "Terjadi kesalahan saat memperbarui password admin.";
            }
        } else {
            $error_message = "Password lama admin salah!";
        }
    }
}

// Update password untuk pegawai
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_pegawai_password'])) {
    $pegawai_id = $_POST['pegawai_id'];  // ID pegawai yang akan diubah passwordnya
    $new_password = $_POST['pegawai_new_password'];
    $new_password_confirm = $_POST['pegawai_new_password_confirm'];

    // Cek jika password baru dan konfirmasi password baru cocok
    if ($new_password !== $new_password_confirm) {
        $error_message = "Password baru dan konfirmasi password pegawai tidak cocok!";
    } else {
        // Update password pegawai
        $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE pegawai SET password = '$new_password_hashed' WHERE id = '$pegawai_id'";

        if ($conn->query($update_sql) === TRUE) {
            $success_message = "Password pegawai berhasil diperbarui!";
        } else {
            $error_message = "Terjadi kesalahan saat memperbarui password pegawai.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <style>
        /* Styling Code (as in previous example, kept for brevity) */
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --error-color: #e74c3c;
            --background-color: #f4f7fc;
            --button-hover-color: #2980b9;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            background-color: var(--background-color);
        }

        .container {
            flex: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: center;
        }

        header h2 {
            margin: 0;
        }

        nav ul {
            list-style: none;
            padding: 0;
            text-align: center;
        }

        nav ul li {
            display: inline;
            margin-right: 20px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        nav ul li a:hover {
            text-decoration: underline;
        }

        section {
            margin-top: 30px;
        }

        h3 {
            font-size: 1.5rem;
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: var(--primary-color);
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        a {
            text-decoration: none;
            color: var(--primary-color);
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        .button {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .button:hover {
            background-color: var(--button-hover-color);
        }

        .error-message, .success-message {
            background: #f8d7da;
            color: var(--error-color);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #f5c6cb;
            text-align: center;
            position: relative;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            section {
                margin-top: 20px;
            }
        }

    </style>
</head>
<body>
    <div class="container">
        <header>
            <h2>Dashboard Admin</h2>
            <nav>
                <ul>
                    <li><a href="logout_admin.php">Logout</a></li>
                </ul>
            </nav>
        </header>

        <!-- Display error or success messages -->
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php elseif (!empty($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <!-- Pengajuan Izin Section -->
        <section>
            <h3>Pengajuan Izin yang Perlu Disetujui</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID Pegawai</th>
                        <th>Nama Pegawai</th>
                        <th>Tipe Izin</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
			<th>Keterangan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_izin->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id_pegawai']; ?></td>
                        <td><?= get_pegawai_name($row['id_pegawai'], $conn); ?></td>
                        <td><?= $row['tipe_izin']; ?></td>
                        <td><?= $row['tanggal_mulai']; ?></td>
                        <td><?= $row['tanggal_selesai']; ?></td>
                        <td><?= $row['keterangan']; ?></td>
			<td><?= $row['status']; ?></td>
                        <td>
                            <a href="approve_izin.php?id=<?= $row['id']; ?>" class="button">Setujui</a>
                            <a href="reject_izin.php?id=<?= $row['id']; ?>" class="button">Tolak</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Edit Data Pegawai Section -->
        <section>
            <h3>Edit Data Pegawai</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID Pegawai</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Jabatan</th>
			<th>Alamat</th>
			<th>Telepon</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_pegawai->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= $row['nama']; ?></td>
                        <td><?= $row['email']; ?></td>
                        <td><?= $row['jabatan']; ?></td>
			<td><?= $row['alamat']; ?></td>
			<td><?= $row['telepon']; ?></td>
                        <td>
                            <a href="edit_pegawai.php?id=<?= $row['id']; ?>" class="button">Edit</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Change Password Section for Admin -->
        <section>
            <h3>Ubah Password Admin</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="old_password">Password Lama</label>
                    <input type="password" name="old_password" id="old_password" required placeholder="Masukkan password lama">
                </div>

                <div class="form-group">
                    <label for="new_password">Password Baru</label>
                    <input type="password" name="new_password" id="new_password" required placeholder="Masukkan password baru">
                </div>

                <div class="form-group">
                    <label for="new_password_confirm">Konfirmasi Password Baru</label>
                    <input type="password" name="new_password_confirm" id="new_password_confirm" required placeholder="Konfirmasi password baru">
                </div>

                <button type="submit" name="update_admin_password" class="button">Ubah Password</button>
            </form>
        </section>

        <!-- Change Password Section for Employee -->
        <section>
            <h3>Ubah Password Pegawai</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="pegawai_id">ID Pegawai</label>
                    <input type="text" name="pegawai_id" id="pegawai_id" required placeholder="Masukkan ID Pegawai">
                </div>

                <div class="form-group">
                    <label for="pegawai_new_password">Password Baru</label>
                    <input type="password" name="pegawai_new_password" id="pegawai_new_password" required placeholder="Masukkan password baru">
                </div>

                <div class="form-group">
                    <label for="pegawai_new_password_confirm">Konfirmasi Password Baru</label>
                    <input type="password" name="pegawai_new_password_confirm" id="pegawai_new_password_confirm" required placeholder="Konfirmasi password baru">
                </div>

                <button type="submit" name="update_pegawai_password" class="button">Ubah Password Pegawai</button>
            </form>
        </section>
    </div>
</body>
</html>

<?php
// Fungsi untuk mendapatkan nama pegawai berdasarkan ID
function get_pegawai_name($id_pegawai, $conn) {
    $sql = "SELECT nama FROM pegawai WHERE id = '$id_pegawai'";
    $result = $conn->query($sql);
    $pegawai = $result->fetch_assoc();
    return $pegawai['nama'];
}
