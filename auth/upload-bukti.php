<?php
session_start();
require 'connection.php';

$id_order = $_GET['id_order'] ?? null;

if (!$id_order) {
    header("Location: ../pembeli/status.php");
    exit;
}

// Ambil order
$orderQ = mysqli_query($conn, "SELECT * FROM orders WHERE id_order='$id_order'");
$order = mysqli_fetch_assoc($orderQ);

// Ambil detail untuk list penjual
$detailQ = mysqli_query($conn, "
    SELECT od.*, p.id_penjual, u.nama AS nama_penjual
    FROM order_details od
    JOIN produk p ON p.id_produk = od.id_produk
    JOIN users u ON u.id_user = p.id_penjual
    WHERE od.id_order = '$id_order'
");


$penjualList = [];
while($d = mysqli_fetch_assoc($detailQ)){
    $penjualList[$d['id_penjual']] = $d['nama_penjual'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Bukti Transfer</title>
</head>
<body>

<h2>Upload Bukti Transfer</h2>

<?php foreach($penjualList as $id_penjual => $nama_penjual): ?>
  <div style="margin-bottom: 20px;">
      <h3>Untuk Penjual: <?= htmlspecialchars($nama_penjual) ?></h3>
      <form action="proses-upload.php" method="post" enctype="multipart/form-data">
          <input type="hidden" name="id_order" value="<?= $id_order ?>">
          <input type="hidden" name="id_penjual" value="<?= $id_penjual ?>">
          <input type="file" name="bukti" required>
          <button type="submit">Upload</button>
      </form>
  </div>
<?php endforeach; ?>

</body>
</html>
