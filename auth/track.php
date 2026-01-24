<?php
$resi = $_GET['resi'] ?? 'UNKNOWN';
$kurir = $_GET['kurir'] ?? 'JNE Express';
$tgl_pengiriman = date('d M Y', strtotime('-2 days'));

/* STATUS DUMMY */
$status = [
  ['title' => 'Pesanan diterima penjual', 'desc' => 'Penjual telah menerima pesanan Anda'],
  ['title' => 'Diproses di gudang', 'desc' => 'Pesanan sedang dipacking dan dicek'],
  ['title' => 'Dalam perjalanan', 'desc' => 'Paket menuju kota tujuan'],
  ['title' => 'Kurir menuju alamat', 'desc' => 'Kurir sedang dalam perjalanan ke alamat Anda'],
  ['title' => 'Pesanan sampai', 'desc' => 'Paket telah diterima oleh penerima']
];

$current_step = 3; // step yang sedang aktif (0-4)
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lacak Pesanan #<?= htmlspecialchars($resi) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-blue-50 to-gray-100 min-h-screen flex items-center justify-center p-4">
  <div class="w-full max-w-lg">
    <!-- Header -->
    <div class="text-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">
        <i class="fas fa-box-open text-blue-600 mr-2"></i>Lacak Pengiriman
      </h1>
      <p class="text-gray-500 mt-1">Pantau perjalanan paket Anda</p>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
      <!-- Resi Info -->
      <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-5">
        <div class="flex justify-between items-center">
          <div>
            <p class="text-sm opacity-90">No. Resi</p>
            <p class="text-xl font-bold tracking-wider"><?= htmlspecialchars($resi) ?></p>
          </div>
          <div class="text-right">
            <p class="text-sm opacity-90">Kurir</p>
            <p class="text-lg font-semibold"><?= htmlspecialchars($kurir) ?></p>
          </div>
        </div>
        <div class="mt-4 pt-4 border-t border-blue-500/50">
          <p class="text-sm"><i class="far fa-calendar-alt mr-2"></i>Tanggal Pengiriman: <?= $tgl_pengiriman ?></p>
        </div>
      </div>

      <!-- Progress Bar -->
      <div class="px-5 pt-6">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-blue-600">Proses Pengiriman</span>
          <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full"><?= $current_step+1 ?>/<?= count($status) ?> langkah</span>
        </div>
        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
          <div class="h-full bg-blue-600 rounded-full" style="width: <?= (($current_step+1)/count($status))*100 ?>%"></div>
        </div>
      </div>

      <!-- Timeline -->
      <div class="p-5">
        <div class="space-y-4">
          <?php foreach ($status as $i => $s) { 
            $is_active = $i <= $current_step;
            $is_last = $i === count($status)-1;
          ?>
            <div class="flex items-start">
              <!-- Timeline dot & line -->
              <div class="flex flex-col items-center mr-4">
                <div class="w-10 h-10 rounded-full flex items-center justify-center 
                  <?= $is_active ? 'bg-blue-100 border-2 border-blue-500' : 'bg-gray-100 border-2 border-gray-300' ?>">
                  <?php if ($is_active && !$is_last) { ?>
                    <i class="fas fa-check text-blue-600 text-sm"></i>
                  <?php } elseif ($is_active && $is_last) { ?>
                    <i class="fas fa-home text-green-600 text-sm"></i>
                  <?php } else { ?>
                    <span class="text-gray-400 text-sm"><?= $i+1 ?></span>
                  <?php } ?>
                </div>
                <?php if (!$is_last): ?>
                  <div class="w-1 h-8 flex-grow <?= $i < $current_step ? 'bg-blue-500' : 'bg-gray-300' ?>"></div>
                <?php endif; ?>
              </div>
              
              <!-- Content -->
              <div class="pb-4 <?= $is_last ? '' : 'border-b border-gray-100' ?>">
                <p class="font-medium <?= $is_active ? 'text-gray-800' : 'text-gray-400' ?>">
                  <?= $s['title'] ?>
                </p>
                <p class="text-sm text-gray-500 mt-1"><?= $s['desc'] ?></p>
                <p class="text-xs text-gray-400 mt-2">
                  <i class="far fa-clock mr-1"></i><?= date('d M Y, H:i', strtotime("-".(4-$i)." hours")) ?>
                </p>
              </div>
            </div>
          <?php } ?>
        </div>
      </div>

      <!-- Footer Buttons -->
      <div class="bg-gray-50 p-5 border-t border-gray-200">
        <div class="flex flex-col sm:flex-row gap-3">
          <a href="../penjual/approve.php"
             class="flex-1 px-4 py-3 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl font-medium transition duration-200 text-center">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
          </a>
          <button class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition duration-200">
            <i class="fas fa-share-alt mr-2"></i>Bagikan
          </button>
        </div>
        
        <div class="mt-4 text-center text-sm text-gray-500">
          <p><i class="fas fa-headset mr-2"></i>Butuh bantuan? <a href="#" class="text-blue-600 font-medium">Hubungi Kami</a></p>
        </div>
      </div>
    </div>

    <!-- Estimated Delivery -->
    <div class="bg-white rounded-xl shadow p-4 mt-4 flex items-center justify-between">
      <div class="flex items-center">
        <div class="bg-green-100 p-3 rounded-lg mr-4">
          <i class="fas fa-truck text-green-600 text-lg"></i>
        </div>
        <div>
          <p class="text-sm text-gray-500">Estimasi Sampai</p>
          <p class="font-bold text-gray-800"><?= date('d M Y', strtotime('+1 day')) ?></p>
        </div>
      </div>
      <div class="text-right">
        <p class="text-sm text-gray-500">Status</p>
        <p class="font-bold text-green-600">Dalam Pengiriman</p>
      </div>
    </div>
  </div>
</body>
</html>