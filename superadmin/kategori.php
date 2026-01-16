<?php
session_start();
require '../auth/connection.php';

$edit = false;
$id_kategori = '';
$nama_kategori = '';

// TAMBAH / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);

    if ($_POST['action'] === 'tambah') {
        mysqli_query($conn, "INSERT INTO kategori (nama_kategori) VALUES ('$nama')");
    }

    if ($_POST['action'] === 'update') {
        $id = $_POST['id_kategori'];
        mysqli_query($conn, "UPDATE kategori SET nama_kategori='$nama' WHERE id_kategori='$id'");
    }

    header("Location: kategori.php");
    exit;
}

// HAPUS
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM kategori WHERE id_kategori='$id'");
    header("Location: kategori.php");
    exit;
}

// EDIT
if (isset($_GET['edit'])) {
    $edit = true;
    $id = $_GET['edit'];
    $data = mysqli_query($conn, "SELECT * FROM kategori WHERE id_kategori='$id'");
    $row = mysqli_fetch_assoc($data);
    $id_kategori = $row['id_kategori'];
    $nama_kategori = $row['nama_kategori'];
}

// AMBIL DATA
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
            <div class="w-10 h-10 rounded-full bg-teal-500 text-white flex items-center justify-center font-bold">
                S
            </div>
            <span class="font-bold text-teal-600">SARI ANGREK</span>
        </div>

        <nav class="flex-1 px-4 space-y-2">
            <a href="dashboard.php"
               class="flex items-center px-4 py-2 rounded-lg text-gray-600 hover:bg-teal-100">
                Dashboard
            </a>
            <a href="penjual.php"
               class="flex items-center px-4 py-2 rounded-lg text-gray-600 hover:bg-teal-100">
                penjual
            </a>
            <a href="pembeli.php"
               class="flex items-center px-4 py-2 rounded-lg text-gray-600 hover:bg-teal-100">
                Pembeli
            </a>
            <a href="kategori.php"
               class="flex items-center px-4 py-2 rounded-lg bg-teal-500 text-white">
                Kategori
            </a>
        </nav>

        <div class="px-4 pb-4 space-y-2">
            <a href="../auth/logout.php"
               class="flex items-center px-4 py-2 text-gray-500 hover:text-red-500">
                Sign Out
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-8">

        <h2 class="text-2xl font-bold mb-6 text-gray-800">
            <?= $edit ? 'Edit Kategori' : 'Tambah Kategori' ?>
        </h2>

        <!-- FORM -->
        <form method="POST" class="flex gap-4 mb-8">
            <input type="hidden" name="action" value="<?= $edit ? 'update' : 'tambah' ?>">
            <input type="hidden" name="id_kategori" value="<?= $id_kategori ?>">

            <input type="text" name="nama_kategori"
                placeholder="Nama kategori"
                value="<?= $nama_kategori ?>"
                class="flex-1 border rounded-lg px-4 py-3 focus:ring-2 focus:ring-teal-500"
                required>

            <button class="bg-teal-500 hover:bg-teal-600 text-white px-6 rounded-lg font-semibold">
                <?= $edit ? 'Update' : 'Tambah' ?>
            </button>

            <?php if ($edit): ?>
                <a href="kategori.php"
                   class="bg-gray-400 hover:bg-gray-500 text-white px-6 rounded-lg flex items-center">
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
                        <th class="border px-4 py-3 text-left">Nama Kategori</th>
                        <th class="border px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no = 1; while ($row = mysqli_fetch_assoc($kategori)) : ?>
                    <tr class="hover:bg-gray-50">
                        <td class="border px-4 py-3 text-center"><?= $no++ ?></td>
                        <td class="border px-4 py-3"><?= $row['nama_kategori'] ?></td>
                        <td class="border px-4 py-3 text-center space-x-2">
                            <a href="?edit=<?= $row['id_kategori'] ?>"
                               class="bg-yellow-400 hover:bg-yellow-500 text-white px-4 py-2 rounded">
                                Edit
                            </a>
                            <a href="?hapus=<?= $row['id_kategori'] ?>"
                               onclick="return confirm('Yakin hapus kategori?')"
                               class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                                Hapus
                            </a>
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
