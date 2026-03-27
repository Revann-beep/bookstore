<?php
session_start();
require '../auth/connection.php';

// Cegah cache browser
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

/* ======================
   VALIDASI LOGIN
====================== */
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'penjual') {
    die('Akses tolak');
}

$id_penjual = $_SESSION['id_user'];

/* ======================
   APPROVE PRODUK (PER PENJUAL)
====================== */
if (isset($_POST['approve'])) {

    $id_detail = $_POST['id_detail'];
    $id_order  = $_POST['id_order'];

    // pastikan produk milik penjual ini
    $cek = mysqli_query($conn, "
        SELECT 1
        FROM order_details od
        JOIN produk p ON p.id_produk = od.id_produk
        WHERE od.id_detail='$id_detail'
        AND p.id_penjual='$id_penjual'
    ");

    if (mysqli_num_rows($cek)) {

        mysqli_query($conn, "
            UPDATE order_details od
            JOIN produk p ON p.id_produk = od.id_produk
            SET od.status_detail='approved'
            WHERE od.id_detail='$id_detail'
            AND p.id_penjual='$id_penjual'
        ");

        // update status order global
        $q = mysqli_query($conn, "
            SELECT 
                SUM(status_detail='approved') AS approved,
                SUM(status_detail='tolak') AS tolak,
                COUNT(*) AS total
            FROM order_details
            WHERE id_order='$id_order'
        ");
        $r = mysqli_fetch_assoc($q);

        if ($r['approved'] == $r['total']) {
            mysqli_query($conn, "
                UPDATE orders SET status='siap_dikirim'
                WHERE id_order='$id_order'
            ");
        } else {
            mysqli_query($conn, "
                UPDATE orders SET status='parsial'
                WHERE id_order='$id_order'
            ");
        }
    }

    header("Location: approve.php");
    exit;
}

/* ======================
   TOLAK / REFUND (PER PENJUAL)
====================== */
if (isset($_POST['tolak'])) {

    $id_detail = (int)$_POST['id_detail'];
    $alasan = $_POST['alasan'];
$alasan_text = $_POST['alasan_text'] ?? '';

if($alasan == "Lainnya"){
    $alasan_tolak = mysqli_real_escape_string($conn, $alasan_text);
}else{
    $alasan_tolak = mysqli_real_escape_string($conn, $alasan);
}

    // ambil id_produk, qty dan status sebelumnya
    $ambil = mysqli_query($conn,"
        SELECT od.id_produk, od.qty, od.status_detail
        FROM order_details od
        JOIN produk p ON p.id_produk = od.id_produk
        WHERE od.id_detail='$id_detail'
        AND p.id_penjual='$id_penjual'
    ");

    $data = mysqli_fetch_assoc($ambil);

    if($data){

        $id_produk = $data['id_produk'];
        $qty = $data['qty'];
        $status_sebelumnya = $data['status_detail'];

        // kembalikan stok hanya jika sebelumnya belum ditolak
        if($status_sebelumnya != 'tolak'){
            mysqli_query($conn,"
                UPDATE produk
                SET stok = stok + $qty
                WHERE id_produk='$id_produk'
            ");
        }

        // update HANYA detail penjual ini
        mysqli_query($conn, "
            UPDATE order_details od
            JOIN produk p ON p.id_produk = od.id_produk
            SET 
                od.status_detail='tolak',
                od.alasan_tolak='$alasan_tolak',
                od.refund_at=NOW()
            WHERE od.id_detail='$id_detail'
            AND p.id_penjual='$id_penjual'
        ");
    }

    // ambil id_order
    $q = mysqli_query($conn, "
        SELECT id_order FROM order_details WHERE id_detail='$id_detail'
    ");
    $o = mysqli_fetch_assoc($q);
    $id_order = $o['id_order'];

    // cek kondisi order global
    $cek = mysqli_query($conn, "
        SELECT 
            SUM(status_detail='approved') AS approved,
            SUM(status_detail='tolak') AS tolak,
            COUNT(*) AS total
        FROM order_details
        WHERE id_order='$id_order'
    ");
    $r = mysqli_fetch_assoc($cek);

    if ($r['tolak'] == $r['total']) {
        // semua produk ditolak
        mysqli_query($conn, "
            UPDATE orders 
            SET status='refund', refund_at=NOW()
            WHERE id_order='$id_order'
        ");
    } else {
        // sebagian ditolak
        mysqli_query($conn, "
            UPDATE orders 
            SET status='parsial'
            WHERE id_order='$id_order'
        ");
    }

    header("Location: approve.php");
    exit;
}

/* ======================
   INPUT RESI (PER DETAIL / PER PENJUAL)
====================== */
if (isset($_POST['resi'])) {

    $id_detail = $_POST['id_detail'];
    $ekspedisi = mysqli_real_escape_string($conn, $_POST['ekspedisi']);
    $no_resi = mysqli_real_escape_string($conn, $_POST['no_resi']);
    
    // Generate link lacak berdasarkan ekspedisi
    $link_lacak = '';
    switch($ekspedisi) {
        case 'jne':
            $link_lacak = "https://cekresi.com/jne/$no_resi";
            break;
        case 'jnt':
            $link_lacak = "https://cekresi.com/jnt/$no_resi";
            break;
        case 'sicepat':
            $link_lacak = "https://cekresi.com/sicepat/$no_resi";
            break;
        case 'pos':
            $link_lacak = "https://cekresi.com/pos-indonesia/$no_resi";
            break;
        case 'tiki':
            $link_lacak = "https://cekresi.com/tiki/$no_resi";
            break;
        case 'wahana':
            $link_lacak = "https://cekresi.com/wahana/$no_resi";
            break;
        case 'ninja':
            $link_lacak = "https://cekresi.com/ninja-express/$no_resi";
            break;
        case 'anteraja':
            $link_lacak = "https://cekresi.com/anteraja/$no_resi";
            break;
        case 'idexpress':
            $link_lacak = "https://cekresi.com/idexpress/$no_resi";
            break;
        default:
            $link_lacak = "https://cekresi.com/track?resi=$no_resi";
    }

    // pastikan detail ini milik penjual & sudah approved
    $cek = mysqli_query($conn, "
        SELECT od.id_order
        FROM order_details od
        JOIN produk p ON p.id_produk = od.id_produk
        WHERE od.id_detail='$id_detail'
        AND p.id_penjual='$id_penjual'
        AND od.status_detail='approved'
        AND od.no_resi IS NULL
    ");

    if (!mysqli_num_rows($cek)) {
        header("Location: approve.php?error=tidak_valid");
        exit;
    }

    $d = mysqli_fetch_assoc($cek);
    $id_order = $d['id_order'];

    // simpan resi, ekspedisi, dan link lacak untuk detail ini
    mysqli_query($conn, "
                  UPDATE order_details
          SET 
              ekspedisi='$ekspedisi',
              no_resi='$no_resi',
              link_lacak='$link_lacak',
              status_detail='dikirim',
              shipped_at=NOW()
          WHERE id_detail='$id_detail'
    ");

    // update status global order (ringkasan)
    mysqli_query($conn, "
        UPDATE orders
        SET status='parsial'
        WHERE id_order='$id_order'
        AND status NOT IN ('refund','selesai')
    ");

    header("Location: approve.php?success=resi_tersimpan");
    exit;
}

/* ======================
   DELETE DETAIL SETELAH REFUND (60 DETIK)
====================== */
if (isset($_GET['delete'])) {

    $id_detail = $_GET['delete'];

    $cek = mysqli_query($conn, "
        SELECT od.refund_at
        FROM order_details od
        JOIN produk p ON p.id_produk = od.id_produk
        WHERE od.id_detail='$id_detail'
        AND p.id_penjual='$id_penjual'
    ");
    $d = mysqli_fetch_assoc($cek);

    if ($d && strtotime($d['refund_at']) <= time() - 60) {
        mysqli_query($conn, "
            DELETE od FROM order_details od
            JOIN produk p ON p.id_produk = od.id_produk
            WHERE od.id_detail='$id_detail'
            AND p.id_penjual='$id_penjual'
        ");
    }

    header("Location: approve.php");
    exit;
}

/* ======================
   PAGINATION
====================== */
$limit = 7; // Data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Hitung total data
$total_query = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM order_details od
    JOIN orders o ON o.id_order = od.id_order
    JOIN produk p ON p.id_produk = od.id_produk
    WHERE p.id_penjual = '$id_penjual'
");
$total_row = mysqli_fetch_assoc($total_query);
$total_data = $total_row['total'];
$total_pages = ceil($total_data / $limit);

// Query dengan limit
$q = mysqli_query($conn, "
    SELECT 
        od.id_detail,
        od.qty,
        od.nama_buku,
        od.status_detail,
        od.ekspedisi,
        od.no_resi,
        od.link_lacak,
        od.refund_at,
        o.id_order,
        o.kode_pesanan,
        o.metode_pembayaran,
        o.bukti_tf
    FROM order_details od
    JOIN orders o ON o.id_order = od.id_order
    JOIN produk p ON p.id_produk = od.id_produk
    WHERE p.id_penjual = '$id_penjual'
    ORDER BY o.id_order DESC
    LIMIT $offset, $limit
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Approve Pesanan | Aksara Jiwa</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .status-badge {
      @apply px-3 py-1 rounded-full text-xs font-medium;
    }
    .status-waiting { @apply bg-yellow-100 text-yellow-800; }
    .status-shipped { @apply bg-blue-100 text-blue-800; }
    .status-refund { @apply bg-red-100 text-red-800; }
    .status-completed { @apply bg-green-100 text-green-800; }
    
    .pagination-btn {
      @apply px-3 py-2 rounded-lg text-sm font-medium transition-colors;
    }
    .pagination-btn-active {
      @apply bg-indigo-600 text-white hover:bg-indigo-700;
    }
    .pagination-btn-inactive {
      @apply bg-white text-gray-700 hover:bg-gray-50 border border-gray-300;
    }
    .pagination-btn-disabled {
      @apply bg-gray-100 text-gray-400 cursor-not-allowed border border-gray-200;
    }
  </style>
</head>

<body class="bg-gray-50">
<div class="flex min-h-screen">
  <!-- Sidebar (tetap sama) -->
  <aside class="w-64 bg-white shadow-lg flex flex-col fixed h-full">
    <!-- LOGO -->
    <div class="p-6 border-b">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
          <i class="fas fa-book text-white"></i>
        </div>
        <div>
          <h2 class="font-bold text-gray-800">Aksara Jiwa</h2>
          <p class="text-xs text-gray-500">Penjual Dashboard</p>
        </div>
      </div>
    </div>

    <!-- MENU -->
    <div class="flex-1 overflow-y-auto">
      <nav class="p-4 space-y-1">
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
          <i class="fas fa-chart-line w-5"></i> Dashboard
        </a>
        
        <a href="produk.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
          <i class="fas fa-box-open w-5"></i> Produk
        </a>
        <a href="kategori.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
                    <i class="fas fa-tags w-5"></i> Kategori
                </a>
        <a href="approve.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-indigo-50 text-indigo-600 font-medium">
          <i class="fas fa-check-circle w-5"></i> Approve
        </a>
        <a href="laporan.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
          <i class="fas fa-file-alt w-5"></i> Laporan
        </a>
        <a href="chat.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
          <i class="fas fa-comments w-5"></i> Chat
        </a>
        <a href="admin.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
          <i class="fas fa-store w-5"></i> Data Penjual
        </a>
        <a href="akun_saya.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
          <i class="fas fa-user-circle w-5"></i> Akun Saya
        </a>
      </nav>
    </div>

    <!-- LOGOUT -->
    <div class="p-4 border-t mt-auto">
      <a href="../auth/logout.php" class="flex items-center gap-3 text-red-500 hover:text-red-600">
        <i class="fas fa-sign-out-alt"></i> Keluar
      </a>
    </div>
  </aside>

  <!-- CONTENT -->
  <main class="flex-1 ml-64 p-6 overflow-y-auto h-screen">
    <!-- HEADER -->
    <div class="mb-8">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-lg flex items-center justify-center">
          <i class="fas fa-check-circle text-white text-xl"></i>
        </div>
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Approve Pesanan</h1>
          <p class="text-gray-600">Kelola dan verifikasi pesanan pelanggan</p>
        </div>
      </div>
      
      <!-- Info Pagination -->
      <div class="flex justify-between items-center mt-4">
        <p class="text-sm text-gray-600">
          Menampilkan <?= min($offset + 1, $total_data) ?> - <?= min($offset + $limit, $total_data) ?> dari <?= $total_data ?> pesanan
        </p>
      </div>
    </div>

    <!-- TABLE -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="p-4 text-left font-semibold text-gray-700">Kode Pesanan</th>
              <th class="p-4 text-left font-semibold text-gray-700">Produk</th>
              <th class="p-4 text-left font-semibold text-gray-700">Qty</th>
              <th class="p-4 text-left font-semibold text-gray-700">Bukti TF</th>
              <th class="p-4 text-left font-semibold text-gray-700">Metode</th>
              <th class="p-4 text-left font-semibold text-gray-700">Status</th>
              <th class="p-4 text-left font-semibold text-gray-700">No. Resi</th>
              <th class="p-4 text-left font-semibold text-gray-700">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($q && mysqli_num_rows($q) > 0) {
                while ($o = mysqli_fetch_assoc($q)) {
                    // Determine status badge class
                    $statusClass = '';
                    switch($status = $o['status_detail'] ?? 'pending') {
                        case 'menunggu_verifikasi': $statusClass = 'status-waiting'; break;
                        case 'dikirim': $statusClass = 'status-shipped'; break;
                        case 'refund': $statusClass = 'status-refund'; break;
                        case 'selesai': $statusClass = 'status-completed'; break;
                        default: $statusClass = 'bg-gray-100 text-gray-800';
                    }
                    
                    // Parse bukti TF
                    $bukti = null;
                    if (!empty($o['bukti_tf'])) {
                        $bukti_arr = json_decode($o['bukti_tf'], true);
                        if ($bukti_arr && isset($bukti_arr[$id_penjual]['file'])) {
                            $bukti = $bukti_arr[$id_penjual]['file'];
                        }
                    }
                    
                    // Format ekspedisi untuk tampilan
                    $nama_ekspedisi = '';
                    if (!empty($o['ekspedisi'])) {
                        $ekspedisi_list = [
                            'jne' => 'JNE',
                            'jnt' => 'J&T',
                            'sicepat' => 'SiCepat',
                            'pos' => 'Pos Indonesia',
                            'tiki' => 'TIKI',
                            'wahana' => 'Wahana',
                            'ninja' => 'Ninja Express',
                            'anteraja' => 'AnterAja',
                            'idexpress' => 'ID Express'
                        ];
                        $nama_ekspedisi = $ekspedisi_list[$o['ekspedisi']] ?? ucfirst($o['ekspedisi']);
                    }
            ?>
            <tr class="border-b hover:bg-gray-50">
              <td class="p-4">
                <span class="font-medium text-gray-800"><?= htmlspecialchars($o['kode_pesanan']) ?></span>
              </td>
              <td class="p-4 text-gray-800"><?= htmlspecialchars($o['nama_buku']) ?></td>
              <td class="p-4">
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                  <?= htmlspecialchars($o['qty']) ?>
                </span>
              </td>
              <td class="p-4">
                <?php if ($bukti): ?>
                <a href="../img/bukti/<?= htmlspecialchars($bukti) ?>" target="_blank"
                   class="text-indigo-600 hover:underline flex items-center gap-1">
                   <i class="fas fa-eye text-sm"></i> Lihat
                </a>
                <?php else: ?>
                <span class="text-gray-400 text-sm">Belum ada</span>
                <?php endif; ?>
              </td>
              <td class="p-4 text-gray-700"><?= htmlspecialchars($o['metode_pembayaran']) ?></td>
              <td class="p-4">
                <span class="status-badge <?= $statusClass ?>">
                  <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $o['status_detail']))) ?>
                </span>
              </td>
              <td class="p-4">
                <?php if($o['no_resi']) { ?>
                <div class="flex flex-col">
                  <span class="text-xs text-gray-500"><?= $nama_ekspedisi ?></span>
                  <a href="<?= htmlspecialchars($o['link_lacak']) ?>" 
                     target="_blank"
                     class="text-indigo-600 hover:underline text-sm">
                     <i class="fas fa-truck"></i> <?= htmlspecialchars($o['no_resi']) ?>
                  </a>
                </div>
                <?php } else { ?>
                <span class="text-gray-400 text-sm">Belum ada</span>
                <?php } ?>
              </td>
              <td class="p-4">
                <div class="flex flex-wrap gap-2">
                  <?php if (in_array($o['status_detail'], ['pending','menunggu_verifikasi']))  { ?>
                    <!-- APPROVE -->
                    <form method="post" action="approve.php">
                      <input type="hidden" name="id_detail" value="<?= $o['id_detail'] ?>">
                      <input type="hidden" name="id_order" value="<?= $o['id_order'] ?>">
                      <button type="submit" name="approve"
                        class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 flex items-center gap-1">
                        <i class="fas fa-check text-xs"></i> Approve
                      </button>
                    </form>

                    <!-- TOLAK -->
                    <button onclick="bukaModalTolak(<?= $o['id_detail'] ?>)"
                      class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 flex items-center gap-1">
                      <i class="fas fa-times text-xs"></i> Tolak
                    </button>
                  <?php } ?>

                  <?php
                  // cek apakah ada minimal 1 item approved di order ini
                  $cekApprove = mysqli_query($conn, "
                      SELECT COUNT(*) AS total
                      FROM order_details
                      WHERE id_order='{$o['id_order']}'
                      AND status_detail='approved'
                  ");
                  $ap = mysqli_fetch_assoc($cekApprove);
                  ?>

                  <?php if ($o['status_detail'] == 'approved' && empty($o['no_resi'])) { ?>
                    <!-- INPUT RESI DENGAN DROPDOWN EKSPEDISI -->
                    <form method="post" action="approve.php" class="flex flex-col gap-2 min-w-[250px] border border-gray-200 p-3 rounded-lg bg-gray-50">
                      <input type="hidden" name="id_detail" value="<?= $o['id_detail'] ?>">
                      
                      <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Pilih Ekspedisi</label>
                        <select name="ekspedisi" required
                          class="w-full border border-gray-300 px-3 py-1.5 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 bg-white">
                          <option value="">-- Pilih Ekspedisi --</option>
                          <option value="jne">JNE</option>
                          <option value="jnt">J&T Express</option>
                          <option value="sicepat">SiCepat</option>
                          <option value="pos">Pos Indonesia</option>
                          <option value="tiki">TIKI</option>
                          <option value="wahana">Wahana</option>
                          <option value="ninja">Ninja Express</option>
                          <option value="anteraja">AnterAja</option>
                          <option value="idexpress">ID Express</option>
                        </select>
                      </div>
                      
                      <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">No. Resi</label>
                        <input type="text" name="no_resi" placeholder="Masukkan No. Resi"
                          class="w-full border border-gray-300 px-3 py-1.5 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"
                          required>
                      </div>
                      
                      <button type="submit" name="resi"
                        class="mt-1 px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 flex items-center justify-center gap-1">
                        <i class="fas fa-save text-xs"></i> Simpan Resi
                      </button>
                    </form>
                  <?php } ?>

                  <?php 
                        if (
                            in_array($o['status_detail'], ['tolak','refund']) &&
                            !empty($o['refund_at']) &&
                            strtotime($o['refund_at']) <= time() - 60
                        ) { 
                        ?>
                    <a href="approve.php?delete=<?= $o['id_detail'] ?>&page=<?= $page ?>"
                       onclick="return confirm('Yakin hapus pesanan ini?')"
                       class="px-3 py-1.5 bg-gray-700 text-white rounded-lg text-sm hover:bg-gray-800 flex items-center gap-1">
                      <i class="fas fa-trash-alt text-xs"></i> Delete
                    </a>
                  <?php } ?>
                </div>
              </td>
            </tr>
            <?php 
                }
            } else {
            ?>
            <tr>
              <td colspan="8" class="p-4 text-center text-gray-500">
                Tidak ada pesanan yang perlu disetujui.
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- PAGINATION -->
    <?php if ($total_pages > 1): ?>
    <div class="mt-6 flex justify-center">
      <div class="flex gap-2">
        <!-- Tombol Previous -->
        <?php if ($page > 1): ?>
          <a href="?page=<?= $page - 1 ?>" class="pagination-btn pagination-btn-inactive">
            <i class="fas fa-chevron-left text-xs"></i> Prev
          </a>
        <?php else: ?>
          <span class="pagination-btn pagination-btn-disabled">
            <i class="fas fa-chevron-left text-xs"></i> Prev
          </span>
        <?php endif; ?>

        <!-- Nomor Halaman -->
        <?php
        $start_page = max(1, $page - 2);
        $end_page = min($total_pages, $page + 2);
        
        for ($i = $start_page; $i <= $end_page; $i++):
        ?>
          <a href="?page=<?= $i ?>" 
             class="pagination-btn <?= $i == $page ? 'pagination-btn-active' : 'pagination-btn-inactive' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>

        <!-- Tombol Next -->
        <?php if ($page < $total_pages): ?>
          <a href="?page=<?= $page + 1 ?>" class="pagination-btn pagination-btn-inactive">
            Next <i class="fas fa-chevron-right text-xs"></i>
          </a>
        <?php else: ?>
          <span class="pagination-btn pagination-btn-disabled">
            Next <i class="fas fa-chevron-right text-xs"></i>
          </span>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- FOOTNOTE -->
    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
      <div class="flex items-start gap-3">
        <i class="fas fa-info-circle text-yellow-600 mt-0.5"></i>
        <div>
          <p class="text-sm text-gray-700">
            <strong>Catatan:</strong> 
            Tombol Delete akan aktif 1 menit setelah status refund. 
            Pastikan konfirmasi refund telah diproses sebelum menghapus data.
          </p>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- MODAL TOLAK -->
<!-- MODAL TOLAK -->
<div id="modalTolak" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50">

  <div class="bg-white rounded-xl p-6 w-[400px] shadow-lg">

    <h2 class="text-lg font-semibold mb-4">Alasan Penolakan</h2>

    <form method="post" action="approve.php">

      <input type="hidden" name="id_detail" id="id_detail_tolak">

      <!-- DROPDOWN -->
      <label class="text-sm text-gray-600">Pilih Alasan</label>
      <select name="alasan" id="alasanSelect"
        class="w-full border rounded-lg px-3 py-2 mt-1 mb-3" required>

        <option value="">-- Pilih Alasan --</option>
        <option value="Uang transfer kurang">Uang transfer kurang</option>
        <option value="Bukti transfer palsu">Bukti transfer palsu</option>
        <option value="Nominal tidak sesuai">Nominal tidak sesuai</option>
        <option value="Lainnya">Lainnya</option>

      </select>

      <!-- TEXT TAMBAHAN -->
      <textarea
        name="alasan_text"
        id="alasanText"
        placeholder="Tulis alasan lainnya..."
        class="w-full border rounded-lg px-3 py-2 mb-4 hidden"></textarea>

      <div class="flex justify-end gap-2">

        <button type="button"
          onclick="closeModal()"
          class="px-4 py-2 bg-gray-300 rounded-lg">
          Batal
        </button>

        <button type="submit"
          name="tolak"
          class="px-4 py-2 bg-red-600 text-white rounded-lg">
          Tolak Pesanan
        </button>

      </div>

    </form>

  </div>
</div>


<script>

function bukaModalTolak(id_detail) {

  const modal = document.getElementById("modalTolak");
  modal.classList.remove("hidden");
  modal.classList.add("flex");

  document.getElementById("id_detail_tolak").value = id_detail;

}

function closeModal() {

  const modal = document.getElementById("modalTolak");
  modal.classList.remove("flex");
  modal.classList.add("hidden");

}


// tunggu halaman selesai load
document.addEventListener("DOMContentLoaded", function(){

  const select = document.getElementById("alasanSelect");
  const text = document.getElementById("alasanText");

  select.addEventListener("change", function(){

    if(this.value === "Lainnya"){
      text.classList.remove("hidden");
      text.required = true;
    }else{
      text.classList.add("hidden");
      text.required = false;
    }

  });

});

</script>

</body>
</html>