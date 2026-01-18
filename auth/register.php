<?php
require 'connection.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nik      = $_POST['nik'];
    $nama     = $_POST['nama'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $alamat   = $_POST['alamat'];
    $role     = $_POST['role'];

    // Upload image
    $image_name = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $ext = strtolower($ext);

        $allowed = ['jpg','jpeg','png','webp','jfif'];

        if (!in_array($ext, $allowed)) {
            $error = "Format gambar tidak diizinkan";
        } else {

            // Pastikan folder ada
            $folder = "../img/profile";
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $image_name = uniqid() . '.' . $ext;

            // Upload file ke folder
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $folder . $image_name)) {
                $error = "Upload gambar gagal (cek permission folder)";
            }
        }
    }

    // Validasi NIK
    if (!preg_match('/^[0-9]{16}$/', $nik)) {
        $error = "NIK harus 16 digit angka";
    }
    // Validasi role
    elseif (!in_array($role, ['pembeli','penjual'])) {
        $error = "Role tidak valid";
    }
    // Validasi password
    elseif ($password !== $confirm) {
        $error = "Password tidak sama";
    }
    else {
        // Cek NIK / Email
        $cek = mysqli_query($conn, "
            SELECT nik FROM users 
            WHERE nik='$nik' OR email='$email'
        ");

        if (mysqli_num_rows($cek) > 0) {
            $error = "NIK atau Email sudah terdaftar";
        } else {
            // Hash password
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert ke DB
            $insert = mysqli_query($conn, "
                INSERT INTO users 
                (nik, nama, email, password, alamat, role, status, image)
                VALUES
                ('$nik','$nama','$email','$hash','$alamat','$role','offline','$image_name')
            ");

            if ($insert) {
                header("Location: ../index.php");
                exit;
            } else {
                $error = "Registrasi gagal";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Sign Up</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-green-200 via-teal-300 to-green-500 flex items-center justify-center">

  <div class="w-full max-w-5xl bg-white/20 backdrop-blur-lg rounded-2xl shadow-xl grid grid-cols-1 md:grid-cols-2 overflow-hidden">

    <!-- LEFT -->
    <div class="hidden md:flex flex-col justify-center items-center p-10">
      <h1 class="text-2xl font-bold text-green-700 mb-2">🌿 Sari Anggrek</h1>
      <p class="text-sm text-green-800 mb-6">@nurhayatuladia</p>
      <img src="illustration.png" class="w-72">
    </div>

    <!-- RIGHT -->
    <div class="p-10">
      <h2 class="text-3xl font-bold text-white mb-2">Create a New Account</h2>
      <p class="text-white/80 mb-6">Join Larana Inc Today</p>

      <?php if ($error): ?>
        <div class="bg-red-100 text-red-600 p-3 rounded mb-4 text-sm">
          <?= $error ?>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" class="space-y-4">

        <input type="text" name="nik" maxlength="16" placeholder="NIK (16 Digit)"
          class="w-full px-4 py-3 rounded-lg bg-white/80 focus:ring-2 focus:ring-green-500" required>

        <input type="text" name="nama" placeholder="Full Name"
          class="w-full px-4 py-3 rounded-lg bg-white/80 focus:ring-2 focus:ring-green-500" required>

        <input type="email" name="email" placeholder="Email Address"
          class="w-full px-4 py-3 rounded-lg bg-white/80 focus:ring-2 focus:ring-green-500" required>

        <input type="password" name="password" placeholder="Password"
          class="w-full px-4 py-3 rounded-lg bg-white/80 focus:ring-2 focus:ring-green-500" required>

        <input type="password" name="confirm_password" placeholder="Confirm Password"
          class="w-full px-4 py-3 rounded-lg bg-white/80 focus:ring-2 focus:ring-green-500" required>

        <textarea name="alamat" placeholder="Alamat"
          class="w-full px-4 py-3 rounded-lg bg-white/80 focus:ring-2 focus:ring-green-500"></textarea>

        <!-- image -->
        <div class="bg-white/80 p-4 rounded-lg">
          <p class="text-sm font-semibold text-gray-700 mb-2">Upload image</p>
          <input type="file" name="image" accept="image/*">
        </div>

        <!-- ROLE -->
        <div class="bg-white/80 p-4 rounded-lg">
          <p class="text-sm font-semibold text-gray-700 mb-2">Daftar Sebagai</p>
          <div class="flex gap-6">
            <label class="flex items-center gap-2">
              <input type="radio" name="role" value="pembeli" required>
              Pembeli
            </label>
            <label class="flex items-center gap-2">
              <input type="radio" name="role" value="penjual" required>
              Penjual
            </label>
          </div>
        </div>

        <button type="submit"
          class="w-full py-3 rounded-lg bg-green-500 text-white font-semibold hover:bg-green-600 transition">
          Sign Up
        </button>

      </form>

      <p class="text-center text-white/90 mt-6 text-sm">
        Already have an account?
        <a href="../index.php" class="font-semibold underline">Log in</a>
      </p>
    </div>

  </div>

</body>
</html>
