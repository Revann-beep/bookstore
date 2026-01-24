<?php
require 'connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

$msg = "";

if (isset($_POST['email'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($cek) > 0) {

        $token = bin2hex(random_bytes(32));
        $expired = date("Y-m-d H:i:s", strtotime("+1 hour"));

        mysqli_query($conn, "UPDATE users SET reset_token='$token', reset_expired='$expired' WHERE email='$email'");

        $link = "http://localhost/bookstore-main/auth/newpass.php?token=$token";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'reifanevandra8@gmail.com';
            $mail->Password   = 'yeaa vtgy mefj lxvc';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('reifanevandra8@gmail.com', 'bookstore');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Reset Password';
            $mail->Body    = "
                <p>Klik link di bawah untuk reset password:</p>
                <a href='$link'>$link</a>
                <p>Link berlaku 1 jam</p>
            ";

            $mail->send();
            $msg = "Link reset password sudah dikirim ke email.";
        } catch (Exception $e) {
            $msg = "Gagal kirim email.";
        }

    } else {
        $msg = "Email tidak terdaftar.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pulihkan Aksara - Lupa Password</title>
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
            max-width: 480px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(50, 50, 93, 0.1), 0 5px 15px rgba(0, 0, 0, 0.07);
            position: relative;
        }

        .aksara-header {
            background: linear-gradient(90deg, #2c3e50 0%, #4a6491 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .aksara-header::before {
            content: "書";
            position: absolute;
            font-size: 120px;
            opacity: 0.1;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-family: serif;
        }

        .aksara-header h2 {
            font-size: 28px;
            margin-bottom: 10px;
            letter-spacing: 1px;
            position: relative;
            z-index: 1;
        }

        .aksara-header p {
            opacity: 0.9;
            font-size: 15px;
            position: relative;
            z-index: 1;
        }

        .aksara-form {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 15px;
        }

        .aksara-input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #f9f9f9;
        }

        .aksara-input:focus {
            outline: none;
            border-color: #4a6491;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(74, 100, 145, 0.1);
        }

        .aksara-button {
            background: linear-gradient(90deg, #2c3e50 0%, #4a6491 100%);
            color: white;
            border: none;
            padding: 18px 30px;
            width: 100%;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
        }

        .aksara-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
        }

        .aksara-button:active {
            transform: translateY(0);
        }

        .aksara-message {
            margin-top: 25px;
            padding: 16px;
            border-radius: 12px;
            text-align: center;
            font-size: 15px;
            display: none;
        }

        .aksara-message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }

        .aksara-message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }

        .aksara-message.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
            display: block;
        }

        .aksara-footer {
            text-align: center;
            padding: 20px 40px 30px;
            color: #666;
            font-size: 14px;
            border-top: 1px solid #eee;
        }

        .aksara-footer a {
            color: #4a6491;
            text-decoration: none;
            font-weight: 600;
        }

        .aksara-footer a:hover {
            text-decoration: underline;
        }

        .aksara-icon {
            font-size: 50px;
            margin-bottom: 15px;
            color: #4a6491;
        }

        @media (max-width: 576px) {
            .aksara-container {
                max-width: 100%;
            }
            
            .aksara-form {
                padding: 30px 25px;
            }
            
            .aksara-header {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="aksara-container">
        <div class="aksara-header">
            <h2>Pulihkan Aksara</h2>
            <p>Temukan kembali kata sandi Anda dengan bantuan email terdaftar</p>
        </div>
        
        <form method="POST" class="aksara-form">
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <input type="email" name="email" id="email" class="aksara-input" required 
                       placeholder="masukkan.email@anda.com" autocomplete="email">
            </div>
            
            <button type="submit" class="aksara-button">
                Kirim Link Pemulihan
            </button>
            
            <?php if (!empty($msg)): ?>
                <div class="aksara-message 
                    <?php 
                    if (strpos($msg, 'dikirim') !== false) echo 'success';
                    elseif (strpos($msg, 'tidak terdaftar') !== false) echo 'error';
                    else echo 'info';
                    ?>">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>
        </form>
        
        <div class="aksara-footer">
            <p>Ingat kata sandi Anda? <a href="../index.php">Kembali ke Masuk</a></p>
            <p style="margin-top: 10px; font-size: 12px; opacity: 0.7;">
                © 2023 Bookstore - Semua aksara terjaga
            </p>
        </div>
    </div>

    <script>
        // Tambahkan efek visual saat form di-submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const button = document.querySelector('.aksara-button');
            button.innerHTML = 'Mengirim...';
            button.style.opacity = '0.8';
            
            // Reset setelah 3 detik jika halaman tidak di-refresh
            setTimeout(() => {
                button.innerHTML = 'Kirim Link Pemulihan';
                button.style.opacity = '1';
            }, 3000);
        });
    </script>
</body>
</html>