<?php
require_once("../../config/app.php");
require_once INC_PATH . "/require_login.php";

if (($_SESSION['role'] ?? '') !== 'admin') {
  redirect_to('pages/auth/login.php?error=' . rawurlencode('Must log in as admin.'));
}

function safe($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// ambil data
$rows = [];
$q = mysqli_query($conn, "SELECT id, title, image_url, is_active, created_at FROM carousel_slides ORDER BY id DESC");
while ($r = mysqli_fetch_assoc($q)) { $rows[] = $r; }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Carousel Slides</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<?php include INC_PATH . '/navbar.php'; ?>

<div class="main-content">
  <div class="content-wrapper">
    <h2 class="page-title"><i class="fa-regular fa-images"></i> Carousel Slides</h2>
    <div class="breadcrumbs"><a href="<?= url('pages/admin/index2.php') ?>">Dashboard</a> / Coursel</div>
    <?php if (!empty($_GET['msg'])): ?>
      <div class="alert alert-success" role="alert" style="border-radius:8px;">
        <?= e($_GET['msg']) ?>
      </div>
    <?php endif; ?>
      <a href="<?= url('pages/admin/manage_data/tambah.php?table=carousel_slides') ?>" class="btn">
        <i class="fa-solid fa-plus"></i> Add Slide
      </a>
   <div class="table-wrapper">
  <table class="table mb-0">
    <thead>
      <tr>
        <th>#</th>
        <th>Title</th>
        <th>Image</th>
        <th>Active</th>
        <th>Updated</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($rows)): ?>
      <tr><td colspan="6" style="text-align:center;color:#64748b;padding:24px">No data.</td></tr>
    <?php else: foreach($rows as $i => $r): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><?= safe($r['title']) ?></td>
        <td>
          <?php if (!empty($r['image_url'])): ?>
            <img class="thumb" src="<?= safe($r['image_url']) ?>" alt="">
          <?php endif; ?>
        </td>
        <td>
          <?php if (!empty($r['is_active'])): ?>
            <span class="badge-active">Active</span>
          <?php else: ?>
            <span class="badge-inactive">Inactive</span>
          <?php endif; ?>
        </td>
        <td><?= safe($r['updated_at'] ?? $r['created_at'] ?? '') ?></td>
        <td class="text-nowrap">
          <a href="<?= url('pages/admin/manage_data/update.php?table=carousel_slides&id='.(int)$r['id']) ?>"
             class="btn btn-sm btn-edit">Edit</a>
          <a href="<?= url('pages/admin/manage_data/delete.php?table=carousel_slides&id='.(int)$r['id']) ?>"
             class="btn btn-sm btn-delete"
             onclick="return confirm('Delete this slide?');">Delete</a>
        </td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
    <div class="form-actions">
      <a href="<?= url('pages/admin/index2.php') ?>" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
      document.addEventListener("DOMContentLoaded", function () {
    const alertBox = document.getElementById('crudAlert');
    if (alertBox) {
      setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertBox);
        bsAlert.close();
      }, 1000);
    }
  });
</script>
</body>
</html>
