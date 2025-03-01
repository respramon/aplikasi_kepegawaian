<?php
session_start();
include 'db.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_pegawai = $_GET['id'];

    $sql = "SELECT * FROM pegawai WHERE id = '$id_pegawai'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $pegawai = $result->fetch_assoc();
    } else {
        echo "Pegawai tidak ditemukan!";
        exit();
    }
} else {
    echo "ID pegawai tidak ditemukan!";
    exit();
}

$error_message = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data lama sebelum diupdate
    $sql_old = "SELECT * FROM pegawai WHERE id = '$id_pegawai'";
    $result_old = $conn->query($sql_old);
    $old_data = $result_old->fetch_assoc();

    // Data baru dari form
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $jabatan = $_POST['jabatan'];
    $alamat = $_POST['alamat'];
    $telepon = $_POST['telepon'];

    // Update data pegawai
    $sql_update = "UPDATE pegawai SET
                    nama = '$nama',
                    email = '$email',
                    jabatan = '$jabatan',
                    alamat = '$alamat',
                    telepon = '$telepon'
                    WHERE id = '$id_pegawai'";

    if ($conn->query($sql_update) === TRUE) {
        // Ambil data baru setelah diupdate
        $sql_new = "SELECT * FROM pegawai WHERE id = '$id_pegawai'";
        $result_new = $conn->query($sql_new);
        $new_data = $result_new->fetch_assoc();

        // Bandingkan setiap field dan catat perubahan
        $fields = ['nama', 'email', 'jabatan', 'alamat', 'telepon'];
        foreach ($fields as $field) {
            if ($old_data[$field] != $new_data[$field]) {
                // Escape string untuk keamanan
                $nilai_sebelum = $conn->real_escape_string($old_data[$field]);
                $nilai_baru = $conn->real_escape_string($new_data[$field]);
                
                // Insert ke log perubahan
                $log_sql = "INSERT INTO log_perubahan (id_pegawai, field_yang_diubah, nilai_sebelumnya, nilai_baru)
                            VALUES ('$id_pegawai', '$field', '$nilai_sebelum', '$nilai_baru')";
                $conn->query($log_sql);
            }
        }

        $success_message = "Data pegawai berhasil diperbarui!";
        header("Refresh: 2; URL=admin_dashboard.php");
    } else {
        $error_message = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pegawai</title>
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
            <h1>Edit Data Pegawai</h1>
        </div>

        <div class="nav-bar">
            <a href="admin_dashboard.php">Dashboard Admin</a>
            <a href="login_admin.php">Logout</a>
        </div>

        <div class="form-section">
            <div class="form-wrapper">
                <div class="form-card">
                    <h2 class="form-title">Form Edit Pegawai</h2>

                    <?php if (!empty($error_message)): ?>
                        <div class="error-message"><?php echo $error_message; ?></div>
                    <?php elseif (!empty($success_message)): ?>
                        <div class="success-message"><?php echo $success_message; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="nama">Nama</label>
                            <input type="text" id="nama" name="nama" value="<?= $pegawai['nama']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= $pegawai['email']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="jabatan">Jabatan</label>
                            <input type="text" id="jabatan" name="jabatan" value="<?= $pegawai['jabatan']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="alamat">Alamat</label>
                            <textarea id="alamat" name="alamat" required><?= $pegawai['alamat']; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="telepon">Telepon</label>
                            <input type="text" id="telepon" name="telepon" value="<?= $pegawai['telepon']; ?>" required>
                        </div>

                        <button type="submit">Perbarui Data Pegawai</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
