<?php
session_start();
require '../auth/connection.php';

if(!isset($_SESSION['id_user'])){
    header("Location: ../index.php");
    exit;
}

/* =====================
   TAMBAH KATEGORI
=====================*/
if(isset($_POST['tambah'])){
    $nama = mysqli_real_escape_string($conn,$_POST['nama']);

    $cek = mysqli_query($conn,"SELECT * FROM kategori WHERE nama_kategori='$nama'");

    if(mysqli_num_rows($cek)>0){
        echo "<script>alert('Kategori sudah ada');</script>";
    }else{

        $icon="";

        if(!empty($_FILES['icon']['name'])){
            $ext = pathinfo($_FILES['icon']['name'],PATHINFO_EXTENSION);
            $icon = uniqid().".".$ext;

            move_uploaded_file($_FILES['icon']['tmp_name'],"../img/kategori/".$icon);
        }

        mysqli_query($conn,"INSERT INTO kategori (nama_kategori,icon) VALUES ('$nama','$icon')");

        header("Location:kategori.php");
    }
}


/* =====================
   DELETE
=====================*/
if(isset($_GET['hapus'])){

    $id = $_GET['hapus'];

    mysqli_query($conn,"DELETE FROM kategori WHERE id_kategori='$id'");

    header("Location:kategori.php");
}


/* =====================
   UPDATE
=====================*/
if(isset($_POST['update'])){

    $id = $_POST['id'];
    $nama = mysqli_real_escape_string($conn,$_POST['nama']);

    if(!empty($_FILES['icon']['name'])){

        $ext = pathinfo($_FILES['icon']['name'],PATHINFO_EXTENSION);
        $icon = uniqid().".".$ext;

        move_uploaded_file($_FILES['icon']['tmp_name'],"../img/kategori/".$icon);

        mysqli_query($conn,"UPDATE kategori SET nama_kategori='$nama', icon='$icon' WHERE id_kategori='$id'");

    }else{

        mysqli_query($conn,"UPDATE kategori SET nama_kategori='$nama' WHERE id_kategori='$id'");

    }

    header("Location:kategori.php");
}


/* =====================
   EDIT
=====================*/
$edit=false;

if(isset($_GET['edit'])){
    $edit=true;

    $id=$_GET['edit'];

    $data=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM kategori WHERE id_kategori='$id'"));
}


/* =====================
   DATA
=====================*/
$kategori=mysqli_query($conn,"SELECT * FROM kategori ORDER BY id_kategori DESC");

?>

<!DOCTYPE html>
<html>
<head>

<title>Kategori</title>

<script src="https://cdn.tailwindcss.com"></script>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body class="bg-gray-100 flex">


<!-- SIDEBAR -->
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

      <a href="dashboard.php"
      class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
      <i class="fas fa-chart-line w-5"></i> Dashboard
      </a>

      <a href="produk.php"
      class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
      <i class="fas fa-box-open w-5"></i> Produk
      </a>

      <!-- MENU BARU -->
      <a href="kategori.php"
      class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
      <i class="fas fa-tags w-5"></i> Kategori
      </a>

      <a href="approve.php"
      class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
      <i class="fas fa-check-circle w-5"></i> Approve
      </a>

      <a href="laporan.php"
      class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
      <i class="fas fa-file-alt w-5"></i> Laporan
      </a>

      <a href="chat.php"
      class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
      <i class="fas fa-comments w-5"></i> Chat
      </a>

      <a href="admin.php"
      class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
      <i class="fas fa-store w-5"></i> Data Penjual
      </a>

      <a href="akun_saya.php"
      class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
      <i class="fas fa-user-circle w-5"></i> Akun Saya
      </a>

      <a href="help.php"
      class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
      <i class="fas fa-question-circle w-5"></i> Bantuan
      </a>

    </nav>
  </div>

  <!-- LOGOUT -->
  <div class="p-4 border-t mt-auto">
    <a href="../auth/logout.php"
       class="flex items-center gap-3 text-red-500 hover:text-red-600">
      <i class="fas fa-sign-out-alt"></i> Keluar
    </a>
  </div>

</aside>


<!-- CONTENT -->
<div class="ml-64 p-8 w-full">

<h1 class="text-3xl font-bold mb-6 text-gray-800">Kelola Kategori</h1>


<!-- FORM -->
<div class="bg-white p-6 rounded-xl shadow mb-8">

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="id"
value="<?= $edit ? $data['id_kategori'] : '' ?>">

<label class="font-semibold">Nama Kategori</label>

<input type="text"
name="nama"
required
value="<?= $edit ? $data['nama_kategori'] : '' ?>"
class="w-full border rounded-lg p-2 mt-2 mb-4">

<label class="font-semibold">Icon</label>

<input type="file" name="icon" class="mb-4">

<?php if($edit && $data['icon']){ ?>

<img src="../img/kategori/<?= $data['icon'] ?>" width="60" class="mb-4">

<?php } ?>


<button name="<?= $edit ? 'update':'tambah' ?>"
class="bg-indigo-600 text-white px-6 py-2 rounded-lg">

<?= $edit ? 'Update':'Tambah' ?>

</button>

<?php if($edit){ ?>

<a href="kategori.php"
class="ml-3 bg-gray-400 text-white px-6 py-2 rounded-lg">

Batal

</a>

<?php } ?>

</form>

</div>


<!-- TABLE -->
<div class="bg-white rounded-xl shadow overflow-hidden">

<table class="w-full">

<thead class="bg-gray-100">

<tr>

<th class="p-4">No</th>
<th class="p-4">Icon</th>
<th class="p-4">Kategori</th>
<th class="p-4">Aksi</th>

</tr>

</thead>

<tbody>

<?php $no=1; while($k=mysqli_fetch_assoc($kategori)){ ?>

<tr class="border-t">

<td class="p-4"><?= $no++ ?></td>

<td class="p-4">

<?php if($k['icon']){ ?>

<img src="../img/kategori/<?= $k['icon'] ?>" width="40">

<?php } ?>

</td>

<td class="p-4 font-medium"><?= $k['nama_kategori'] ?></td>

<td class="p-4 space-x-2">

<a href="?edit=<?= $k['id_kategori'] ?>"
class="bg-yellow-500 text-white px-3 py-1 rounded">

Edit

</a>

<a href="?hapus=<?= $k['id_kategori'] ?>"
onclick="return confirm('Hapus kategori ini?')"
class="bg-red-500 text-white px-3 py-1 rounded">

Hapus

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</body>
</html>