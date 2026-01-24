<?php
require '../auth/connection.php';

/* ======================
   HANDLE AKSI
====================== */



// APPROVE
if (isset($_POST['approve'])) {
  $id = $_POST['id'];
  mysqli_query($conn, "
    UPDATE orders 
    SET status='dikirim' 
    WHERE id_order='$id'
  ");
  header("Location: approve.php");
}

// TOLAK
if (isset($_POST['tolak'])) {
  $id = $_POST['id'];
  mysqli_query($conn, "
    UPDATE orders 
    SET status='refund', refund_at=NOW() 
    WHERE id_order='$id'
  ");
  header("Location: approve.php");
}

// INPUT RESI
// INPUT RESI + LINK LACAK
if (isset($_POST['resi'])) {
  $id   = $_POST['id'];
  $resi = $_POST['no_resi'];

  // link lacak dummy (pura-pura)
  $link_lacak = "https://tracking-dummy.com/lacak?resi=".$resi;

  mysqli_query($conn, "
    UPDATE orders 
    SET 
      no_resi='$resi',
      link_lacak='$link_lacak'
    WHERE id_order='$id'
  ");

  header("Location: approve.php");
}


// DELETE (setelah refund 1 menit)
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];

  $cek = mysqli_query($conn, "
    SELECT refund_at FROM orders 
    WHERE id_order='$id'
  ");
  $data = mysqli_fetch_assoc($cek);

  if (strtotime($data['refund_at']) <= time() - 60) {
    mysqli_query($conn, "DELETE FROM orders WHERE id_order='$id'");
  }

  header("Location: approve.php");
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
  <!-- Sidebar -->
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
              SELECT o.*, od.qty, od.nama_buku
              FROM orders o
              JOIN order_details od ON od.id_order = o.id_order
              ORDER BY o.id_order DESC
            ");

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
            ?>
            <tr class="border-b hover:bg-gray-50">
              <td class="p-4">
                <span class="font-medium text-gray-800"><?= $o['kode_pesanan'] ?></span>
              </td>
              <td class="p-4 text-gray-800"><?= $o['nama_buku'] ?></td>
              <td class="p-4">
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                  <?= $o['qty'] ?>
                </span>
              </td>
              <td class="p-4">
                <a href="../img/bukti/<?= $o['bukti_tf'] ?>" target="_blank" 
                   class="text-indigo-600 hover:text-indigo-800 hover:underline flex items-center gap-1">
                  <i class="fas fa-eye text-sm"></i>
                  <span class="text-sm">Lihat</span>
                </a>
              </td>
              <td class="p-4 text-gray-700"><?= $o['metode_pembayaran'] ?></td>
              <td class="p-4">
                <span class="status-badge <?= $statusClass ?>">
                  <?= ucfirst(str_replace('_', ' ', $o['status'])) ?>
                </span>
              </td>
              <td class="p-4">
  <?php if($o['no_resi']) { ?>
  <a href="../auth/track.php?resi=<?= $o['no_resi'] ?>"
     target="_blank"
     class="text-indigo-600 hover:underline text-sm">
     Lacak Pesanan
  </a>
<?php } ?>

</td>

              <td class="p-4">
                <div class="flex flex-wrap gap-2">
                  <?php if ($o['status']=='menunggu_verifikasi') { ?>
                    <form method="post" class="inline">
                      <input type="hidden" name="id" value="<?= $o['id_order'] ?>">
                      <button name="approve" 
                              class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 flex items-center gap-1">
                        <i class="fas fa-check text-xs"></i>
                        Approve
                      </button>
                    </form>
                    <form method="post" class="inline">
                      <input type="hidden" name="id" value="<?= $o['id_order'] ?>">
                      <button name="tolak" 
                              class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 flex items-center gap-1">
                        <i class="fas fa-times text-xs"></i>
                        Tolak
                      </button>
                    </form>
                  <?php } ?>

                  <?php if ($o['status']=='dikirim' && empty($o['no_resi'])) { ?>
                    <form method="post" class="flex items-center gap-2">
                      <input type="hidden" name="id" value="<?= $o['id_order'] ?>">
                      <input type="text" name="no_resi" placeholder="No Resi" 
                             class="border border-gray-300 px-3 py-1.5 rounded-lg text-sm w-32 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                      <button name="resi" 
                              class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 flex items-center gap-1">
                        <i class="fas fa-save text-xs"></i>
                        Simpan
                      </button>
                    </form>
                  <?php } ?>

                  <?php
                  if ($o['status']=='refund' && strtotime($o['refund_at']) <= time()-60) {
                  ?>
                    <a href="?delete=<?= $o['id_order'] ?>" 
                       onclick="return confirm('Yakin hapus pesanan ini?')"
                       class="px-3 py-1.5 bg-gray-700 text-white rounded-lg text-sm hover:bg-gray-800 flex items-center gap-1">
                      <i class="fas fa-trash-alt text-xs"></i>
                      Delete
                    </a>
                  <?php } ?>
                </div>
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