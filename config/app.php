<?php
// config/app.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/* ==== Base URL (tanpa slash akhir) ==== */
$baseURL = "http://localhost/assign_2";

/* ==== Path helper (optional, memudahkan include) ==== */
if (!defined('BASE_PATH')) define('BASE_PATH', realpath(__DIR__ . '/..'));            // .. = root project
if (!defined('PAGES_PATH')) define('PAGES_PATH', BASE_PATH . '/pages');
if (!defined('INC_PATH'))   define('INC_PATH',   PAGES_PATH . '/includes');

/* ==== Koneksi DB (MySQLi, error mode strict) ==== */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = mysqli_connect("localhost", "root", "", "online_course");
mysqli_set_charset($conn, "utf8mb4");

/* ==== Helper URL ==== */
function url(string $path = ''): string {
  global $baseURL;
  return rtrim($baseURL, '/') . '/' . ltrim($path, '/');
}
function asset(string $path): string {
  return url('assets/' . ltrim($path, '/'));
}
function redirect_to(string $path): void {
  header('Location: ' . url($path));
  exit;
}

/* ==== Utility kecil ==== */
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function admin_table_url(string $table): string {
  $map = [
    'carousel_slides' => 'carousel_table.php',
    'contacts'        => 'contact_table.php',
    'courses'         => 'courses_table.php',
    'enrollments'     => 'enrollments_table.php',
  ];
  $file = $map[$table] ?? 'index2.php';
  return url('pages/admin/' . $file);
}

/* ==== Redirect CRUD success helper ==== */
function redirect_crud_success(string $table, string $message): void {
  $msg = rawurlencode($message);
  header('Location: ' . admin_table_url($table) . "?msg={$msg}");
  exit;
}