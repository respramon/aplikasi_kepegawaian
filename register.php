<?php
// Set parameter cookie sesi yang lebih aman
session_set_cookie_params([
    'secure' => true,       // Pastikan hanya dikirim melalui HTTPS
    'httponly' => true,     // Cegah akses melalui JavaScript
    'samesite' => 'Strict', // Cegah pengiriman cookie lintas situs
]);

// Cek apakah koneksi menggunakan HTTPS, jika tidak alihkan ke HTTPS
if ($_SERVER['HTTPS'] !== 'on') {
    $redirect_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: " . $redirect_url);
    exit(); // Menghentikan eksekusi lebih lanjut
}

session_start();
include 'db.php';

// Jika token CSRF belum ada di session, buat token baru
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifikasi token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Token CSRF tidak valid!');
    }

    // Ambil data dari form dan sanitasi
    $nama = htmlspecialchars(trim($_POST['nama']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; // Ambil konfirmasi password
    $jabatan = htmlspecialchars(trim($_POST['jabatan']));
    $alamat = htmlspecialchars(trim($_POST['alamat']));
    $telepon = htmlspecialchars(trim($_POST['telepon']));

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Email tidak valid!";
    }
    // Validasi password panjang minimal 8 karakter
    elseif (strlen($password) < 8) {
        $error_message = "Password harus memiliki minimal 8 karakter!";
    }
    // Validasi password dan konfirmasi password
    elseif ($password !== $confirm_password) {
        $error_message = "Password dan konfirmasi password tidak cocok!";
    } else {
        // Cek apakah email sudah terdaftar
        $sql = "SELECT * FROM pegawai WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Email sudah terdaftar!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert data pegawai ke database
            $sql = "INSERT INTO pegawai (nama, email, password, jabatan, alamat, telepon) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $nama, $email, $hashed_password, $jabatan, $alamat, $telepon);

            if ($stmt->execute()) {
                header("Location: index.php");
                exit();
            } else {
                $error_message = "Gagal registrasi! Silakan coba lagi.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pegawai</title>
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
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2980b9 100%);
            color: white;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .form-section {
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            background: white;
        }

        .form-wrapper {
            width: 100%;
            max-width: 400px;
        }

        .logo {
            width: 120px;
            margin-bottom: 2rem;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .form-card {
            background: white;
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
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
            border-color: var(--secondary-color);
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

        .switch-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #636363;
        }

        .switch-link a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .error-message {
            background: #f8d7da;
            color: var(--error-color);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #f5c6cb;
            position: relative;
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }

            .hero-section {
                display: none;
            }

            .form-section {
                padding: 1.5rem;
            }

            .form-card {
                padding: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .form-title {
                font-size: 1.5rem;
            }

            input {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero-section">
            <h1>Selamat Datang di Sistem Kepegawaian</h1>
            <p>Kelola data pegawai dengan mudah dan efisien</p>
        </div>

        <div class="form-section">
            <div class="form-wrapper">
                <div class="form-card">
                    <h2 class="form-title">Daftar Akun Baru</h2>

                    <?php if (!empty($error_message)): ?>
                        <div class="error-message"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="register.php">
                        <!-- CSRF Token Field -->
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />

                        <div class="form-group">
                            <label for="nama">Nama</label>
                            <input
                                type="text"
                                id="nama"
                                name="nama"
                                required
                                placeholder="Nama Lengkap"
                            >
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                required
                                placeholder="contoh@perusahaan.com"
                            >
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                placeholder="Masukkan password"
                            >
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password</label>
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                required
                                placeholder="Konfirmasi password"
                            >
                        </div>

                        <div class="form-group">
                            <label for="jabatan">Jabatan</label>
			    <select id="jabatan" name="jabatan" required>
        			<option value="" disabled selected>Pilih Jabatan Anda</option>
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
                            <textarea
                                id="alamat"
                                name="alamat"
                                required
                                placeholder="Alamat Lengkap"
                            ></textarea>
                        </div>

                        <div class="form-group">
                            <label for="telepon">Telepon</label>
                            <input
                                type="text"
                                id="telepon"
                                name="telepon"
                                required
                                placeholder="Nomor Telepon"
                            >
                        </div>

                        <button type="submit">Daftar Sekarang</button>
                    </form>

                    <div class="switch-link">
                        Sudah punya akun? <a href="index.php">Login disini</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const errorMessage = document.querySelector('.error-message');
        if (errorMessage) {
            setTimeout(() => {
                errorMessage.style.opacity = '0';
                setTimeout(() => {
                    errorMessage.remove();
                }, 500);
            }, 1000);
        }
    </script>
</body>
</html>

