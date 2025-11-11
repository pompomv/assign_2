<?php
require_once("../../config/app.php");

/* Helper lokal untuk redirect dengan pesan error */
function back_with_error($msg) {
  redirect_to('pages/auth/login.php?error=' . rawurlencode($msg));
}

/* Validasi method */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect_to('pages/auth/login.php');
}

/* Ambil input */
$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

if ($email === '' || $pass === '') {
  back_with_error('Email dan password wajib');
}

/* Query user by email (prepared, kompatibel tanpa mysqlnd) */
$sql  = "SELECT id, nama, email, password_hash, role FROM users WHERE email = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
  error_log('LOGIN prepare failed: ' . mysqli_error($conn));
  back_with_error('Terjadi kesalahan sistem (prep)');
}

mysqli_stmt_bind_param($stmt, "s", $email);
if (!mysqli_stmt_execute($stmt)) {
  error_log('LOGIN execute failed: ' . mysqli_error($conn));
  back_with_error('Terjadi kesalahan sistem (exec)');
}

mysqli_stmt_store_result($stmt);

$user = null;
if (mysqli_stmt_num_rows($stmt) === 1) {
  mysqli_stmt_bind_result($stmt, $id, $nama, $emailDb, $hashDb, $role);
  mysqli_stmt_fetch($stmt);

  // Validasi hash dasar
  if ($hashDb === null || strlen($hashDb) < 60 || strpos($hashDb, '$2y$') !== 0) {
    error_log("LOGIN hash invalid for {$emailDb}: len=" . strlen((string)$hashDb));
    back_with_error('Email atau password salah');
  }

  $user = [
    'id' => (int)$id,
    'nama' => $nama,
    'email' => $emailDb,
    'password_hash' => $hashDb,
    'role' => $role
  ];
}
mysqli_stmt_close($stmt);

if (!$user || !password_verify($pass, $user['password_hash'])) {
  back_with_error('Email atau password salah');
}

/* Set session */
$_SESSION['user_id']   = $user['id'];
$_SESSION['user_name'] = $user['nama'];
$_SESSION['role']      = $user['role'];

/* Redirect by role */
if ($user['role'] === 'admin') {
  redirect_to('pages/admin/index2.php');
} else {
  redirect_to('pages/public/index.php');
}
