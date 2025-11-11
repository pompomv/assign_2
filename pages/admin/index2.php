<?php
include("../../config/app.php");
require_once dirname(__DIR__) . '/includes/require_login.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
  redirect_to('pages/auth/login.php?error=' . rawurlencode('Must log in as admin'));
}

function table_exists(mysqli $conn, string $table): bool {
  $db = mysqli_fetch_row(mysqli_query($conn, "SELECT DATABASE()"))[0];
  $stmt = mysqli_prepare($conn, "SELECT 1 FROM information_schema.tables WHERE table_schema=? AND table_name=? LIMIT 1");
  mysqli_stmt_bind_param($stmt, "ss", $db, $table);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_store_result($stmt);
  $exists = mysqli_stmt_num_rows($stmt) > 0;
  mysqli_stmt_close($stmt);
  return $exists;
}
function column_exists(mysqli $conn, string $table, string $column): bool {
  $db = mysqli_fetch_row(mysqli_query($conn, "SELECT DATABASE()"))[0];
  $stmt = mysqli_prepare($conn, "SELECT 1 FROM information_schema.columns WHERE table_schema=? AND table_name=? AND column_name=? LIMIT 1");
  mysqli_stmt_bind_param($stmt, "sss", $db, $table, $column);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_store_result($stmt);
  $exists = mysqli_stmt_num_rows($stmt) > 0;
  mysqli_stmt_close($stmt);
  return $exists;
}
function fetch_count(mysqli $conn, string $sql): int {
  $res = mysqli_query($conn, $sql);
  if (!$res) return 0;
  [$n] = mysqli_fetch_row($res);
  return (int)$n;
}

if (empty($_SESSION['user_name'])) {
  $stmt = mysqli_prepare($conn, "SELECT nama FROM users WHERE id = ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_bind_result($stmt, $nama);
  mysqli_stmt_fetch($stmt);
  mysqli_stmt_close($stmt);
  $_SESSION['user_name'] = $nama ?: 'User';
}
$userName = htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8');

if (table_exists($conn, 'courses')) {
  if (column_exists($conn, 'courses', 'is_active')) {
    $total_courses = fetch_count($conn, "SELECT COUNT(*) FROM courses WHERE is_active = 1");
  } else {
    $total_courses = fetch_count($conn, "SELECT COUNT(*) FROM courses");
  }
} else {
  $total_courses = 0; // tabel belum dibuat
}

$enroll_table = table_exists($conn, 'enrollments') ? 'enrollments' : (table_exists($conn, 'user_courses') ? 'user_courses' : null);
if ($enroll_table) {
  if (column_exists($conn, $enroll_table, 'created_at')) {
    $new_enrollments = fetch_count($conn, "SELECT COUNT(*) FROM {$enroll_table} WHERE created_at >= (NOW() - INTERVAL 7 DAY)");
  } else {
    $new_enrollments = fetch_count($conn, "SELECT COUNT(*) FROM {$enroll_table}");
  }
} else {
  $new_enrollments = 0;
}

$carousel_table = 'carousel_slides';
if (!function_exists('column_exists')) {
  function column_exists(mysqli $conn, string $table, string $column): bool {
    $db = mysqli_fetch_row(mysqli_query($conn, "SELECT DATABASE()"))[0];
    $stmt = mysqli_prepare($conn,
      "SELECT 1 FROM information_schema.columns
       WHERE table_schema=? AND table_name=? AND column_name=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "sss", $db, $table, $column);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $ok = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $ok;
  }
}
$active_carousels = 0;
if (column_exists($conn, $carousel_table, 'is_active')) {
  $q = mysqli_query($conn, "SELECT COUNT(*) FROM `$carousel_table` WHERE is_active=1");
  $active_carousels = (int) mysqli_fetch_row($q)[0];
} else {
  // fallback kalau tidak ada kolom is_active
  $q = mysqli_query($conn, "SELECT COUNT(*) FROM `$carousel_table`");
  $active_carousels = (int) mysqli_fetch_row($q)[0];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SX Courses - Dashboard</title>
        <link rel="stylesheet" href="<?= $baseURL ?>/assets/css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
        <style>
            :root{ --primary-color:#2e7d32; }
            .summary-cards { display: flex; gap: 30px; margin-bottom: 30px; flex-wrap: wrap; }
            .card { 
                color: white;
                padding: 25px;
                border-radius: 12px;
                flex: 1 1 260px;
                text-align: left;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                position: relative;
                overflow: hidden;
                min-width: 260px;
            }
            .card h3 { margin: 0; font-size: 18px; font-weight: 500; opacity: 0.9; }
            .card .count { font-size: 48px; font-weight: bold; margin: 5px 0 0 0; }
            .card .icon { font-size: 60px; position: absolute; right: 20px; top: 50%; transform: translateY(-50%); opacity: 0.2; }
            .card.courses { background: linear-gradient(45deg, #007bff, #0056b3); }
            .card.enrollments { background: linear-gradient(45deg, #28a745, #1e7e34); }
            .main-content { padding: 24px; }
            .subtitle{ color:#64748b; margin-top:6px; }
        </style>
</head>
<body>
  <?php include dirname(__DIR__) . '/includes/navbar.php'; ?>
<div class="main-content">
    <div class="content-wrapper">
        <h1><i class="fa-solid fa-tachometer-alt"></i> Dashboard</h1>
        <p class="subtitle">Welcome, <?= $userName ?>! Here is a summary of data activity on the website.</p>

        <div class="summary-cards">
            <div class="card courses">
                <h3>Courses Active</h3>
                <p class="count"><?= number_format($total_courses) ?></p>
                <i class="fa-solid fa-book icon"></i>
            </div>
            <div class="card enrollments">
                <h3>New Enrollments<?= ($enroll_table && column_exists($conn, $enroll_table, 'created_at')) ? ' (7 days)' : '' ?></h3>
                <p class="count"><?= number_format($new_enrollments) ?></p>
                <i class="fa-solid fa-user-plus icon"></i>
            </div>
          <?php if ($active_carousels > 0): ?>
            <div class="card carousels">
              <h3>Active Carousels</h3>
              <p class="count"><?= number_format($active_carousels) ?></p>
              <i class="fa-solid fa-images icon"></i>
            </div>
          <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
