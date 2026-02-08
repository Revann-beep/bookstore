<?php
session_start();
require '../auth/connection.php';

/* ======================
   FUNCTION GLOBAL
====================== */
function semuaPenjualSudahApprove($conn, $id_order) {
    $q = mysqli_query($conn, "
        SELECT COUNT(*) AS sisa
        FROM order_details
        WHERE id_order='$id_order'
        AND status_detail!='approved'
    ");
    $c = mysqli_fetch_assoc($q);
    return $c['sisa'] == 0;
}


if (!isset($_SESSION['id_user'])) {
    die('SESSION id_user hilang');
}

if ($_SESSION['role'] !== 'penjual') {
    die('Bukan penjual');
}

$id_penjual = $_SESSION['id_user'];

/* ======================
   APPROVE ORDER (PER PENJUAL)
====================== */
if (isset($_POST['approve'])) {
    if (!isset($_POST['id_detail']) || !isset($_POST['id_order'])) {
        header("Location: approve.php");
        exit;
    }

    $id_detail = $_POST['id_detail'];
    $id_order  = $_POST['id_order'];

    // pastikan produk milik penjual ini
    $cek = mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM order_details od
        JOIN produk p ON p.id_produk = od.id_produk
        WHERE od.id_detail='$id_detail'
        AND p.id_penjual='$id_penjual'
    ");

    $c = mysqli_fetch_assoc($cek);

    if ($c['total'] > 0) {

        // approve hanya detail milik penjual ini
        mysqli_query($conn, "
            UPDATE order_details od
            JOIN produk p ON p.id_produk = od.id_produk
            SET od.status_detail='approved'
            WHERE od.id_detail='$id_detail'
            AND p.id_penjual='$id_penjual'
        ");

        // cek apakah semua penjual sudah approve
        $cek_all = mysqli_query($conn, "
            SELECT COUNT(*) AS sisa
            FROM order_details
            WHERE id_order='$id_order'
            AND status_detail!='approved'
        ");

        $all = mysqli_fetch_assoc($cek_all);

        // kalau semua sudah approve → siap kirim
        if ($all['sisa'] == 0) {
            mysqli_query($conn, "
                UPDATE orders
                SET status='siap_dikirim'
                WHERE id_order='$id_order'
            ");
        }
    }

    header("Location: approve.php");
    exit;
}

/* ======================
   TOLAK / REFUND
====================== */
if (isset($_POST['tolak']) && isset($_POST['id_detail'])) {
    $id_detail = $_POST['id_detail'];

    mysqli_query($conn, "
        UPDATE order_details od
        JOIN produk p ON p.id_produk = od.id_produk
        JOIN orders o ON o.id_order = od.id_order
        SET 
            od.status='refund',
            o.status='refund',
            o.refund_at=NOW()
        WHERE od.id_detail='$id_detail'
        AND p.id_penjual='$id_penjual'
    ");

    header("Location: approve.php");
    exit;
}

/* ======================
   INPUT RESI (GLOBAL & TERKUNCI)
====================== */
if (isset($_POST['resi'])) {
    $id_detail = $_POST['id_detail'];
    $resi = mysqli_real_escape_string($conn, $_POST['no_resi']);

    // ambil id_order
    $q = mysqli_query($conn, "
        SELECT id_order FROM order_details WHERE id_detail='$id_detail'
    ");
    $o = mysqli_fetch_assoc($q);
    $id_order = $o['id_order'];

    // ❌ TOLAK kalau belum semua approve
    if (!semuaPenjualSudahApprove($conn, $id_order)) {
        header("Location: approve.php?error=belum_semua_approve");
        exit;
    }

    // simpan resi (HANYA SEKALI)
    mysqli_query($conn, "
        UPDATE orders
        SET 
            no_resi='$resi',
            link_lacak='https://tracking-dummy.com/lacak?resi=$resi',
            status='dikirim'
        WHERE id_order='$id_order'
        AND no_resi IS NULL
    ");

    header("Location: approve.php");
    exit;
}

/* ======================
   DELETE SETELAH REFUND
====================== */
if (isset($_GET['delete'])) {
    $id_detail = $_GET['delete'];

    $cek = mysqli_query($conn, "
        SELECT o.refund_at
        FROM order_details od
        JOIN produk p ON p.id_produk = od.id_produk
        JOIN orders o ON o.id_order = od.id_order
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
            $q = mysqli_query($conn, "
                SELECT 
                    od.id_detail,
                    od.qty,
                    od.nama_buku,
                    o.id_order,
                    o.status,
                    o.no_resi,
                    o.link_lacak,
                    o.refund_at,
                    o.kode_pesanan,
                    o.metode_pembayaran,
                    o.bukti_tf
                FROM order_details od
                JOIN orders o ON o.id_order = od.id_order
                JOIN produk p ON p.id_produk = od.id_produk
                WHERE p.id_penjual = '$id_penjual'
                ORDER BY o.id_order DESC
            ");
            
            if ($q && mysqli_num_rows($q) > 0) {
                while ($o = mysqli_fetch_assoc($q)) {
                    // Determine status badge class
                    $statusClass = '';
                    switch($o['status']) {
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
                  <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $o['status']))) ?>
                </span>
              </td>
              <td class="p-4">
                <?php if($o['no_resi']) { ?>
                <a href="../auth/track.php?resi=<?= urlencode($o['no_resi']) ?>"
                   target="_blank"
                   class="text-indigo-600 hover:underline text-sm">
                   Lacak Pesanan
                </a>
                <?php } else { ?>
                <span class="text-gray-400 text-sm">Belum ada</span>
                <?php } ?>
              </td>
              <td class="p-4">
                <div class="flex flex-wrap gap-2">
                  <?php if ($o['status'] == 'menunggu_verifikasi') { ?>
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
                    <form method="post" action="approve.php">
                      <input type="hidden" name="id_detail" value="<?= $o['id_detail'] ?>">
                      <button type="submit" name="tolak"
                        class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 flex items-center gap-1">
                        <i class="fas fa-times text-xs"></i> Tolak
                      </button>
                    </form>
                  <?php } ?>

                  <?php if (semuaPenjualSudahApprove($conn, $o['id_order']) && empty($o['no_resi'])) { ?>
                    <!-- INPUT RESI -->
                    <form method="post" action="approve.php" class="flex items-center gap-2">
                      <input type="hidden" name="id_detail" value="<?= $o['id_detail'] ?>">
                      <input type="text" name="no_resi" placeholder="No Resi"
                        class="border border-gray-300 px-3 py-1.5 rounded-lg text-sm w-32 focus:ring-2 focus:ring-indigo-500"
                        required>
                      <button type="submit" name="resi"
                        class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 flex items-center gap-1">
                        <i class="fas fa-save text-xs"></i> Simpan
                      </button>
                    </form>
                  <?php } ?>

                  <?php if ($o['status'] == 'refund' && $o['refund_at'] && strtotime($o['refund_at']) <= time() - 60) { ?>
                    <a href="approve.php?delete=<?= $o['id_detail'] ?>"
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
</body>
</html>