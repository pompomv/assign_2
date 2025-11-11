<?php
require_once __DIR__ . '/../../config/app.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

if (!function_exists('redirect_to')) {
  function redirect_to($path) {
    global $baseURL;
    header('Location: ' . rtrim($baseURL,'/') . '/' . ltrim($path,'/'));
    exit;
  }
}

// cek session login
if (!isset($_SESSION['user_id'])) {
  redirect_to('pages/auth/login.php?error=' . rawurlencode('Please log in first'));
}
