<?php
session_start();
require '../auth/connection.php';

$edit = false;
$id_kategori = '';
$nama_kategori = '';
$icon_lama = '';

// ================= TAMBAH / UPDATE =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    $icon = '';

    // UPLOAD icon
    if (!empty($_FILES['icon']['name'])) {
        $ext = pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION);
        $icon = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['icon']['tmp_name'], "../img/kategori/" . $icon);
    }

    // TAMBAH
    if ($_POST['action'] === 'tambah') {
        mysqli_query($conn, "INSERT INTO kategori (nama_kategori, icon)
                             VALUES ('$nama', '$icon')");
    }

    // UPDATE
    if ($_POST['action'] === 'update') {
        $id = $_POST['id_kategori'];

        if ($icon) {
            // hapus icon lama
            $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT icon FROM kategori WHERE id_kategori='$id'"));
            if ($old['icon'] && file_exists("../img/kategori/" . $old['icon'])) {
                unlink("../img/kategori/" . $old['icon']);
            }

            mysqli_query($conn, "UPDATE kategori 
                                 SET nama_kategori='$nama', icon='$icon'
                                 WHERE id_kategori='$id'");
        } else {
            mysqli_query($conn, "UPDATE kategori 
                                 SET nama_kategori='$nama'
                                 WHERE id_kategori='$id'");
        }
    }

    header("Location: kategori.php");
    exit;
}

// ================= HAPUS =================
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT icon FROM kategori WHERE id_kategori='$id'"));
    if ($data['icon'] && file_exists("../img/kategori/" . $data['icon'])) {
        unlink("../img/kategori/" . $data['icon']);
    }

    mysqli_query($conn, "DELETE FROM kategori WHERE id_kategori='$id'");
    header("Location: kategori.php");
    exit;
}

// ================= EDIT =================
if (isset($_GET['edit'])) {
    $edit = true;
    $id = $_GET['edit'];
    $data = mysqli_query($conn, "SELECT * FROM kategori WHERE id_kategori='$id'");
    $row = mysqli_fetch_assoc($data);

    $id_kategori = $row['id_kategori'];
    $nama_kategori = $row['nama_kategori'];
    $icon_lama = $row['icon'];
}

// ================= AMBIL DATA =================
$kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id_kategori DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kategori - Super Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white shadow-lg flex flex-col">
        <div class="p-6 flex items-center gap-2">
            <div class="w-10 h-10 rounded-full bg-teal-500 text-white flex items-center justify-center font-bold">S</div>
            <span class="font-bold text-teal-600">SARI ANGREK</span>
        </div>

        <nav class="flex-1 px-4 space-y-2">
            <a href="dashboard.php" class="flex px-4 py-2 rounded-lg text-gray-600 hover:bg-teal-100">Dashboard</a>
            <a href="penjual.php" class="flex px-4 py-2 rounded-lg text-gray-600 hover:bg-teal-100">Penjual</a>
            <a href="pembeli.php" class="flex px-4 py-2 rounded-lg text-gray-600 hover:bg-teal-100">Pembeli</a>
            <a href="kategori.php" class="flex px-4 py-2 rounded-lg bg-teal-500 text-white">Kategori</a>
        </nav>

        <div class="px-4 pb-4">
            <a href="../auth/logout.php" class="flex px-4 py-2 text-gray-500 hover:text-red-500">Sign Out</a>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="flex-1 p-8">

        <h2 class="text-2xl font-bold mb-6">
            <?= $edit ? 'Edit Kategori' : 'Tambah Kategori' ?>
        </h2>

        <!-- FORM -->
        <form method="POST" enctype="multipart/form-data" class="flex gap-4 mb-8 items-center">
            <input type="hidden" name="action" value="<?= $edit ? 'update' : 'tambah' ?>">
            <input type="hidden" name="id_kategori" value="<?= $id_kategori ?>">

            <input type="text" name="nama_kategori"
                   value="<?= $nama_kategori ?>"
                   placeholder="Nama kategori"
                   class="flex-1 border rounded-lg px-4 py-3"
                   required>

            <input type="file" name="icon" accept="image/*"
                   class="border rounded-lg px-4 py-3">

            <?php if ($edit && $icon_lama): ?>
                <img src="../img/kategori/<?= $icon_lama ?>" class="w-12 h-12 rounded">
            <?php endif; ?>

            <button class="bg-teal-500 hover:bg-teal-600 text-white px-6 py-3 rounded-lg">
                <?= $edit ? 'Update' : 'Tambah' ?>
            </button>

            <?php if ($edit): ?>
                <a href="kategori.php" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-3 rounded-lg">
                    Batal
                </a>
            <?php endif; ?>
        </form>

        <!-- TABLE -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full border text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-3">No</th>
                        <th class="border px-4 py-3">icon</th>
                        <th class="border px-4 py-3 text-left">Nama Kategori</th>
                        <th class="border px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no=1; while ($row = mysqli_fetch_assoc($kategori)) : ?>
                    <tr class="hover:bg-gray-50">
                        <td class="border px-4 py-3 text-center"><?= $no++ ?></td>
                        <td class="border px-4 py-3 text-center">
                            <?php if ($row['icon']): ?>
                                <img src="../img/kategori/<?= $row['icon'] ?>" class="w-10 h-10 mx-auto rounded">
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="border px-4 py-3"><?= $row['nama_kategori'] ?></td>
                        <td class="border px-4 py-3 text-center space-x-2">
                            <a href="?edit=<?= $row['id_kategori'] ?>" class="bg-yellow-400 text-white px-3 py-2 rounded">Edit</a>
                            <a href="?hapus=<?= $row['id_kategori'] ?>"
                               onclick="return confirm('Yakin hapus kategori?')"
                               class="bg-red-500 text-white px-3 py-2 rounded">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

</body>
</html>
