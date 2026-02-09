<?php
session_start();
require '../auth/connection.php';

mysqli_query($conn, "
    UPDATE users 
    SET last_activity = NOW(),
        status = 'online'
    WHERE id_user = '$id_user'
");


$edit = false;
$id_kategori = '';
$nama_kategori = '';
$icon_lama = '';

// ================= TAMBAH / UPDATE =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    $icon = '';

    // UPLOAD icon
    if (!empty($_FILES['icon']['name'])) {
        $ext = pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION);
        $icon = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['icon']['tmp_name'], "../img/kategori/" . $icon);
    }

    // TAMBAH
if ($_POST['action'] === 'tambah') {

    // CEK DUPLIKAT NAMA
    $cek = mysqli_query($conn, "
        SELECT id_kategori 
        FROM kategori 
        WHERE nama_kategori = '$nama'
    ");

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>
            alert('Nama kategori sudah digunakan!');
            window.location='kategori.php';
        </script>";
        exit;
    }

    mysqli_query($conn, "
        INSERT INTO kategori (nama_kategori, icon)
        VALUES ('$nama', '$icon')
    ");
}

    // UPDATE
    // UPDATE
if ($_POST['action'] === 'update') {
    $id = $_POST['id_kategori'];

    // CEK DUPLIKAT NAMA (KECUALI ID SENDIRI)
    $cek = mysqli_query($conn, "
        SELECT id_kategori 
        FROM kategori 
        WHERE nama_kategori = '$nama'
        AND id_kategori != '$id'
    ");

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>
            alert('Nama kategori sudah digunakan kategori lain!');
            window.location='kategori.php?edit=$id';
        </script>";
        exit;
    }

    if ($icon) {
        // hapus icon lama
        $old = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT icon FROM kategori 
            WHERE id_kategori='$id'
        "));

        if ($old['icon'] && file_exists("../img/kategori/" . $old['icon'])) {
            unlink("../img/kategori/" . $old['icon']);
        }

        mysqli_query($conn, "
            UPDATE kategori 
            SET nama_kategori='$nama', icon='$icon'
            WHERE id_kategori='$id'
        ");
    } else {
        mysqli_query($conn, "
            UPDATE kategori 
            SET nama_kategori='$nama'
            WHERE id_kategori='$id'
        ");
    }
}

    header("Location: kategori.php");
    exit;
}

// ================= HAPUS =================
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT icon FROM kategori WHERE id_kategori='$id'"));
    if ($data['icon'] && file_exists("../img/kategori/" . $data['icon'])) {
        unlink("../img/kategori/" . $data['icon']);
    }

    mysqli_query($conn, "DELETE FROM kategori WHERE id_kategori='$id'");
    header("Location: kategori.php");
    exit;
}

// ================= EDIT =================
if (isset($_GET['edit'])) {
    $edit = true;
    $id = $_GET['edit'];
    $data = mysqli_query($conn, "SELECT * FROM kategori WHERE id_kategori='$id'");
    $row = mysqli_fetch_assoc($data);

    $id_kategori = $row['id_kategori'];
    $nama_kategori = $row['nama_kategori'];
    $icon_lama = $row['icon'];
}

// ================= AMBIL DATA =================
$kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id_kategori DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori | Aksara Jiwa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
            overflow: hidden; /* Mencegah scroll global */
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.08);
        }
        .gradient-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
        .gradient-accent {
            background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%);
        }
        .gradient-success {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        }
        .hover-lift {
            transition: all 0.3s ease;
        }
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        .sidebar-link {
            position: relative;
            overflow: hidden;
        }
        .sidebar-link::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
            transition: width 0.3s ease;
        }
        .sidebar-link:hover::after {
            width: 100%;
        }
        .active-link {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        }
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(79, 70, 229, 0.3);
        }
        .table-row-hover:hover {
            background-color: #f8fafc;
        }
        .file-upload {
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }
        .file-upload input[type="file"] {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 100%;
            min-height: 100%;
            font-size: 100px;
            text-align: right;
            filter: alpha(opacity=0);
            opacity: 0;
            outline: none;
            background: white;
            cursor: pointer;
            display: block;
        }
        .category-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 20px;
            color: white;
            margin: 0 auto;
        }
        .no-icon {
            background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
        }
        .icon-preview {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        /* SIDEBAR FIXED - TIDAK IKUT SCROLL */
        .sidebar-container {
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 16rem; /* 64 * 0.25rem = 16rem */
            z-index: 40;
            overflow-y: auto; /* Scroll hanya di sidebar jika kontennya panjang */
            overflow-x: hidden;
        }
        
        /* MAIN CONTENT SCROLLABLE */
        .main-content {
            margin-left: 16rem; /* Sama dengan lebar sidebar */
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 1.5rem; /* p-6 */
        }
        
        /* Custom scrollbar untuk main content */
        .main-content::-webkit-scrollbar {
            width: 8px;
        }
        .main-content::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .main-content::-webkit-scrollbar-thumb {
            background: #c7d2fe;
            border-radius: 4px;
        }
        .main-content::-webkit-scrollbar-thumb:hover {
            background: #a5b4fc;
        }
        
        /* Custom scrollbar untuk sidebar */
        .sidebar-container::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-container::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-container::-webkit-scrollbar-thumb {
            background: #c7d2fe;
            border-radius: 4px;
        }
        .sidebar-container::-webkit-scrollbar-thumb:hover {
            background: #a5b4fc;
        }
        
        /* Responsive untuk mobile */
        @media (max-width: 768px) {
            .sidebar-container {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                width: 100%;
                max-width: 280px;
            }
            .sidebar-container.mobile-open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            .mobile-menu-btn {
                display: block;
            }
            .overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 30;
            }
            .overlay.active {
                display: block;
            }
        }
        
        /* Desktop - tetap tampil normal */
        @media (min-width: 769px) {
            .mobile-menu-btn {
                display: none;
            }
            .overlay {
                display: none !important;
            }
        }
    </style>
</head>
<body class="min-h-screen overflow-hidden">

<!-- OVERLAY MOBILE -->
<div id="mobileOverlay" class="overlay"></div>

<!-- SIDEBAR FIXED - TIDAK IKUT SCROLL -->
<aside class="sidebar-container w-64 glass-card flex flex-col shadow-xl">
    <!-- LOGO -->
    <div class="p-6 border-b border-gray-100">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 gradient-primary text-white rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-book-open text-lg"></i>
            </div>
            <div>
                <h1 class="font-bold text-xl text-gray-800">Aksara Jiwa</h1>
                <p class="text-xs text-gray-500 mt-1">Super Admin Dashboard</p>
            </div>
        </div>
    </div>

    <!-- NAVIGATION -->
    <nav class="flex-1 px-4 py-6 space-y-1">
        <a href="dashboard.php" class="flex items-center px-4 py-3 rounded-xl sidebar-link text-gray-700 hover:bg-indigo-50 hover-lift">
            <i class="fas fa-tachometer-alt w-5 mr-3 text-gray-500"></i>
            <span>Dashboard</span>
        </a>
        <a href="penjual.php" class="flex items-center px-4 py-3 rounded-xl sidebar-link text-gray-700 hover:bg-indigo-50 hover-lift">
            <i class="fas fa-store w-5 mr-3 text-blue-500"></i>
            <span>Data Penjual</span>
        </a>
        <a href="pembeli.php" class="flex items-center px-4 py-3 rounded-xl sidebar-link text-gray-700 hover:bg-indigo-50 hover-lift">
            <i class="fas fa-users w-5 mr-3 text-emerald-500"></i>
            <span>Data Pembeli</span>
        </a>
        <a href="kategori.php" class="flex items-center px-4 py-3 rounded-xl active-link hover-lift">
            <i class="fas fa-tags w-5 mr-3"></i>
            <span class="font-medium">Kategori</span>
        </a>
    </nav>

    <!-- FOOTER -->
    <div class="p-4 border-t border-gray-100 mt-auto">
        <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-200">
            <i class="fas fa-sign-out-alt w-5"></i>
            <span>Sign Out</span>
        </a>
    </div>
</aside>

<!-- MAIN CONTENT SCROLLABLE -->
<main class="main-content bg-gradient-to-br from-gray-50 to-blue-50">
    <!-- MOBILE MENU BUTTON -->
    <button id="mobileMenuBtn" class="mobile-menu-btn mb-6 p-2 rounded-lg bg-white shadow hover:shadow-md transition-shadow">
        <i class="fas fa-bars text-gray-600 text-xl"></i>
    </button>

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h2 class="text-2xl lg:text-3xl font-bold text-gray-800 mb-2"><?= $edit ? 'Edit Kategori' : 'Kelola Kategori' ?></h2>
            <p class="text-gray-600"><?= $edit ? 'Perbarui informasi kategori' : 'Tambah dan kelola kategori produk' ?></p>
        </div>
        <div class="mt-4 md:mt-0 text-sm text-gray-500 bg-white px-4 py-2 rounded-full shadow-sm">
            <i class="fas fa-layer-group mr-2"></i>
            <span>Total Kategori: <?= mysqli_num_rows($kategori) ?></span>
        </div>
    </div>

    <!-- FORM CARD -->
    <div class="glass-card rounded-2xl p-6 mb-8 hover-lift">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-<?= $edit ? 'edit' : 'plus-circle' ?> text-indigo-500"></i>
            <?= $edit ? 'Form Edit Kategori' : 'Form Tambah Kategori Baru' ?>
        </h3>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="action" value="<?= $edit ? 'update' : 'tambah' ?>">
            <input type="hidden" name="id_kategori" value="<?= $id_kategori ?>">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Nama Kategori -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag mr-2 text-gray-500"></i>
                        Nama Kategori
                    </label>
                    <input type="text" name="nama_kategori"
                           value="<?= htmlspecialchars($nama_kategori) ?>"
                           placeholder="Masukkan nama kategori"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                           required>
                </div>

                <!-- Upload Icon -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-image mr-2 text-gray-500"></i>
                        Icon Kategori
                    </label>
                    <div class="flex items-center gap-4">
                        <div class="file-upload">
                            <label class="flex items-center gap-2 px-4 py-3 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition-colors">
                                <i class="fas fa-cloud-upload-alt text-gray-500"></i>
                                <span class="text-gray-700" id="fileLabel"><?= $edit ? 'Ganti Icon' : 'Pilih Icon' ?></span>
                                <input type="file" name="icon" accept="image/*" class="hidden" id="fileInput">
                            </label>
                        </div>
                        
                        <?php if ($edit && $icon_lama): ?>
                            <div class="text-center">
                                <p class="text-xs text-gray-500 mb-2">Icon Saat Ini</p>
                                <img src="../img/kategori/<?= htmlspecialchars($icon_lama) ?>" 
                                     class="icon-preview mx-auto">
                            </div>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Format: JPG, PNG, GIF. Maks: 2MB</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3 pt-4">
                <button type="submit" 
                        class="btn-primary px-6 py-3 rounded-xl font-medium flex items-center gap-2">
                    <i class="fas fa-<?= $edit ? 'save' : 'plus' ?>"></i>
                    <?= $edit ? 'Update Kategori' : 'Tambah Kategori' ?>
                </button>
                
                <?php if ($edit): ?>
                    <a href="kategori.php" 
                       class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors flex items-center gap-2">
                        <i class="fas fa-times"></i>
                        Batal Edit
                    </a>
                <?php endif; ?>
                
                <a href="kategori.php" 
                   class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors flex items-center gap-2">
                    <i class="fas fa-redo"></i>
                    Refresh
                </a>
            </div>
        </form>
    </div>

    <!-- DATA KATEGORI -->
    <div class="glass-card rounded-2xl overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-list-alt text-indigo-500"></i>
                Daftar Kategori
            </h3>
        </div>
        
        <?php if (mysqli_num_rows($kategori) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Icon</th>
                            <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kategori</th>
                            <th class="py-4 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    <?php $no=1; while ($row = mysqli_fetch_assoc($kategori)) : ?>
                        <tr class="table-row-hover transition-colors duration-200">
                            <td class="py-4 px-6 text-center text-gray-700 font-medium"><?= $no++ ?></td>
                            <td class="py-4 px-6">
                                <?php if ($row['icon']): ?>
                                    <div class="flex items-center justify-center">
                                        <img src="../img/kategori/<?= htmlspecialchars($row['icon']) ?>" 
                                             class="category-icon"
                                             alt="<?= htmlspecialchars($row['nama_kategori']) ?>">
                                    </div>
                                <?php else: ?>
                                    <div class="category-icon no-icon">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-medium text-gray-800"><?= htmlspecialchars($row['nama_kategori']) ?></div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex gap-2">
                                    <a href="?edit=<?= $row['id_kategori'] ?>" 
                                       class="px-4 py-2 bg-amber-100 text-amber-700 rounded-lg font-medium hover:bg-amber-200 transition-colors flex items-center gap-2">
                                        <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <a href="?hapus=<?= $row['id_kategori'] ?>"
                                       onclick="return confirm('Yakin hapus kategori <?= htmlspecialchars($row['nama_kategori']) ?>?')"
                                       class="px-4 py-2 bg-red-100 text-red-700 rounded-lg font-medium hover:bg-red-200 transition-colors flex items-center gap-2">
                                        <i class="fas fa-trash"></i>
                                        Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-12 text-center">
                <div class="w-20 h-20 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-tags text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Belum Ada Kategori</h3>
                <p class="text-gray-600 mb-6">Mulai dengan menambahkan kategori pertama Anda</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- FOOTER -->
    <div class="text-center py-6 border-t border-gray-200">
        <p class="text-sm text-gray-500">
            © 2024 <span class="font-semibold text-indigo-600">Aksara Jiwa</span> - Platform Literasi Digital
            <br>
            <span class="text-xs mt-1 block">Super Admin Dashboard • Manajemen Kategori</span>
        </p>
    </div>
</main>

<script>
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.querySelector('.sidebar-container');
    const overlay = document.getElementById('mobileOverlay');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
        });
    }
    
    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        });
    }
    
    // File upload preview
    const fileInput = document.getElementById('fileInput');
    const fileLabel = document.getElementById('fileLabel');
    
    if (fileInput && fileLabel) {
        fileInput.addEventListener('change', function(e) {
            const fileName = this.files[0]?.name || (this.closest('label').dataset.default || 'Pilih Icon');
            fileLabel.textContent = fileName;
        });
    }
    
    // Confirm delete
    document.querySelectorAll('a[onclick*="confirm"]').forEach(link => {
        link.addEventListener('click', function(e) {
            const confirmMessage = this.getAttribute('onclick').match(/'([^']+)'/)[1];
            if (!confirm(confirmMessage)) {
                e.preventDefault();
            }
        });
    });
    
    // Close mobile menu on window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
        }
    });
</script>
</body>
</html>