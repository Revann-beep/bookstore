<?php
session_start();
require '../auth/connection.php';

$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
    header("Location: ../index.php");
    exit;
}

/* =====================
   AMBIL KERANJANG + PENJUAL
===================== */
$cartQ = mysqli_query($conn, "
    SELECT 
        k.id_produk,
        k.qty,
        p.nama_buku,
        p.harga,
        p.modal,
        p.stok,
        p.id_penjual,
        u.nama       AS nama_toko,
        u.norek,
        u.nama_bank,
        u.qris
    FROM keranjang k
    JOIN produk p ON p.id_produk = k.id_produk
    JOIN users u  ON u.id_user = p.id_penjual
    WHERE k.id_user = '$id_user'
");

if (mysqli_num_rows($cartQ) == 0) {
    header("Location: halaman-keranjang.php");
    exit;
}

$total_penjualan = 0;
$pembayaran = [];          // UNTUK TAMPILAN
$itemsPerPenjual = [];    // UNTUK CHECKOUT

while ($c = mysqli_fetch_assoc($cartQ)) {

    if ($c['qty'] > $c['stok']) {
        die("Stok {$c['nama_buku']} tidak cukup");
    }

    $subtotal_penjualan  = $c['qty'] * $c['harga'];
    $subtotal_modal = $c['qty'] * $c['modal'];

    $total_penjualan += $subtotal_penjualan;

    $id_penjual = $c['id_penjual'];

    /* =====================
       GROUP ITEM PER PENJUAL
    ===================== */
    $itemsPerPenjual[$id_penjual][] = [
        'id_produk'      => $c['id_produk'],
        'nama_buku'      => $c['nama_buku'],
        'qty'            => $c['qty'],
        'harga'          => $c['harga'],
        'modal'          => $c['modal'],
        'subtotal_penjualan'  => $subtotal_penjualan,
        'subtotal_modal' => $subtotal_modal
    ];

    /* =====================
       GROUP PEMBAYARAN (UI)
    ===================== */
    if (!isset($pembayaran[$id_penjual])) {
        $pembayaran[$id_penjual] = [
            'nama_toko' => $c['nama_toko'],
            'nama_bank' => $c['nama_bank'],
            'norek'     => $c['norek'],
            'qris'      => $c['qris'],
            'total'     => 0
        ];
    }

    $pembayaran[$id_penjual]['total'] += $subtotal_penjualan;
}

if (isset($_POST['checkout'])) {

      $metode = strtolower(trim($_POST['metode'] ?? ''));
      $uang_bayar = $_POST['uang_bayar'] ?? 0;
      $kembalian = 0;

      /* =====================
        STATUS OTOMATIS
      ===================== */

    if(in_array($metode, ['cod','cash'])){
    $status_order = 'menunggu_verifikasi';
    }else{
        $status_order = 'pending';
    }

      if (!in_array($metode, ['transfer','qris','cod','cash'])) {
          die('Metode pembayaran tidak valid');
      }

      /* =====================
        LOGIKA PEMBAYARAN
      ===================== */

      if($metode == 'cash'){

          if($uang_bayar < $total_penjualan){
              die("Uang bayar tidak cukup");
          }

          $kembalian = $uang_bayar - $total_penjualan;

      }else{

          $uang_bayar = 0;
          $kembalian = 0;

      }

    foreach ($itemsPerPenjual as $id_penjual => $items) {

        $total_penjual = 0;
        $total_modal   = 0;

        foreach ($items as $i) {
            $total_penjual += $i['subtotal_penjualan'];
            $total_modal   += $i['subtotal_modal'];
        }

        $kode_pesanan = 'ORD' . date('YmdHis') . rand(100,999);

        // ======================
        // INSERT ORDER (1 PENJUAL)
        // ======================
        mysqli_query($conn, "
            INSERT INTO orders
(kode_pesanan, id_pembeli, total_harga, total_modal, status, metode_pembayaran, uang_bayar, kembalian, bukti_tf, created_at)
VALUES
('$kode_pesanan', '$id_user', '$total_penjual', '$total_modal', '$status_order', '$metode', '$uang_bayar', '$kembalian', '', NOW())
        ") or die(mysqli_error($conn));

        $id_order = mysqli_insert_id($conn);

        // ======================
        // INSERT DETAIL + UPDATE STOK
        // ======================
        foreach ($items as $i) {

            mysqli_query($conn, "
                INSERT INTO order_details
                (id_order, id_produk, id_penjual, nama_buku, qty, harga, modal, subtotal_penjualan, subtotal_modal, status_detail)
                VALUES (
                    '$id_order',
                    '{$i['id_produk']}',
                    '$id_penjual',
                    '{$i['nama_buku']}',
                    '{$i['qty']}',
                    '{$i['harga']}',
                    '{$i['modal']}',
                    '{$i['subtotal_penjualan']}',
                    '{$i['subtotal_modal']}',
                    'pending'
                )
            ");

            mysqli_query($conn, "
                UPDATE produk
                SET stok = stok - {$i['qty']}
                WHERE id_produk = {$i['id_produk']}
            ");
        }
    }

    // ======================
    // HAPUS KERANJANG
    // ======================
    mysqli_query($conn, "DELETE FROM keranjang WHERE id_user = '$id_user'");

    header("Location: ../pembeli/invoice.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Checkout</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100">

<div class="max-w-4xl mx-auto p-6">

<h1 class="text-2xl font-bold mb-6">Checkout</h1>

<!-- RINGKASAN -->
<div class="bg-white rounded-xl shadow p-6 mb-6">
  <h2 class="font-semibold mb-3">Ringkasan Pesanan</h2>
  <p class="text-lg font-bold text-amber-600">
    Total: Rp <?= number_format($total_penjualan,0,',','.') ?>
  </p>
</div>

<form method="post">

<!-- METODE PEMBAYARAN -->
<div class="bg-white rounded-xl shadow p-6 mb-6">
  <h2 class="font-semibold mb-4">Metode Pembayaran</h2>

  <select name="metode" required onchange="toggleMetode(this.value)"
    class="w-full border rounded-xl px-4 py-3">
    <option value="">-- Pilih Metode --</option>
    <option value="transfer">Transfer Bank</option>
    <option value="qris">QRIS</option>
    <option value="cod">Cash on Delivery (COD)</option>
    <option value="cash">Bayar Tunai (di tempat)</option>
  </select>
</div>

<!-- TRANSFER -->
<div id="transferBox" class="hidden bg-white rounded-xl shadow p-6 mb-6 space-y-4">
  <?php foreach ($pembayaran as $p): ?>
  <div class="border rounded-xl p-4">
    <p class="font-semibold"><?= $p['nama_toko'] ?></p>
    <p class="text-sm text-gray-600">
      <?= $p['nama_bank'] ?><br>
      <b><?= $p['norek'] ?></b>
    </p>
    <p class="font-bold text-amber-600 mt-2">
      Rp <?= number_format($p['total'],0,',','.') ?>
    </p>
  </div>
  <?php endforeach; ?>
</div>

<!-- QRIS -->
<div id="qrisBox" class="hidden bg-white rounded-xl shadow p-6 mb-6 space-y-4">
<?php foreach ($pembayaran as $p): ?>
  <div class="border rounded-xl p-4 text-center">
    <p class="font-semibold"><?= $p['nama_toko'] ?></p>
    <img src="../img/qris/<?= $p['qris'] ?>" class="w-40 mx-auto my-3">
    <p class="font-bold text-amber-600">
      Rp <?= number_format($p['total'],0,',','.') ?>
    </p>
  </div>
<?php endforeach; ?>
</div>

<!-- CASH -->
<!-- CASH -->

<div id="cashBox" class="hidden bg-white rounded-xl shadow p-6 mb-6">

<p class="font-semibold mb-2">Pembayaran Cash</p>

<input
type="number"
name="uang_bayar"
id="uang_bayar"
placeholder="Masukkan uang bayar"
class="w-full border rounded-xl px-4 py-3"
oninput="hitungKembalian()"

>

<div class="mt-3">
<label class="text-sm text-gray-600">Kembalian</label>
<input 
type="text"
id="kembalian"
readonly
class="w-full border rounded-xl px-4 py-3 bg-gray-100"
placeholder="0"
>
</div>

<p class="text-sm text-gray-500 mt-2">
Pembayaran dilakukan langsung saat transaksi
</p>

</div>


<!-- COD -->
<div id="codBox" class="hidden bg-white rounded-xl shadow p-6 mb-6">

<p class="font-semibold">COD (Cash On Delivery)</p>

<p class="text-gray-600 text-sm">
Pembayaran dilakukan kepada kurir saat pesanan sampai.
</p>

</div>


<div class="flex gap-3">
  <a href="../pembeli/halaman-keranjang.php"
     class="w-1/2 text-center bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 rounded-xl">
     ⬅ Kembali
  </a>

  <button type="submit" name="checkout"
onclick="return cekPembayaran()"
class="w-1/2 bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 rounded-xl">
Konfirmasi Pesanan
</button>
</div>

</form>
</div>

<script>
function toggleMetode(val){

document.getElementById('transferBox').classList.add('hidden');
document.getElementById('qrisBox').classList.add('hidden');
document.getElementById('cashBox').classList.add('hidden');
document.getElementById('codBox').classList.add('hidden');

if(val==='transfer'){
document.getElementById('transferBox').classList.remove('hidden');
}

if(val==='qris'){
document.getElementById('qrisBox').classList.remove('hidden');
}

if(val==='cash'){
document.getElementById('cashBox').classList.remove('hidden');
}

if(val==='cod'){
document.getElementById('codBox').classList.remove('hidden');
}

}

function hitungKembalian(){

let uang = document.getElementById('uang_bayar').value;
let kembali = uang - totalBelanja;

if(uang === ''){
document.getElementById('kembalian').value = '';
return;
}

if(kembali < 0){
document.getElementById('kembalian').value = "Uang kurang";
}else{
document.getElementById('kembalian').value = "Rp " + kembali.toLocaleString('id-ID');
}

}

function cekPembayaran(){

let metode = document.querySelector('[name="metode"]').value;

if(metode === 'cash'){

let uang = document.getElementById('uang_bayar').value;

if(uang < totalBelanja){
alert("Uang bayar tidak cukup!");
return false;
}

}

return true;

}

</script>

<script>
let totalBelanja = <?= $total_penjualan ?>;
</script>

</body>
</html>
