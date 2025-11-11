<?php
require_once("../../config/app.php");
require_once INC_PATH . '/require_login.php';
if (($_SESSION['role'] ?? '') !== 'admin') {
  redirect_to('pages/auth/login.php?error=' . rawurlencode('Must log in as admin'));
}

function safe($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// ambil data enrollments + relasi
$rows = [];
$sql = "
SELECT
  e.id,
  e.created_at,
  e.price,
  e.status,
  u.nama  AS user_name,
  u.email AS user_email,
  c.name AS course_name,
  cat.name AS plan_name
FROM enrollments e
JOIN users    u   ON u.id = e.user_id
JOIN courses  c   ON c.id = e.course_id
LEFT JOIN categories cat ON cat.id = e.plan_id
ORDER BY e.id DESC";
if ($res = mysqli_query($conn, $sql)) {
  while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Admin • Enrollments</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="<?= url('/assets/css/style.css') ?>">
</head>
<body>
  <?php include INC_PATH . '/navbar.php'; ?>
  <div class="main-content">
    <div class="container">
      <h2 class="page-title"><i class="fa-solid fa-graduation-cap"></i> Enrollments</h2>
      <div class="breadcrumbs"><a href="<?= url('pages/admin/index2.php') ?>">Dashboard</a> / Enrollments</div>

      <?php if (!empty($_GET['msg'])): ?>
        <div class="alert alert-success" style="border-radius:8px;"><?= safe($_GET['msg']) ?></div>
      <?php endif; ?>

      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>User</th>
              <th>Email</th>
              <th>Course</th>
              <th>Plan</th>
              <th>Price</th>
              <th>Status</th>
              <th>Enrolled At</th>
              <th class="text-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$rows): ?>
              <tr><td colspan="9" style="text-align:center;color:#64748b;padding:24px">No enrollments.</td></tr>
            <?php else: foreach($rows as $i=>$r): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><?= safe($r['user_name']) ?></td>
                <td><a href="mailto:<?= safe($r['user_email']) ?>"><?= safe($r['user_email']) ?></a></td>
                <td><?= safe($r['course_name']) ?></td>
                <td><?= safe($r['plan_name'] ?: '-') ?></td>
                <td>Rp<?= number_format((int)$r['price'],0,',','.') ?></td>
                <td>
                  <?php
                    $st = strtolower($r['status'] ?? 'pending');
                    $cls = $st==='active' ? 'badge-active' : ($st==='cancelled' ? 'badge-cancel' : 'badge-pending');
                  ?>
                  <span class="badge <?= $cls ?>"><?= safe(ucfirst($st)) ?></span>
                </td>
                <td><?= safe($r['created_at']) ?></td>
                <td class="text-nowrap">
                  <a href="<?= url('pages/admin/manage_data/update.php?table=enrollments&id='.(int)$r['id']) ?>" class="text-decoration-none btn btn-sm btn-edit">Edit</a>
                  <a href="<?= url('pages/admin/manage_data/delete.php?table=enrollments&id='.(int)$r['id']) ?>"
                     class="text-decoration-none btn btn-sm btn-delete text"
                     onclick="return confirm('Delete this enrollment?');">Delete</a>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
