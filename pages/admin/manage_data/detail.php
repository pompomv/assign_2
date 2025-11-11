<?php
require_once("../../../config/app.php");
require_once INC_PATH . '/require_login.php';
if (($_SESSION['role'] ?? '') !== 'admin') redirect_to('pages/auth/login.php?error=' . rawurlencode('Admin only'));

$SCHEMA = [
  'carousel_slides' => ['label'=>'Carousels','pk'=>'id','list_cols'=>['id','title','image_url','is_active','created_at']],
  'contacts'  => ['label'=>'Contacts','pk'=>'id','list_cols'=>['id','name','email','subject','message','created_at']],
  'courses'   => ['label'=>'Courses','pk'=>'id','list_cols'=>['id','title','slug','price','is_active','created_at']],
  'enrollments'=>['label'=>'Enrollments','pk'=>'id','list_cols'=>['id','user_id','course_id','created_at']],
];
$table = $_GET['table'] ?? '';  if (!isset($SCHEMA[$table])) die('Invalid table');
$meta  = $SCHEMA[$table]; $pk = $meta['pk']; $cols = $meta['list_cols'];

$rows = [];
if ($table === 'enrollments') {
  $sql = "SELECT e.id, u.nama AS user_id, c.title AS course_id, e.created_at
          FROM enrollments e
          JOIN users u ON u.id=e.user_id
          JOIN courses c ON c.id=e.course_id
          ORDER BY e.id DESC";
} else {
  $sql = "SELECT ".implode(',', $cols)." FROM `$table` ORDER BY `$pk` DESC";
}
$res = mysqli_query($conn, $sql);
if ($res) while($r=mysqli_fetch_assoc($res)) $rows[]=$r;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= e($meta['label']) ?> • List</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<?php include dirname(INC_PATH) . '/includes/navbar.php'; ?>
<div class="main-content p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="m-0"><?= e($meta['label']) ?></h3>
    <a class="btn btn-success" href="<?= url("pages/admin/manage_data/tambah.php?table={$table}") ?>">+ Add</a>
  </div>

  <?php if (!empty($_GET['msg'])): ?>
    <div class="alert alert-success"><?= e($_GET['msg']) ?></div>
  <?php endif; ?>

  <div class="table-responsive bg-white rounded shadow-sm">
    <table class="table table-striped mb-0">
      <thead>
        <tr>
          <?php foreach ($cols as $c): ?><th><?= e(ucfirst($c)) ?></th><?php endforeach; ?>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="<?= count($cols)+1 ?>" class="text-center text-muted py-4">No data</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <?php foreach ($cols as $c): ?>
              <td>
                  <?php
                  if ($table === 'carousel_slides' && $c === 'image_url' && !empty($r[$c])) {
                    echo '<img src="'.e($r[$c]).'" alt="" style="height:40px;border:1px solid #eee;border-radius:6px">';
                  } elseif (in_array($c, ['is_active'], true)) {
                    echo $r[$c] ? '<span class="badge bg-success">Active</span>'
                                : '<span class="badge bg-secondary">Inactive</span>';
                  } elseif ($table === 'courses' && $c === 'price' && $r[$c] !== null) {
                    echo 'Rp ' . number_format($r[$c], 0, ',', '.');
                  } else {
                    echo e($r[$c]);
                  }
                ?>
              </td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    <a href="<?= url('pages/admin/index2.php') ?>" class="btn btn-outline-secondary">&larr; Back to Dashboard</a>
  </div>
</div>
</body>
</html>
