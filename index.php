 <?php
date_default_timezone_set('Asia/Jakarta');
session_start();
require 'auth/connection.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, "
        SELECT * FROM users 
        WHERE (nik='$username' OR email='$username')
        LIMIT 1
    ");

    $user = mysqli_fetch_assoc($query);

    if ($user && password_verify($password, $user['password'])) {

        // SET SESSION
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama']    = $user['nama'];
        $_SESSION['role']    = $user['role'];

        // 🔥 UPDATE LAST ACTIVITY + STATUS ONLINE
        mysqli_query($conn, "
            UPDATE users 
            SET last_activity = NOW(),
                status = 'online'
            WHERE id_user = '{$user['id_user']}'
        ");

        // REDIRECT SESUAI ROLE
        if ($user['role'] === 'super_admin') {
            header("Location: superadmin/dashboard.php");
        } elseif ($user['role'] === 'penjual') {
            header("Location: penjual/dashboard.php");
        } elseif ($user['role'] === 'pembeli') {
            header("Location: pembeli/dashboard_pembeli.php");
        } else {
            $error = "Role tidak dikenali";
        }
        exit;

    } else {
        $error = "NIK / Email atau Password salah!";
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Dunia Aksara - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .aksara-container {
            width: 100%;
            max-width: 1000px;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(50, 50, 93, 0.15), 0 10px 25px rgba(0, 0, 0, 0.08);
            display: flex;
            min-height: 600px;
        }

        /* Sidebar Kiri dengan Tema Aksara */
        .aksara-sidebar {
            flex: 1;
            background: linear-gradient(145deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .aksara-sidebar::before {
            content: "書文墨字筆";
            position: absolute;
            font-size: 100px;
            opacity: 0.05;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-10deg);
            font-family: serif;
            white-space: nowrap;
            letter-spacing: 20px;
        }

        .aksara-logo {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .aksara-logo-icon {
            font-size: 50px;
            color: white;
        }

        .aksara-sidebar h1 {
            font-size: 32px;
            margin-bottom: 15px;
            letter-spacing: 1px;
            position: relative;
            z-index: 1;
        }

        .aksara-sidebar p {
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 30px;
            font-size: 16px;
            position: relative;
            z-index: 1;
        }

        .aksara-features {
            list-style: none;
            position: relative;
            z-index: 1;
        }

        .aksara-features li {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 15px;
        }

        .aksara-features li:before {
            content: "✓";
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            margin-right: 12px;
            font-size: 12px;
        }

        /* Form Login */
        .aksara-form-section {
            flex: 1;
            padding: 50px 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .aksara-form-header {
            margin-bottom: 40px;
        }

        .aksara-form-header h2 {
            font-size: 32px;
            color: #2c3e50;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .aksara-form-header p {
            color: #7f8c8d;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .aksara-input-group {
            position: relative;
        }

        .aksara-input {
            width: 100%;
            padding: 18px 20px 18px 50px;
            border: 2px solid #e8e8e8;
            border-radius: 14px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #f9f9f9;
            color: #333;
        }

        .aksara-input:focus {
            outline: none;
            border-color: #3498db;
            background-color: white;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
        }

        .aksara-input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 20px;
        }

        .aksara-button {
            background: linear-gradient(90deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            padding: 20px 30px;
            width: 100%;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .aksara-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.2);
        }

        .aksara-button:active {
            transform: translateY(-1px);
        }

        .aksara-links {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            font-size: 14px;
        }

        .aksara-link {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            padding: 8px 0;
        }

        .aksara-link:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        .aksara-register {
            text-align: center;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #eee;
        }

        .aksara-register a {
            color: #2c3e50;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .aksara-register a:hover {
            color: #3498db;
            text-decoration: underline;
        }

        /* Error Message */
        .aksara-error {
            background: linear-gradient(90deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 16px 20px;
            border-radius: 14px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: shake 0.5s ease-in-out;
        }

        .aksara-error:before {
            content: "⚠";
            font-size: 20px;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Responsif */
        @media (max-width: 900px) {
            .aksara-container {
                flex-direction: column;
                max-width: 500px;
            }
            
            .aksara-sidebar {
                padding: 40px 30px;
            }
            
            .aksara-form-section {
                padding: 40px 30px;
            }
        }

        @media (max-width: 480px) {
            .aksara-sidebar, .aksara-form-section {
                padding: 30px 20px;
            }
            
            .aksara-form-header h2 {
                font-size: 28px;
            }
            
            .aksara-links {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }

        /* Animasi Load */
        .aksara-loading {
            display: none;
        }

        .loading .aksara-loading {
            display: inline;
        }

        .loading .aksara-button-text {
            display: none;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="aksara-container">
        <!-- Sidebar Kiri dengan Tema Aksara -->
        <div class="aksara-sidebar">
            <div class="aksara-logo">
                <div class="aksara-logo-icon">書</div>
            </div>
            
            <h1>Masuk ke Dunia Aksara</h1>
            <p>Ruang di mana setiap kata memiliki jiwa dan setiap aksara membawa makna. Temukan keindahan dalam setiap halaman.</p>
            
            <ul class="aksara-features">
                <li>Akses ke koleksi buku lengkap</li>
                <li>Pengalaman membaca yang personal</li>
                <li>Komunitas pecinta aksara</li>
                <li>Dashboard yang intuitif</li>
            </ul>
            
            <div style="margin-top: auto; opacity: 0.7; font-size: 14px; position: relative; z-index: 1;">
                <p>"Dalam setiap aksara tersimpan jiwa yang menunggu untuk dibaca"</p>
            </div>
        </div>
        
        <!-- Form Login -->
        <div class="aksara-form-section">
            <div class="aksara-form-header">
                <h2>Selamat Datang Kembali</h2>
                <p>Masukkan kredensial Anda untuk melanjutkan petualangan aksara</p>
            </div>
            
           
            
            <?php if ($error): ?>
                <div class="aksara-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="loginForm">
                <div class="form-group">
                    <div class="aksara-input-group">
                        <div class="aksara-input-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <input type="text" name="username" class="aksara-input" 
                               placeholder="NIK atau Email" required
                               autocomplete="username">
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="aksara-input-group">
                        <div class="aksara-input-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <input type="password" name="password" class="aksara-input" 
                               placeholder="Password" required
                               autocomplete="current-password">
                    </div>
                </div>
                
                <button type="submit" name="login" class="aksara-button" id="loginButton">
                    <span class="aksara-button-text">
                        <i class="fas fa-sign-in-alt"></i> Masuk ke Aksara
                    </span>
                    <span class="aksara-loading">
                        <i class="fas fa-spinner fa-spin"></i> Memproses...
                    </span>
                </button>
                
                <div class="aksara-links">
                    <a href="auth/forgot-pass.php" class="aksara-link">
                        <i class="fas fa-key"></i> Lupa Password?
                    </a>
                    <a href="auth/register.php" class="aksara-link">
                        <i class="fas fa-user-plus"></i> Belum punya akun?
                    </a>
                </div>
            </form>
            
            <div class="aksara-register">
                <p>Ingin bergabung dengan komunitas aksara? <a href="auth/register.php">Daftar Sekarang</a></p>
            </div>
        </div>
    </div>

    <script>
        // Form submission animation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const button = document.getElementById('loginButton');
            button.classList.add('loading');
            
            // Simulate processing time for visual feedback
            setTimeout(() => {
                if (document.querySelector('.aksara-error')) {
                    button.classList.remove('loading');
                }
            }, 2000);
        });
        
        // Add focus effects
        const inputs = document.querySelectorAll('.aksara-input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
        
        // Show/hide password functionality
        const passwordInput = document.querySelector('input[name="password"]');
        const passwordIcon = document.querySelector('.aksara-input-group:nth-child(2) .aksara-input-icon');
        
        // Create show/hide password toggle
        const togglePassword = document.createElement('div');
        togglePassword.innerHTML = '<i class="fas fa-eye"></i>';
        togglePassword.style.cssText = `
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 18px;
            cursor: pointer;
            transition: color 0.2s ease;
        `;
        togglePassword.addEventListener('mouseenter', () => {
            togglePassword.style.color = '#3498db';
        });
        togglePassword.addEventListener('mouseleave', () => {
            togglePassword.style.color = '#7f8c8d';
        });
        
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
        
        document.querySelector('.aksara-input-group:nth-child(2)').appendChild(togglePassword);
    </script>
</body>
</html>