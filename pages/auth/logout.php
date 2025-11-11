<?php
require_once("../../config/app.php");

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Bersihkan data session
$_SESSION = [];

// Hapus cookie session (jika ada)
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// Tutup session total
session_regenerate_id(true);
session_destroy();

// Redirect ke login
redirect_to('pages/auth/login.php?error=' . rawurlencode('You have logged out'));
