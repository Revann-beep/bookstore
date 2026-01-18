<?php
session_start();
require '../auth/connection.php';

$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
    header("Location: ../auth/login.php");
    exit;
}

/* =====================
   AMBIL KERANJANG
===================== */
$cartQ = mysqli_query($conn, "
    SELECT 
        k.id_produk,
        k.qty,
        p.nama_buku,
        p.harga,
        p.modal,
        p.stok
    FROM keranjang k
    JOIN produk p ON p.id_produk = k.id_produk
    WHERE k.id_user = '$id_user'
");

if (mysqli_num_rows($cartQ) == 0) {
    header("Location: halaman-keranjang.php");
    exit;
}

$total_penjualan = 0;
$total_modal     = 0;
$items = [];

while ($c = mysqli_fetch_assoc($cartQ)) {

    if ($c['qty'] > $c['stok']) {
        die("Stok {$c['nama_buku']} tidak cukup");
    }

    $subtotal_jual  = $c['qty'] * $c['harga'];
    $subtotal_modal = $c['qty'] * $c['modal'];

    $total_penjualan += $subtotal_jual;
    $total_modal     += $subtotal_modal;

    $items[] = [
        'id_produk'        => $c['id_produk'],
        'nama_buku'        => $c['nama_buku'],
        'qty'              => $c['qty'],
        'harga'            => $c['harga'],
        'modal'            => $c['modal'],
        'subtotal_jual'    => $subtotal_jual,
        'subtotal_modal'   => $subtotal_modal
    ];
}

/* =====================
   INSERT ORDERS
===================== */
$kode_pesanan = 'ORD' . date('YmdHis');

mysqli_query($conn, "
    INSERT INTO orders
    (kode_pesanan, id_pembeli, total_harga, status, created_at)
    VALUES
    ('$kode_pesanan', '$id_user', '$total_penjualan', 'pending', NOW())
") or die(mysqli_error($conn));

$id_order = mysqli_insert_id($conn);

/* =====================
   INSERT ORDER_DETAILS
===================== */
foreach ($items as $i) {

    mysqli_query($conn, "
        INSERT INTO order_details
        (id_order, id_produk, nama_buku, qty, harga, modal, subtotal_penjualan, subtotal_modal)
        VALUES
        (
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

/* =====================
   HAPUS KERANJANG
===================== */
mysqli_query($conn, "
    DELETE FROM keranjang WHERE id_user = '$id_user'
");

/* =====================
   REDIRECT KE INVOICE
===================== */
header("Location: ../pembeli/invoice.php?id_order=$id_order");
exit;
