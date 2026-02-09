<?php
require 'connection.php';

if (!isset($_GET['token'])) {
    die("Token tidak valid");
}

$token = $_GET['token'];
$data = mysqli_query($conn, "SELECT * FROM users WHERE reset_token='$token' AND reset_expired > NOW()");

if (mysqli_num_rows($data) == 0) {
    die("Token kadaluarsa atau salah");
}

$message = '';
if (isset($_POST['password'])) {
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    mysqli_query($conn, "
        UPDATE users 
        SET password='$pass', reset_token=NULL, reset_expired=NULL 
        WHERE reset_token='$token'
    ");

    $message = "Password berhasil direset. <a href='../index.php' class='text-blue-600 hover:text-blue-800 font-medium'>Login</a>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .form-container {
            animation: slideUp 0.5s ease-out;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto form-container">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-8 py-6">
                    <h1 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-key mr-3"></i>
                        Reset Password
                    </h1>
                    <p class="text-blue-100 mt-2">Masukkan password baru Anda</p>
                </div>
                
                <!-- Form -->
                <div class="px-8 py-8">
                    <?php if($message): ?>
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                                <div class="text-green-800"><?php echo $message; ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <form method="POST" class="space-y-6">
                            <div class="space-y-2">
                                <label for="password" class="block text-sm font-medium text-gray-700">
                                    <i class="fas fa-lock mr-2"></i>Password Baru
                                </label>
                                <div class="relative">
                                    <input 
                                        type="password" 
                                        name="password" 
                                        id="password"
                                        required 
                                        placeholder="Masukkan password baru"
                                        class="w-full px-4 py-3 pl-11 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none"
                                    >
                                    <i class="fas fa-key absolute left-4 top-3.5 text-gray-400"></i>
                                    <button type="button" id="togglePassword" class="absolute right-4 top-3.5 text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Password minimal 8 karakter dengan kombinasi huruf dan angka</p>
                            </div>
                            
                            <button 
                                type="submit" 
                                class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold py-3.5 rounded-xl hover:from-blue-600 hover:to-purple-700 focus:ring-4 focus:ring-blue-300 transition-all duration-300 transform hover:-translate-y-0.5 shadow-lg"
                            >
                                <i class="fas fa-redo-alt mr-2"></i>Reset Password
                            </button>
                        </form>
                        
                        <div class="mt-6 pt-6 border-t border-gray-100 text-center">
                            <a href="../index.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
                                <i class="fas fa-arrow-left mr-2"></i> Kembali ke halaman login
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Footer -->
                <div class="bg-gray-50 px-8 py-4 text-center text-sm text-gray-500">
                    <p>Pastikan Anda menggunakan password yang kuat dan mudah diingat</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Form validation
        document.querySelector('form')?.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password minimal harus 8 karakter');
                return false;
            }
        });
    </script>
</body>
</html>