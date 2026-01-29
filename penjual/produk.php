<?php
session_start();
require '../auth/connection.php';

// CEGAH AKSES SELAIN PENJUAL
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../login.php");
    exit;
}

$id_penjual = $_SESSION['id_user'];



// SEARCH
$search = $_GET['search'] ?? '';

// AMBIL PRODUK MILIK SENDIRI
$produk = mysqli_query($conn, "
    SELECT p.*, k.nama_kategori
    FROM produk p
    LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
    WHERE p.id_penjual = '$id_penjual'
    AND p.nama_buku LIKE '%$search%'
    ORDER BY p.id_produk DESC
");

// HAPUS PRODUK (HANYA JIKA STOK = 0)
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    $cek = mysqli_query($conn, "
        SELECT stok FROM produk
        WHERE id_produk='$id'
        AND id_penjual='$id_penjual'
    ");
    $row = mysqli_fetch_assoc($cek);

    if ($row && $row['stok'] == 0) {
        mysqli_query($conn, "DELETE FROM produk WHERE id_produk='$id'");
        $_SESSION['success'] = "Produk berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Produk tidak bisa dihapus karena stok masih ada!";
    }

    header("Location: produk.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk Saya | Aksara Jiwa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="../style/produkpenjual.css">
</head>
<body class="bg-gray-50 min-h-screen">

<div class="flex min-h-screen">

<!-- SIDEBAR -->
<aside class="w-64 bg-white shadow-xl flex flex-col h-screen sticky top-0">
    <!-- LOGO -->
    <div class="p-6 flex items-center gap-3 border-b border-gray-100">
        <div class="w-12 h-12 gradient-bg text-white rounded-xl flex items-center justify-center font-bold text-xl shadow-lg">
            <i class="fas fa-book-open"></i>
        </div>
        <div>
            <span class="font-bold text-xl text-gray-800 title-font">Aksara Jiwa</span>
            <p class="text-xs text-gray-500">Penjual Dashboard</p>
        </div>
    </div>

    <!-- MENU -->
    <div class="flex-1 px-4 py-6 space-y-1">
        <!-- Dashboard -->
        <a href="dashboard.php"
           class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
           <i class="fas fa-chart-line w-5 text-center"></i>
           <span>Dashboard</span>
        </a>
        <!-- Data Penjual -->
        

        <!-- Produk (Active) -->
        <a href="produk.php"
           class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
           <i class="fas fa-box-open w-5 text-center"></i>
           <span>Produk Saya</span>
        </a>

        <!-- Approve -->
        <div class="border border-gray-100 rounded-lg mt-2">
            <button onclick="toggleApprove()"
                    class="w-full flex items-center justify-between px-4 py-3 text-gray-700 hover:bg-indigo-50 rounded-lg">
                <span class="flex items-center gap-3">
                    <i class="fas fa-check-circle w-5 text-center"></i>
                    <span>Approval</span>
                </span>
                <span id="iconApprove" class="transform transition-transform">▼</span>
            </button>

            <div id="approveMenu" class="hidden pl-12 pr-4 pb-2 space-y-2 mt-2">
                <a href="approve.php" class="block px-3 py-2 rounded-lg hover:bg-gray-100 text-sm text-gray-600 hover:text-gray-900">
                    Approve Pesanan
                </a>
                <a href="laporan.php" class="block px-3 py-2 rounded-lg hover:bg-gray-100 text-sm text-gray-600 hover:text-gray-900">
                    Laporan Penjualan
                </a>
                <a href="chat.php" class="block px-3 py-2 rounded-lg hover:bg-gray-100 text-sm text-gray-600 hover:text-gray-900">
                    Chat Pelanggan
                </a>
            </div>
        </div>

        <a href="admin.php"
           class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
           <i class="fas fa-store w-5 text-center"></i>
           <span>Data Penjual</span>
        </a>

        <!-- My Account -->
        <a href="akun_saya.php"
           class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
           <i class="fas fa-user-circle w-5 text-center"></i>
           <span>Akun Saya</span>
        </a>

        <!-- Sign Out -->
        <div class="pt-4 mt-4 border-t border-gray-100">
            <a href="../auth/logout.php"
               class="flex items-center gap-3 px-4 py-3 rounded-lg text-red-500 hover:bg-red-50">
               <i class="fas fa-sign-out-alt w-5 text-center"></i>
               <span>Keluar</span>
            </a>
        </div>
    </div>

    <!-- HELP -->
    <div class="px-4 py-4 border-t border-gray-100">
        <a href="help.php"
           class="flex items-center gap-3 text-gray-500 hover:text-indigo-600">
           <i class="fas fa-question-circle"></i>
           <span class="text-sm">Bantuan & Dukungan</span>
        </a>
        <div class="mt-3 text-xs text-gray-400 px-1">
            <p>© 2024 Aksara Jiwa</p>
            <p>Versi 2.1.0</p>
        </div>
    </div>
</aside>

<!-- MAIN CONTENT -->
<main class="flex-1 p-6 md:p-8">
    <!-- HEADER -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 title-font">Produk Saya</h1>
                <p class="text-gray-600 mt-2">Kelola dan pantau produk Anda di satu tempat</p>
            </div>
            <a href="../auth/add_buku.php"
               class="btn-primary px-6 py-3 rounded-xl font-semibold inline-flex items-center gap-2 whitespace-nowrap">
                <i class="fas fa-plus"></i>
                Tambah Produk Baru
            </a>
        </div>

        <!-- STATS BAR -->
        <?php
            $totalProduk = mysqli_num_rows($produk);
            mysqli_data_seek($produk, 0);
            
            $totalStok = 0;
            $totalKeuntungan = 0;
            while($row = mysqli_fetch_assoc($produk)) {
                $margin = $row['harga'] - $row['modal'];
                $keuntungan = $margin * $row['stok'];
                $totalStok += $row['stok'];
                $totalKeuntungan += $keuntungan;
            }
            mysqli_data_seek($produk, 0);
        ?>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="stats-card p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-90">Total Produk</p>
                        <h3 class="text-2xl font-bold mt-1"><?= $totalProduk ?></h3>
                    </div>
                    <i class="fas fa-boxes text-2xl opacity-80"></i>
                </div>
            </div>
            
            <div class="bg-white card-shadow p-5 rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Stok</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= $totalStok ?></h3>
                    </div>
                    <i class="fas fa-layer-group text-2xl text-indigo-500"></i>
                </div>
            </div>
            
            <div class="bg-white card-shadow p-5 rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Perkiraan Keuntungan</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1">Rp<?= number_format($totalKeuntungan) ?></h3>
                    </div>
                    <i class="fas fa-coins text-2xl text-green-500"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- SEARCH & FILTER -->
    <div class="bg-white card-shadow rounded-xl p-5 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex-1 relative">
                <form method="GET">
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search"
                               value="<?= htmlspecialchars($search) ?>"
                               placeholder="Cari produk berdasarkan nama buku..."
                               class="search-input w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-400">
                        <?php if($search): ?>
                            <a href="produk.php" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-red-500">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="flex items-center gap-2">
                <button class="px-4 py-3 border border-gray-200 rounded-xl hover:bg-gray-50 flex items-center gap-2">
                    <i class="fas fa-filter"></i>
                    Filter
                </button>
                <button class="px-4 py-3 border border-gray-200 rounded-xl hover:bg-gray-50 flex items-center gap-2">
                    <i class="fas fa-sort"></i>
                    Urutkan
                </button>
            </div>
        </div>
    </div>

    <!-- PRODUCT LIST -->
    <?php if (mysqli_num_rows($produk) == 0): ?>
        <div class="bg-white card-shadow rounded-xl p-8 text-center">
            <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum ada produk</h3>
            <p class="text-gray-500 mb-6">Mulai dengan menambahkan produk pertama Anda</p>
            <a href="../auth/add_buku.php" class="btn-primary px-6 py-3 rounded-xl font-semibold inline-flex items-center gap-2">
                <i class="fas fa-plus"></i>
                Tambah Produk Pertama
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($row = mysqli_fetch_assoc($produk)) :
                $margin = $row['harga'] - $row['modal'];
                $keuntungan = $margin * $row['stok'];
                $marginPercentage = $row['modal'] > 0 ? round(($margin / $row['modal']) * 100) : 0;
            ?>
                <div class="bg-white card-shadow rounded-2xl overflow-hidden border border-gray-100">
                    <!-- IMAGE SECTION -->
                    <div class="relative">
                        <?php if (!empty($row['gambar']) && file_exists("../img/produk/" . $row['gambar'])): ?>
                            <img src="../img/produk/<?= htmlspecialchars($row['gambar']) ?>"
                                 alt="<?= htmlspecialchars($row['nama_buku']) ?>"
                                 class="w-full h-56 object-cover">
                        <?php else: ?>
                            <div class="w-full h-56 bg-gradient-to-r from-indigo-50 to-purple-50 flex flex-col items-center justify-center text-gray-400">
                                <i class="fas fa-book text-4xl mb-3"></i>
                                <p>Gambar tidak tersedia</p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- STATUS BADGES -->
                        <div class="absolute top-3 left-3 flex gap-2">
                            <span class="badge badge-stock">
                                <i class="fas fa-cube text-xs mr-1"></i>
                                Stok: <?= $row['stok'] ?>
                            </span>
                            <?php if($row['nama_kategori']): ?>
                                <span class="badge badge-category">
                                    <?= $row['nama_kategori'] ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- CONTENT SECTION -->
                    <div class="p-5">
                        <h3 class="font-bold text-lg text-gray-800 mb-2 truncate"><?= htmlspecialchars($row['nama_buku']) ?></h3>
                        
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                            <?= nl2br(htmlspecialchars(substr($row['deskripsi'], 0, 100))) ?>...
                        </p>
                        
                        <!-- PRICE INFO -->
                        <div class="space-y-3 mb-5">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Harga Jual</span>
                                <span class="font-bold text-lg text-gray-800">Rp<?= number_format($row['harga']) ?></span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Modal</span>
                                <span class="text-gray-700">Rp<?= number_format($row['modal']) ?></span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Margin</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-green-600">Rp<?= number_format($margin) ?></span>
                                    <span class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded-full">
                                        +<?= $marginPercentage ?>%
                                    </span>
                                </div>
                            </div>
                            
                            <div class="pt-3 border-t border-gray-100">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Keuntungan Potensial</span>
                                    <span class="font-bold text-green-600">Rp<?= number_format($keuntungan) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- ACTION BUTTONS -->
                        <div class="flex gap-3">
                            <a href="../auth/edit_buku.php?id=<?= $row['id_produk'] ?>"
                               class="flex-1 btn-accent py-3 rounded-xl text-center font-medium flex items-center justify-center gap-2">
                                <i class="fas fa-edit"></i>
                                Edit
                            </a>
                            
                            <?php if ($row['stok'] == 0): ?>
                                <a href="?hapus=<?= $row['id_produk'] ?>"
                                   onclick="return hapusProduk(<?= $row['id_produk'] ?>)"
                                   class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 rounded-xl text-center font-medium flex items-center justify-center gap-2 transition-all duration-300">
                                    <i class="fas fa-trash-alt"></i>
                                    Hapus
                                </a>
                            <?php else: ?>
                                <button onclick="hapusProduk(<?= $row['id_produk'] ?>)"
                                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-xl text-center font-medium flex items-center justify-center gap-2 cursor-not-allowed opacity-70"
                                        title="Produk hanya dapat dihapus jika stok 0">
                                    <i class="fas fa-trash-alt"></i>
                                    Hapus
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <!-- PAGINATION (Placeholder) -->
        <div class="mt-8 flex justify-center">
            <div class="flex items-center gap-2">
                <button class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="w-10 h-10 flex items-center justify-center rounded-lg bg-indigo-500 text-white">1</button>
                <button class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50">2</button>
                <button class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50">3</button>
                <button class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    <?php endif; ?>
</main>
</div>

<script>
    function toggleApprove() {
        const menu = document.getElementById('approveMenu');
        const icon = document.getElementById('iconApprove');
        
        menu.classList.toggle('hidden');
        icon.classList.toggle('rotate-180');
    }

    function hapusProduk(id) {
        Swal.fire({
            title: 'Konfirmasi Hapus Produk',
            text: 'Apakah Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-2xl'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '?hapus=' + id;
            }
        });
        return false;
    }

    <?php if (isset($_SESSION['success'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '<?= $_SESSION['success'] ?>',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        background: '#f0fdf4',
        iconColor: '#10b981',
        color: '#065f46'
    });
    <?php unset($_SESSION['success']); endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '<?= $_SESSION['error'] ?>',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        background: '#fef2f2',
        iconColor: '#ef4444',
        color: '#7f1d1d'
    });
    <?php unset($_SESSION['error']); endif; ?>

    <?php if (isset($_SESSION['alert'])): ?>
    Swal.fire({
        icon: '<?= $_SESSION['alert']['type'] ?>',
        title: '<?= $_SESSION['alert']['title'] ?>',
        text: '<?= $_SESSION['alert']['text'] ?>',
        confirmButtonColor: '#4f46e5'
    });
    <?php unset($_SESSION['alert']); endif; ?>
</script>

</body>
</html>