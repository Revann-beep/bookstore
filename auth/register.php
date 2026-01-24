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
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar - Aksara Jiwa</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', system-ui, sans-serif;
    }

    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .container {
      width: 100%;
      max-width: 500px;
      background: white;
      border-radius: 20px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
      overflow: hidden;
    }

    /* Header */
    .header {
      background: linear-gradient(90deg, #1a237e 0%, #3949ab 100%);
      color: white;
      padding: 40px 30px 30px;
      text-align: center;
      position: relative;
    }

    .header::before {
      content: "書";
      position: absolute;
      font-size: 120px;
      opacity: 0.1;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-family: serif;
    }

    .logo {
      font-size: 36px;
      font-weight: 800;
      margin-bottom: 10px;
      letter-spacing: 2px;
      position: relative;
    }

    .tagline {
      font-size: 16px;
      opacity: 0.9;
    }

    /* Form */
    .form-container {
      padding: 40px 35px;
    }

    .form-title {
      font-size: 28px;
      color: #1a237e;
      margin-bottom: 5px;
      font-weight: 700;
    }

    .form-subtitle {
      color: #666;
      margin-bottom: 30px;
      font-size: 15px;
    }

    /* Error */
    .error {
      background: #ffebee;
      color: #c62828;
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 20px;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 10px;
      border-left: 4px solid #c62828;
    }

    .error i {
      font-size: 18px;
    }

    /* Form */
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 20px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group.full {
      grid-column: 1 / -1;
    }

    label {
      display: block;
      margin-bottom: 8px;
      color: #333;
      font-weight: 600;
      font-size: 14px;
    }

    input, textarea {
      width: 100%;
      padding: 15px;
      border: 2px solid #e0e0e0;
      border-radius: 12px;
      font-size: 15px;
      transition: all 0.3s;
      background: #fafafa;
    }

    input:focus, textarea:focus {
      outline: none;
      border-color: #3949ab;
      background: white;
      box-shadow: 0 0 0 3px rgba(57, 73, 171, 0.1);
    }

    textarea {
      min-height: 100px;
      resize: vertical;
    }

    /* File Upload */
    .file-upload {
      border: 2px dashed #bdbdbd;
      border-radius: 12px;
      padding: 30px;
      text-align: center;
      background: #f9f9f9;
      cursor: pointer;
      transition: all 0.3s;
    }

    .file-upload:hover {
      border-color: #3949ab;
      background: #f5f5f5;
    }

    .file-upload input {
      display: none;
    }

    .upload-icon {
      font-size: 40px;
      color: #757575;
      margin-bottom: 10px;
    }

    .upload-text {
      color: #666;
      font-size: 14px;
    }

    /* Role */
    .role-selector {
      display: flex;
      gap: 15px;
      margin: 25px 0;
    }

    .role-option {
      flex: 1;
      border: 2px solid #e0e0e0;
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s;
      background: white;
    }

    .role-option:hover {
      border-color: #3949ab;
    }

    .role-option.active {
      border-color: #3949ab;
      background: #f0f4ff;
    }

    .role-icon {
      font-size: 24px;
      color: #3949ab;
      margin-bottom: 10px;
    }

    .role-name {
      font-weight: 600;
      color: #333;
    }

    /* Submit Button */
    .submit-btn {
      width: 100%;
      padding: 18px;
      background: linear-gradient(90deg, #1a237e 0%, #3949ab 100%);
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-top: 10px;
    }

    .submit-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(57, 73, 171, 0.3);
    }

    .submit-btn:active {
      transform: translateY(0);
    }

    /* Login Link */
    .login-link {
      text-align: center;
      margin-top: 25px;
      color: #666;
      font-size: 15px;
    }

    .login-link a {
      color: #3949ab;
      font-weight: 600;
      text-decoration: none;
    }

    .login-link a:hover {
      text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 600px) {
      .container {
        max-width: 100%;
      }
      
      .form-container {
        padding: 30px 25px;
      }
      
      .form-grid {
        grid-template-columns: 1fr;
        gap: 15px;
      }
      
      .role-selector {
        flex-direction: column;
      }
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="logo">AKSARA JIWA</div>
      <div class="tagline">Platform Buku dan Literasi</div>
    </div>
    
    <div class="form-container">
      <h2 class="form-title">Buat Akun Baru</h2>
      <p class="form-subtitle">Daftar untuk mulai berbelanja atau menjual buku</p>
      
      <?php if ($error): ?>
        <div class="error">
          <i class="fas fa-exclamation-circle"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      
      <form method="POST" enctype="multipart/form-data" id="signupForm">
        <div class="form-grid">
          <div class="form-group">
            <label>NIK (16 Digit)</label>
            <input type="text" name="nik" maxlength="16" 
                   placeholder="3273010101010001" required>
          </div>
          
          <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" 
                   placeholder="Nama lengkap Anda" required>
          </div>
          
          <div class="form-group full">
            <label>Email</label>
            <input type="email" name="email" 
                   placeholder="email@contoh.com" required>
          </div>
          
          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" id="password"
                   placeholder="Minimal 8 karakter" required>
          </div>
          
          <div class="form-group">
            <label>Konfirmasi Password</label>
            <input type="password" name="confirm_password" id="confirmPassword"
                   placeholder="Ulangi password" required>
          </div>
          
          <div class="form-group full">
            <label>Alamat</label>
            <textarea name="alamat" placeholder="Alamat lengkap"></textarea>
          </div>
          
          <div class="form-group full">
            <label>Foto Profil (Opsional)</label>
            <div class="file-upload" onclick="document.getElementById('fileInput').click()">
              <div class="upload-icon">
                <i class="fas fa-cloud-upload-alt"></i>
              </div>
              <div class="upload-text">
                Klik untuk upload foto<br>
                <small>JPG, PNG (maks. 5MB)</small>
              </div>
              <input type="file" name="image" id="fileInput" accept="image/*">
            </div>
          </div>
          
          <div class="form-group full">
            <label>Daftar Sebagai</label>
            <div class="role-selector">
              <div class="role-option" onclick="selectRole('pembeli')">
                <div class="role-icon">
                  <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="role-name">Pembeli</div>
              </div>
              
              <div class="role-option" onclick="selectRole('penjual')">
                <div class="role-icon">
                  <i class="fas fa-store"></i>
                </div>
                <div class="role-name">Penjual</div>
              </div>
            </div>
            <input type="hidden" name="role" id="selectedRole" required>
          </div>
        </div>
        
        <button type="submit" class="submit-btn">
          <i class="fas fa-user-plus"></i> Daftar Sekarang
        </button>
      </form>
      
      <div class="login-link">
        Sudah punya akun? <a href="../index.php">Login di sini</a>
      </div>
    </div>
  </div>

  <script>
    // Role Selection
    function selectRole(role) {
      // Remove active class from all options
      document.querySelectorAll('.role-option').forEach(option => {
        option.classList.remove('active');
      });
      
      // Add active class to selected option
      event.currentTarget.classList.add('active');
      
      // Set hidden input value
      document.getElementById('selectedRole').value = role;
    }
    
    // Form Validation
    document.getElementById('signupForm').addEventListener('submit', function(e) {
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirmPassword').value;
      const selectedRole = document.getElementById('selectedRole').value;
      
      // Check password match
      if (password !== confirmPassword) {
        e.preventDefault();
        alert('Password dan konfirmasi password tidak cocok!');
        return;
      }
      
      // Check password length
      if (password.length < 8) {
        e.preventDefault();
        alert('Password harus minimal 8 karakter!');
        return;
      }
      
      // Check role selection
      if (!selectedRole) {
        e.preventDefault();
        alert('Silakan pilih peran (Pembeli atau Penjual)!');
        return;
      }
      
      // Show loading state
      const btn = e.target.querySelector('.submit-btn');
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
      btn.disabled = true;
    });
    
    // File upload preview
    document.getElementById('fileInput').addEventListener('change', function(e) {
      const file = e.target.files[0];
      const uploadDiv = document.querySelector('.file-upload');
      
      if (file) {
        // Check file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
          alert('File terlalu besar! Maksimal 5MB.');
          this.value = '';
          return;
        }
        
        // Update UI
        uploadDiv.innerHTML = `
          <div class="upload-icon" style="color: #4CAF50">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="upload-text">
            ${file.name}<br>
            <small>File siap diupload</small>
          </div>
          <input type="file" name="image" id="fileInput" accept="image/*" style="display: none">
        `;
        
        // Re-add click event
        uploadDiv.onclick = () => document.getElementById('fileInput').click();
      }
    });
  </script>
</body>
</html>