<?php
session_start();
require '../auth/connection.php';

/* =====================
   CEK ROLE
===================== */
// echo "SESSION ID: " . $_SESSION['id_user'] . "<br>";
// echo "GET ID: " . ($_GET['id'] ?? 'null') . "<br>";
// echo "POST ID: " . ($_POST['target_id'] ?? 'null');
// exit;

if (
    !isset($_SESSION['role']) ||
    !in_array($_SESSION['role'], ['penjual', 'super_admin'])
) {
    header("Location: ../index.php");
    exit;
}

/* =====================
   TENTUKAN ID USER
===================== */
if ($_SESSION['role'] === 'super_admin') {
    // superadmin edit penjual lain
    $id_user = $_GET['id'] ?? null;
} else {
    // penjual edit dirinya sendiri
    $id_user = $_SESSION['id_user'];
}

if (!$id_user) {
    header("Location: ../index.php");
    exit;
}

/* =====================
   AMBIL DATA USER
===================== */
if ($_SESSION['role'] === 'superadmin') {
    // superadmin hanya boleh edit akun penjual
    $data = mysqli_query($conn, "
        SELECT * FROM users 
        WHERE id_user='$id_user' AND role='penjual'
    ");
} else {
    $data = mysqli_query($conn, "
        SELECT * FROM users 
        WHERE id_user='$id_user'
    ");
}

if (mysqli_num_rows($data) === 0) {
    header("Location: ../index.php");
    exit;
}

$user = mysqli_fetch_assoc($data);

$error = '';
$success = '';

/* =====================
   CEK BATAS 7 HARI GANTI FOTO
===================== */
$bolehGantiimage = true;
if (!empty($user['last_photo_update'])) {
    $last = strtotime($user['last_photo_update']);
    $now  = time();
    if (($now - $last) < (7 * 24 * 60 * 60)) {
        $bolehGantiimage = false;
    }
}

/* =====================
   PROSES UPDATE
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama  = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $imageBaru = $user['image']; // default pakai yang lama

    /* ===== UPLOAD IMAGE ===== */
    if (!empty($_FILES['image']['name'])) {

        if (!$bolehGantiimage) {
            $error = "Image profil hanya bisa diganti 7 hari sekali.";
        } else {

            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowed)) {
                $error = "Format gambar harus JPG / PNG / WEBP.";
            } else {

                $folder = "../img/profile/";
                if (!is_dir($folder)) {
                    mkdir($folder, 0777, true);
                }

                $namaFile = 'profile_' . $id_user . '_' . time() . '.' . $ext;
                $target = $folder . $namaFile;

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $error = "Upload gambar gagal. Cek permission folder.";
                } else {

                    if (!empty($user['image']) && file_exists($user['image'])) {
                        unlink($user['image']);
                    }

                    $imageBaru = $target;

                    mysqli_query($conn, "
                        UPDATE users 
                        SET last_photo_update=NOW() 
                        WHERE id_user='$id_user'
                    ");
                }
            }
        }
    }

    /* ===== UPDATE DATA ===== */
    if (!$error) {
        mysqli_query($conn, "
            UPDATE users SET 
                nama='$nama',
                email='$email',
                image='$imageBaru'
            WHERE id_user='$id_user'
        ");

        $success = "Data berhasil diperbarui";
    }
}
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Akun</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(10px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex items-center justify-center p-4">

<div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-lg animate-fade-in">
    
    <!-- Header -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-r from-primary-500 to-primary-600 text-white mb-4">
            <i class="fas fa-user-edit text-lg"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Edit Akun Saya</h2>
        <p class="text-gray-500 mt-2">Kelola informasi akun Anda</p>
    </div>

    <!-- Alert Messages -->
    <?php if ($error): ?>
        <div class="mb-6 animate-slide-up">
            <div class="flex items-center p-4 rounded-lg border border-red-200 bg-red-50 text-red-700">
                <i class="fas fa-exclamation-circle mr-3"></i>
                <span><?= $error ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="mb-6 animate-slide-up">
            <div class="flex items-center p-4 rounded-lg border border-green-200 bg-green-50 text-green-700">
                <i class="fas fa-check-circle mr-3"></i>
                <span><?= $success ?></span>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <!-- Profile Image Section -->
        <div class="text-center space-y-4">
            <div class="relative inline-block">
                <div class="relative">
                    <img src="<?= $user['image'] ?: '../assets/default_user.png' ?>"
                         class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-white shadow-lg">
                    <div class="absolute bottom-2 right-4 w-10 h-10 rounded-full bg-primary-500 flex items-center justify-center text-white shadow-lg">
                        <i class="fas fa-user text-sm"></i>
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <?php if ($bolehGantiimage): ?>
                    <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-primary-50 hover:bg-primary-100 text-primary-700 rounded-lg transition duration-200">
                        <i class="fas fa-camera mr-2"></i>
                        <span>Unggah Foto Baru</span>
                        <input type="file" name="image" class="hidden" id="fileInput" onchange="previewImage(event)">
                    </label>
                    <div id="imagePreview" class="mt-3 hidden">
                        <p class="text-sm text-gray-600">Pratinjau:</p>
                        <img id="preview" class="w-20 h-20 rounded-full mx-auto mt-2 object-cover border">
                    </div>
                    <p class="text-xs text-gray-500 mt-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        Foto profil dapat diganti setiap 7 hari sekali
                    </p>
                <?php else: ?>
                    <div class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-500 rounded-lg">
                        <i class="fas fa-clock mr-2"></i>
                        <span>Foto profil dapat diganti setelah 7 hari</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Form Fields -->
        <div class="space-y-5">
            <!-- NAMA Field -->
            <div class="space-y-2">
                <label class="text-sm font-semibold text-gray-700 flex items-center">
                    <i class="fas fa-user text-primary-500 mr-2 text-sm"></i>
                    Nama Lengkap
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>"
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition duration-200"
                           placeholder="Masukkan nama lengkap" required>
                </div>
            </div>

            <!-- EMAIL Field -->
            <div class="space-y-2">
                <label class="text-sm font-semibold text-gray-700 flex items-center">
                    <i class="fas fa-envelope text-primary-500 mr-2 text-sm"></i>
                    Alamat Email
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-400"></i>
                    </div>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition duration-200"
                           placeholder="contoh@email.com" required>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="pt-4 space-y-4">
            <button type="submit"
                    class="w-full bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-medium py-3.5 rounded-xl shadow-md hover:shadow-lg transition duration-300 transform hover:-translate-y-0.5">
                <i class="fas fa-save mr-2"></i>
                Simpan Perubahan
            </button>

            <a href="penjual.php"
               class="block text-center text-gray-600 hover:text-gray-800 hover:bg-gray-50 font-medium py-3 rounded-xl border border-gray-300 hover:border-gray-400 transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Dashboard
            </a>
        </div>
    </form>

    <!-- Footer Info -->
    <div class="mt-8 pt-6 border-t border-gray-200 text-center">
        <p class="text-xs text-gray-500">
            <i class="fas fa-shield-alt mr-1"></i>
            Data Anda aman dan terlindungi
        </p>
    </div>
</div>

<script>
    // Image preview function
    function previewImage(event) {
        const reader = new FileReader();
        const imagePreview = document.getElementById('imagePreview');
        const preview = document.getElementById('preview');
        
        reader.onload = function() {
            preview.src = reader.result;
            imagePreview.classList.remove('hidden');
        }
        
        if(event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }

    // Add focus effect to form inputs
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('input[type="text"], input[type="email"]');
        
        inputs.forEach(input => {
            // Add focus/blur effects
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('ring-2', 'ring-primary-100');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('ring-2', 'ring-primary-100');
            });
        });
    });
</script>

</body>
</html>