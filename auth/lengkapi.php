<?php
session_start();
require 'connection.php';

// Cek login & role
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../index.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// Ambil data penjual
$query = mysqli_query($conn, "SELECT nama_bank, norek, qris FROM users WHERE id_user='$id_user'");
$user  = mysqli_fetch_assoc($query);

// Kalau SUDAH LENGKAP → langsung ke dashboard
if (!empty($user['nama_bank']) && !empty($user['norek']) && !empty($user['qris'])) {
    header("Location: ../penjual/dashboard.php");
    exit;
}

$error = "";

// Proses simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama_bank = trim($_POST['nama_bank']);
    $norek     = trim($_POST['norek']);

    if (empty($nama_bank) || empty($norek)) {
        $error = "Semua field wajib diisi";
    }

    // Upload QRIS
    if (!$error && isset($_FILES['qris']) && $_FILES['qris']['error'] === 0) {

        $ext = strtolower(pathinfo($_FILES['qris']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','jfif'];

        if (!in_array($ext, $allowed)) {
            $error = "Format QRIS tidak valid";
        } else {
            $folder = "../img/qris/";
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $qris_name = 'qris_' . uniqid() . '.' . $ext;

            if (!move_uploaded_file($_FILES['qris']['tmp_name'], $folder . $qris_name)) {
                $error = "Upload QRIS gagal";
            }
        }
    } else {
        $error = "QRIS wajib diupload";
    }

    // Simpan ke DB
    if (!$error) {
        mysqli_query($conn, "
            UPDATE users SET
            nama_bank='$nama_bank',
            norek='$norek',
            qris='$qris_name'
            WHERE id_user='$id_user'
        ");

        header("Location: ../penjual/dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Lengkapi Data Toko</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-900 to-indigo-600 flex items-center justify-center px-4">

  <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
    
    <h2 class="text-2xl font-bold text-indigo-700 mb-2 text-center">
      Lengkapi Data Toko
    </h2>
    <p class="text-sm text-gray-500 text-center mb-6">
      Lengkapi data pembayaran untuk mulai berjualan
    </p>

    <?php if ($error): ?>
      <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Nama Bank
        </label>
        <input type="text" name="nama_bank"
               class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
               placeholder="Contoh: BCA / BRI / Mandiri"
               required>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Nomor Rekening
        </label>
        <input type="text" name="norek"
               class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
               placeholder="Nomor rekening aktif"
               required>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          QRIS (Upload Gambar)
        </label>
        <input type="file" name="qris" accept="image/*"
               class="w-full text-sm file:mr-4 file:py-2 file:px-4
                      file:rounded-full file:border-0
                      file:text-sm file:font-semibold
                      file:bg-indigo-50 file:text-indigo-700
                      hover:file:bg-indigo-100"
               required>
      </div>

      <button type="submit"
              class="w-full py-3 rounded-xl bg-indigo-600 text-white font-semibold
                     hover:bg-indigo-700 transition">
        Simpan & Masuk Dashboard
      </button>

    </form>

  </div>

</body>
</html>
