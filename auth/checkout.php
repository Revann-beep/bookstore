<?php
session_start();
require '../auth/connection.php';

$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
    header("Location: ../auth/login.php");
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
$total_modal     = 0;
$items           = [];
$pembayaran      = []; // GROUP PER PENJUAL

while ($c = mysqli_fetch_assoc($cartQ)) {

    if ($c['qty'] > $c['stok']) {
        die("Stok {$c['nama_buku']} tidak cukup");
    }

    $subtotal_jual  = $c['qty'] * $c['harga'];
    $subtotal_modal = $c['qty'] * $c['modal'];

    $total_penjualan += $subtotal_jual;
    $total_modal     += $subtotal_modal;

    /* SIMPAN ITEM */
    $items[] = [
        'id_produk'      => $c['id_produk'],
        'nama_buku'      => $c['nama_buku'],
        'qty'            => $c['qty'],
        'harga'          => $c['harga'],
        'modal'          => $c['modal'],
        'subtotal_jual'  => $subtotal_jual,
        'subtotal_modal' => $subtotal_modal
    ];

    /* GROUP PEMBAYARAN PER TOKO */
    $id_penjual = $c['id_penjual'];

    if (!isset($pembayaran[$id_penjual])) {
        $pembayaran[$id_penjual] = [
            'nama_toko' => $c['nama_toko'],
            'nama_bank' => $c['nama_bank'],
            'norek'     => $c['norek'],
            'qris'      => $c['qris'],
            'total'     => 0
        ];
    }

    $pembayaran[$id_penjual]['total'] += $subtotal_jual;
}

/* =====================
   PROSES CHECKOUT
===================== */
if (isset($_POST['checkout'])) {

    $metode = $_POST['metode'] ?? '';
    if (!in_array($metode, ['transfer', 'qris'])) {
        die('Metode pembayaran tidak valid');
    }

    $kode_pesanan = 'ORD' . date('YmdHis');

    mysqli_query($conn, "
        INSERT INTO orders
        (kode_pesanan, id_pembeli, total_harga, status, metode_pembayaran, bukti_tf, created_at)
        VALUES
        ('$kode_pesanan', '$id_user', '$total_penjualan', 'pending', '$metode', '{}', NOW())
    ") or die(mysqli_error($conn));

    $id_order = mysqli_insert_id($conn);

    foreach ($items as $i) {

        mysqli_query($conn, "
            INSERT INTO order_details
            (id_order, id_produk, nama_buku, qty, harga, modal, subtotal_penjualan, subtotal_modal)
            VALUES (
                '$id_order',
                '{$i['id_produk']}',
                '{$i['nama_buku']}',
                '{$i['qty']}',
                '{$i['harga']}',
                '{$i['modal']}',
                '{$i['subtotal_jual']}',
                '{$i['subtotal_modal']}'
            )
        ");

        mysqli_query($conn, "
            UPDATE produk
            SET stok = stok - {$i['qty']}
            WHERE id_produk = {$i['id_produk']}
        ");
    }

    mysqli_query($conn, "
        DELETE FROM keranjang WHERE id_user = '$id_user'
    ");

    header("Location: ../pembeli/invoice.php?id_order=$id_order");
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




<div class="flex gap-3">
  <a href="../pembeli/halaman-keranjang.php"
     class="w-1/2 text-center bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 rounded-xl">
     ⬅ Kembali
  </a>

  <button type="submit" name="checkout"
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
 

  if(val==='transfer'){
    document.getElementById('transferBox').classList.remove('hidden');
    document.getElementById('uploadBox').classList.remove('hidden');
  }

  if(val==='qris'){
    document.getElementById('qrisBox').classList.remove('hidden');
  }
}
</script>

</body>
</html>
