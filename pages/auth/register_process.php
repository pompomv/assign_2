<?php
require_once("../../config/app.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: " . url('pages/auth/register.php'));
  exit;
}

$_SESSION['flash_success'] = 'Registration successful! Please log in.';
redirect_to('pages/auth/login.php');

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($name === '' || $email === '' || $password === '' || $confirm === '') {
  header("Location: " . url('pages/auth/register.php?error=All fields are required'));
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  header("Location: " . url('pages/auth/register.php?error=Invalid email format'));
  exit;
}

if ($password !== $confirm) {
  header("Location: " . url('pages/auth/register.php?error=Passwords do not match'));
  exit;
}

// Check if email already exists
$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
  mysqli_stmt_close($stmt);
  header("Location: " . url('pages/auth/register.php?error=Email is already registered'));
  exit;
}
mysqli_stmt_close($stmt);

// Hash password
$hash = password_hash($password, PASSWORD_BCRYPT);

// Insert user (role = 'user')
$stmt = mysqli_prepare($conn, "INSERT INTO users (nama, email, password_hash, role, created_at, updated_at) VALUES (?, ?, ?, 'user', NOW(), NOW())");
mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hash);

if (mysqli_stmt_execute($stmt)) {
  header("Location: " . url('pages/auth/login.php?success=Registration successful! Please log in.'));
} else {
  header("Location: " . url('pages/auth/register.php?error=Registration failed, please try again'));
}
mysqli_stmt_close($stmt);
exit;
