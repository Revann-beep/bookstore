<?php
session_start();
include '../auth/connection.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}



$id_user = $_SESSION['id_user'];
$page = $_GET['page'] ?? 'profile';

$query = mysqli_query ($conn, "SELECT * FROM users WHERE id_user='$id_user'");
$data = mysqli_fetch_assoc($query);

/* PROSES UPDATE */
if (isset($_POST['update'])) {

    $nama   = $_POST['nama'];
    $email  = $_POST['email'];
    $alamat = $_POST['alamat'];

    if (!empty($_FILES['image']['name'])) {
        $folder = "../img/profile/";
        if (!is_dir($folder)) mkdir($folder, 0777, true);

        $nama_image = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], $folder . $nama_image);

        if ($data['image'] && $data['image'] != 'default.png') {
            @unlink($folder . $data['image']);
        }

        $updateimage = ", image='$nama_image'";
    } else {
        $updateimage = "";
    }

    mysqli_query($conn, "
        UPDATE users SET
        nama='$nama',
        email='$email',
        alamat='$alamat'
        $updateimage
        WHERE id_user='$id_user'
    ");

    echo "<script>alert('Data berhasil diperbarui');location='my.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>My Account - Aksara Jiwa</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Poppins', sans-serif;
    }
    .brand-font {
        font-family: 'Playfair Display', serif;
    }
</style>
</head>

<body class="bg-gradient-to-b from-slate-50 to-slate-100">

<div class="flex min-h-screen">

<!-- SIDEBAR -->
<aside class="w-64 bg-gradient-to-b from-slate-900 to-slate-800 shadow-2xl p-6">
    <div class="mb-10">
        <h1 class="text-3xl font-bold text-amber-300 brand-font mb-1">AKSARA</h1>
        <h1 class="text-3xl font-bold text-amber-100 brand-font">JIWA</h1>
        <p class="text-slate-400 text-sm mt-2">Bookstore </p>
    </div>
    <nav class="space-y-2">
        <a href="dashboard_pembeli.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
            </svg>
            Dashboard
        </a>
        <a href="halaman-pesanan.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
            </svg>
            Produk
        </a>
        <a href="status.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            Status
        </a>
        <a href="pesan.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />
            </svg>
            Chat
        </a>
        <a href="report.html" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            Laporan
        </a>
        <!-- Help Section Added Here -->
        <a href="help.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
            Help
        </a>
        <a href="my.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 text-white font-medium shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
            </svg>
            My Account
        </a>
        <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-300 hover:bg-red-900/30 hover:text-red-200 transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
            </svg>
            Sign Out
        </a>
    </nav>
</aside>

<!-- MAIN CONTENT -->
<main class="flex-1 p-8">

<div class="max-w-4xl mx-auto">

    <!-- HEADER -->
    <div class="mb-10">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 brand-font mb-2">Akun Saya</h1>
                <p class="text-slate-600">Kelola informasi profil dan preferensi akun Anda</p>
            </div>
            <div class="flex items-center gap-3 bg-gradient-to-r from-slate-800 to-slate-900 px-5 py-3 rounded-xl shadow-lg">
                <div class="text-right">
                    <p class="font-semibold text-white"><?= $data['nama']; ?></p>
                    <p class="text-sm text-slate-300">Member Aksara Jiwa</p>
                </div>
                <div class="relative">
                    <img src="../img/profile/<?= $data['image'] ?? 'default.png'; ?>"
                         class="w-12 h-12 rounded-full object-cover border-2 border-amber-400">
                    <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-slate-900"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
        <div class="p-8">
            
            <?php if ($page == 'profile') { ?>

            <!-- PROFILE OVERVIEW -->
            <div class="text-center mb-10">
                <div class="relative inline-block">
                    <img src="../img/profile/<?= $data['image'] ?? 'default.png'; ?>"
                         class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-2xl mx-auto mb-4">
                    <div class="absolute bottom-6 right-6 w-8 h-8 bg-gradient-to-r from-amber-500 to-amber-600 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2"><?= $data['nama']; ?></h2>
                <p class="text-slate-600 mb-6"><?= $data['email']; ?></p>
                
                <div class="flex justify-center gap-4 mt-8">
                    <a href="?page=detail" class="flex items-center gap-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-8 py-3 rounded-full font-semibold transition-all duration-300 shadow hover:shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                        </svg>
                        Detail Profil
                    </a>
                    <a href="?page=edit" class="flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white px-8 py-3 rounded-full font-semibold transition-all duration-300 shadow hover:shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                        Edit Profil
                    </a>
                </div>
            </div>

            <?php } elseif ($page == 'detail') { ?>

            <!-- DETAIL PROFILE -->
            <div class="max-w-2xl mx-auto">
                <div class="flex items-center gap-4 mb-8">
                    <a href="my.php" class="flex items-center gap-2 text-slate-600 hover:text-slate-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Kembali
                    </a>
                    <h2 class="text-xl font-bold text-slate-800">Detail Profil</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl p-8">
                        <div class="text-center mb-6">
                            <img src="../img/profile/<?= $data['image'] ?? 'default.png'; ?>"
                                 class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg mx-auto">
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 text-center mb-2"><?= $data['nama']; ?></h3>
                        <p class="text-slate-600 text-center"><?= $data['email']; ?></p>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white border border-slate-200 rounded-xl p-6">
                            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                                Informasi Pribadi
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm text-slate-500 mb-1">Nama Lengkap</p>
                                    <p class="font-semibold text-slate-800"><?= $data['nama']; ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500 mb-1">Email</p>
                                    <p class="font-semibold text-slate-800"><?= $data['email']; ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500 mb-1">Alamat</p>
                                    <p class="font-semibold text-slate-800"><?= $data['alamat'] ?: 'Belum diisi'; ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500 mb-1">Terdaftar Sejak</p>
                                    <p class="font-semibold text-slate-800"><?= date('d F Y', strtotime($data['created_at'])); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-amber-50 to-amber-100 border border-amber-200 rounded-xl p-6">
                            <h3 class="text-lg font-bold text-amber-800 mb-2">Member Aksara Jiwa</h3>
                            <p class="text-amber-700 text-sm">Nikmati semua keuntungan sebagai anggota toko buku kami</p>
                        </div>
                    </div>
                </div>
            </div>

            <?php } elseif ($page == 'edit') { ?>

            <!-- EDIT PROFILE -->
            <div class="max-w-2xl mx-auto">
                <div class="flex items-center gap-4 mb-8">
                    <a href="my.php" class="flex items-center gap-2 text-slate-600 hover:text-slate-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Kembali
                    </a>
                    <h2 class="text-xl font-bold text-slate-800">Edit Profil</h2>
                </div>

                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl p-8">
                        <div class="text-center mb-6">
                            <div class="relative inline-block">
                                <img src="../img/profile/<?= $data['image'] ?? 'default.png'; ?>"
                                     id="profilePreview"
                                     class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg mx-auto">
                                <label for="image" class="absolute bottom-2 right-2 w-10 h-10 bg-gradient-to-r from-amber-500 to-amber-600 rounded-full flex items-center justify-center cursor-pointer hover:shadow-lg transition-all duration-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                </label>
                            </div>
                            <input type="file" name="image" id="image" accept="image/*" class="hidden" onchange="previewImage(this)">
                            <p class="text-sm text-slate-500 mt-3">Klik ikon untuk mengubah foto profil</p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap</label>
                                <input type="text" name="nama" value="<?= $data['nama']; ?>"
                                    class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-300" required>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
                                <input type="email" name="email" value="<?= $data['email']; ?>"
                                    class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-300" required>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat</label>
                                <textarea name="alamat" rows="3"
                                    class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-300"><?= $data['alamat']; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button name="update" 
                                class="flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white px-8 py-3 rounded-full font-semibold transition-all duration-300 shadow hover:shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Simpan Perubahan
                        </button>
                        <a href="my.php" 
                           class="flex items-center gap-2 bg-gradient-to-r from-slate-500 to-slate-600 hover:from-slate-600 hover:to-slate-700 text-white px-8 py-3 rounded-full font-semibold transition-all duration-300 shadow hover:shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z" clip-rule="evenodd" />
                            </svg>
                            Batal
                        </a>
                    </div>
                </form>
            </div>

            <?php } ?>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="text-center text-slate-500 mt-12 pb-4">
        <div class="flex items-center justify-center gap-2 mb-2">
            <div class="w-8 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
            <div class="w-8 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
            <div class="w-8 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
        </div>
        <p>© <?= date('Y'); ?> <span class="text-amber-600 font-semibold brand-font">Aksara Jiwa</span> - Bookstore </p>
        <p class="text-sm mt-1">My Account | <?= htmlspecialchars($data['nama']); ?></p>
    </footer>
</div>
</main>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

</body>
</html>