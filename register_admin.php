<?php
session_start();
include 'db.php';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi konfirmasi password
    if ($password !== $confirm_password) {
        $error_message = "Password dan konfirmasi password tidak cocok!";
    } else {
        // Validasi email
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Email sudah terdaftar!";
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert data admin
            $sql_insert = "INSERT INTO users (email, password, role) VALUES (?, ?, 'admin')";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ss", $email, $password_hash);

            if ($stmt_insert->execute()) {
                header("Location: login_admin.php");
                exit();
            } else {
                $error_message = "Gagal mendaftarkan admin: " . $conn->error;
            }
            $stmt_insert->close();
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
    <title>Registrasi Admin</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="hero-section">
            <h1>Registrasi Admin</h1>
            <p>Sistem Manajemen Administrasi</p>
        </div>

        <div class="form-section">
            <div class="form-wrapper">
                <div class="form-card">
                    <h2 class="form-title">Buat Akun Admin</h2>

                    <?php if (!empty($error_message)): ?>
                        <div class="error-message"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                required
                                placeholder="admin@example.com"
                            >
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                placeholder="••••••••"
                            >
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password</label>
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                required
                                placeholder="••••••••"
                            >
                        </div>

                        <button type="submit">Daftarkan Admin</button>
                        <div class="switch-link">
                        Sudah punya akun? <a href="login_admin.php">Masuk disini</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
