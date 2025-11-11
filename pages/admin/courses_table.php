<?php
require_once("../../config/app.php");
require_once INC_PATH . '/require_login.php';
if (($_SESSION['role'] ?? '') !== 'admin') redirect_to('pages/auth/login.php?error=' . rawurlencode('Must log in as admin.'));

function safe($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$rows = [];
$sql = "SELECT c.id, c.name, c.price, c.short_desc, c.image_url, c.is_published, c.created_at,
               g.name AS category_name
        FROM courses c
        LEFT JOIN categories g ON g.id = c.category_id
        ORDER BY c.id DESC";
$res = mysqli_query($conn, $sql);
if ($res) { while($r = mysqli_fetch_assoc($res)) $rows[] = $r; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Admin • Courses</title>
  <link rel="stylesheet" href="<?= url('/assets/css/style.css') ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
  <?php include dirname(__DIR__) . '/includes/navbar.php'; ?>
  <div class="main-content">
    <div class="content-wrapper">
      <h2 class="page-title"><i class="fa-solid fa-book"></i> Courses</h2>
      <div class="breadcrumbs"><a href="<?= url('pages/admin/index2.php') ?>">Dashboard</a> / Courses</div>

      <?php if (!empty($_GET['msg'])): ?>
        <div class="alert alert-success" role="alert" id="crudAlert" style="border-radius:8px;"><?= e($_GET['msg']) ?></div>
      <?php endif; ?>

      <a href="<?= url('pages/admin/manage_data/tambah.php?table=courses') ?>" class="btn">
        <i class="fa-solid fa-plus"></i> Add Course
      </a>

      <div class="table-wrapper">
        <table class="table mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Image</th>
              <th>Course</th>
              <th>Category</th>
              <th>Price</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$rows): ?>
            <tr><td colspan="8" style="text-align:center;color:#64748b;padding:24px">No data.</td></tr>
          <?php else: foreach($rows as $i=>$r): ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td>
                <?php if (!empty($r['image_url'])): ?>
                  <img class="thumb" src="<?= e(str_starts_with($r['image_url'],'http') ? $r['image_url'] : $baseURL.$r['image_url']) ?>" alt="">
                <?php endif; ?>
              </td>
              <td><?= e($r['name']) ?></td>
              <td><?= e($r['category_name'] ?? '-') ?></td>
              <td>Rp<?= number_format((int)$r['price'],0,',','.') ?></td>
              <td>
                <?php if (!empty($r['is_published'])): ?>
                  <span class="status status-published">Published</span>
                <?php else: ?>
                  <span class="status status-draft">Draft</span>
                <?php endif; ?>
              </td>
              <td><?= e($r['created_at']) ?></td>
              <td class="text-nowrap">
              <a href="<?= url('pages/admin/manage_data/update.php?table=courses&id='.(int)$r['id']) ?>"
                class="text-decoration-none btn btn-sm btn-edit">Edit</a>

              <a href="<?= url('pages/admin/manage_data/delete.php?table=courses&id='.(int)$r['id']) ?>"
                class="text-decoration-none btn btn-sm btn-delete"
                onclick="return confirm('Delete this item?');">Delete</a>
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
  if (alertBox) setTimeout(() => alertBox.remove(), 1500);
});
</script>
</body>
</html>
