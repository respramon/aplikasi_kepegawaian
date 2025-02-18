<?php
session_start();
include 'db.php';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Menggunakan prepared statement untuk keamanan
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['id_user'] = $user['id'];  // Simpan session
            $_SESSION['role'] = $user['role'];  // Simpan role (admin atau pegawai)

            if ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");  // Redirect ke dashboard admin
            } else {
                header("Location: dashboard.php");  // Redirect ke dashboard pegawai
            }
            exit();
        } else {
            $error_message = "Password salah!";
        }
    } else {
        $error_message = "Email tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pegawai</title>
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

        input {
            width: 90%;
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
            text-align: center;
            position: relative;
            animation: fadeOut 4s forwards;
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }
            100% {
                opacity: 0;
            }
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
            <h1>Selamat Datang Admin</h1>
            <p>Kelola data pegawai dengan mudah dan efisien</p>
        </div>

        <div class="form-section">
            <div class="form-wrapper">
                <div class="form-card">
                    <h2 class="form-title">Masuk ke Akun Anda</h2>

                    <!-- Display error message -->
                    <?php if (!empty($error_message)): ?>
                        <div class="error-message"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="login_admin.php">
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

                        <button type="submit">Masuk Sekarang</button>
                    </form>

                    <div class="switch-link">
                        Belum punya akun? <a href="register_admin.php">Daftar disini</a>
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
            }, 1000); // 5 seconds before fading out
        }
    </script>
</body>
</html>
