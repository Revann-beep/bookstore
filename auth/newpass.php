<?php
require 'connection.php';

if (!isset($_GET['token'])) {
    die("Token tidak valid");
}

$token = $_GET['token'];
$data = mysqli_query($conn, "SELECT * FROM users WHERE reset_token='$token' AND reset_expired > NOW()");

if (mysqli_num_rows($data) == 0) {
    die("Token kadaluarsa atau salah");
}

if (isset($_POST['password'])) {
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    mysqli_query($conn, "
        UPDATE users 
        SET password='$pass', reset_token=NULL, reset_expired=NULL 
        WHERE reset_token='$token'
    ");

    echo "Password berhasil direset. <a href='../index.php'>Login</a>";
    exit;
}
?>

<form method="POST">
    <h3>Reset Password</h3>
    <input type="password" name="password" required placeholder="Password baru">
    <button type="submit">Reset</button>
</form>
