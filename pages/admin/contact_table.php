<?php
require_once("../../config/app.php");
require_once INC_PATH . '/require_login.php';
if (($_SESSION['role'] ?? '') !== 'admin') redirect_to('pages/auth/login.php?error=' . rawurlencode('Must log in as admin.'));

function safe($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$rows = [];
$q = "SELECT id, name, email, subject, message, created_at FROM contact_messages ORDER BY id DESC";
$res = mysqli_query($conn, $q);
if ($res) { while($r = mysqli_fetch_assoc($res)) $rows[] = $r; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Admin • Contact Messages</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= url('/assets/css/style.css?v=20251019') ?>">
  <style>
    .container{padding:24px}
    .table-responsive{overflow:auto;border:1px solid #e5e7eb;border-radius:12px;background:#fff}
    table{width:100%;border-collapse:collapse}
    th,td{padding:12px 14px;border-bottom:1px solid #f1f5f9;vertical-align:top}
    th{background:#f6f8fa;font-weight:700}
    .msg{white-space:pre-wrap;max-width:520px}
  </style>
</head>
<body>
  <?php include dirname(__DIR__) . '/includes/navbar.php'; ?>
  <div class="main-content">
    <div class="container">
      <h2 class="page-title"><i class="fa-solid fa-envelope"></i> Contact Messages</h2>
      <div class="breadcrumbs"><a href="<?= url('pages/admin/index2.php') ?>">Dashboard</a> / Contact</div>

      <?php if (!empty($_GET['msg'])): ?>
        <div id="crudAlert" class="alert alert-success" role="alert" style="border-radius:8px;">
          <?= e($_GET['msg']) ?>
        </div>
      <?php endif; ?>

      <div class="table-responsive">
        <table class="table table-striped mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Subject</th>
              <th>Message</th>
              <th>Received</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$rows): ?>
            <tr><td colspan="7" style="text-align:center;color:#64748b;padding:24px">No messages.</td></tr>
          <?php else: foreach($rows as $i=>$r): ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td><?= safe($r['name']) ?></td>
              <td><a href="mailto:<?= safe($r['email']) ?>"><?= safe($r['email']) ?></a></td>
              <td><?= safe($r['subject']) ?></td>
              <td class="msg"><?= safe($r['message']) ?></td>
              <td><?= safe($r['created_at']) ?></td>
              <td class="text-nowrap">
                <a href="<?= url('pages/admin/manage_data/update.php?table=contact&id='.(int)$r['id']) ?>"
                   class="text-decoration-none btn btn-sm btn-edit">Edit</a>
                <a href="<?= url('pages/admin/manage_data/delete.php?table=contact&id='.(int)$r['id']) ?>"
                   class="text-decoration-none btn btn-sm btn-delete"
                   onclick="return confirm('Delete this message?');">Delete</a>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const alertBox = document.getElementById('crudAlert');
    if (alertBox && window.bootstrap?.Alert) {
      setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertBox);
        bsAlert.close();
      }, 1000);
    }
  });
</script>
</body> 
</html>
