<?php
require_once("../../config/app.php");
require_once INC_PATH . '/require_login.php';

if (!function_exists('e')) {
  function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

/* --- Ambil parameter --- */
$course_id   = (int)($_GET['course_id'] ?? 0); // opsional
$course_name = trim($_GET['course'] ?? '');    // opsional, mis. "Biology"
$course_slug = trim($_GET['slug'] ?? '');      // opsional, mis. "biology"
$plan_name   = trim($_GET['plan'] ?? '');      // opsional, mis. Pemula/Menengah/Profesional

/* --- Cari course: id -> slug -> name/title --- */
$course = null;

if ($course_id > 0) {
  $sql = "SELECT id, COALESCE(name) AS name short_desc, price, image_url,
                 COALESCE(is_published, is_published, 1) AS pub
          FROM courses
          WHERE id = ?
            AND COALESCE(is_published, is_published, 1) = 1
          LIMIT 1";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, 'i', $course_id);

} elseif ($course_slug !== '') {
  $sql = "SELECT id, COALESCE(name) AS name short_desc, price, image_url,
                 COALESCE(is_published, is_published, 1) AS pub
          FROM courses
          WHERE slug = ?
            AND COALESCE(is_published, is_published, 1) = 1
          LIMIT 1";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, 's', $course_slug);

} elseif ($course_name !== '') {
  $sql = "SELECT id, COALESCE(name) AS name, short_desc, price, image_url,
                 COALESCE(is_published, is_published, 1) AS pub
          FROM courses
          WHERE (name = ? OR name = ?)
            AND COALESCE(is_published, is_published, 1) = 1
          LIMIT 1";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, 'ss', $course_name, $course_name);

} else {
  header("Location: " . url('pages/public/index.php') . "?error=" . rawurlencode("Course tidak valid"));
  exit;
}

mysqli_stmt_execute($stmt);
$res    = mysqli_stmt_get_result($stmt);
$course = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$course) {
  header("Location: " . url('pages/public/index.php') . "?error=" . rawurlencode("Course tidak ditemukan / tidak aktif"));
  exit;
}

// Normalisasi course_id dari hasil query (kalau awalnya pakai slug/nama)
$course_id = (int)$course['id'];

/* --- Data user --- */
$user_id = (int)$_SESSION['user_id'];
$uRes = mysqli_query($conn, "SELECT id, nama, email FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($uRes) ?: ['nama'=>'','email'=>''];

/* --- Plan (categories.name) opsional --- */
$plan = null;
$final_price = (int)($course['price'] ?? 0);

if ($plan_name !== '') {
  $stmt = mysqli_prepare($conn, "SELECT id, name, price, short_desc FROM categories WHERE name = ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, 's', $plan_name);
  mysqli_stmt_execute($stmt);
  $pres = mysqli_stmt_get_result($stmt);
  $plan  = mysqli_fetch_assoc($pres) ?: null;
  mysqli_stmt_close($stmt);

  if ($plan) {
    $final_price = (int)$plan['price'];
  }
}

/* --- CSRF --- */
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

/* --- Flash --- */
$success = $_GET['success'] ?? '';
$error   = $_GET['error'] ?? '';

/* --- Submit --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    $error = "CSRF token tidak valid.";
  } else {
    $agree = isset($_POST['agree']);
    if (!$agree) {
      $error = "You must agree to the Terms and Conditions.";
    } else {
      // Sudah daftar?
      $check = mysqli_prepare($conn, "SELECT id FROM enrollments WHERE user_id = ? AND course_id = ? AND status IN ('pending','active') LIMIT 1");
      mysqli_stmt_bind_param($check, 'ii', $user_id, $course_id);
      mysqli_stmt_execute($check);
      $cres = mysqli_stmt_get_result($check);
      $exists = mysqli_fetch_assoc($cres);
      mysqli_stmt_close($check);

      if ($exists) {
        $error = "You have already registered for this course (pending/active).";
      } else {
        $plan_id = $plan['id'] ?? null;
        $status  = 'pending';
        $price   = $final_price;

        // Kalau tabel enrollments TIDAK punya kolom plan_id, ubah query di bawah:
        // "INSERT INTO enrollments (user_id, course_id, price, status, created_at) VALUES (?, ?, ?, ?, NOW())"
        // dan bind jadi 'iiss' tanpa $plan_id.
        $stmt = mysqli_prepare($conn, "INSERT INTO enrollments (user_id, course_id, plan_id, price, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, 'iiiis', $user_id, $course_id, $plan_id, $price, $status);
        $ok = mysqli_stmt_execute($stmt);
        $newId = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        if (!$ok) {
          $error = "Failed to save registration.";
        } else {
          // Redirect PRG: pertahankan parameter yang dipakai
          $q = [];
          if ($course_slug !== '') $q[] = "slug=" . rawurlencode($course_slug);
          elseif ($course_name !== '') $q[] = "course=" . rawurlencode($course_name);
          else $q[] = "course_id=" . $course_id;
          if ($plan_name !== '') $q[] = "plan=" . rawurlencode($plan_name);
          $q[] = "success=" . rawurlencode("Registration successfully created (ID: $newId).");

          header("Location: " . url("pages/user/enroll.php") . "?" . implode('&', $q));
          exit;
        }
      }
    }
  }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Enroll Course • SkillXpert</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Vendor -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <!-- App -->
  <link rel="stylesheet" href="<?= $baseURL ?>/assets/css/style.css">
  <style>
    body{ background:#eaf7e8; }
    .card-lite{
      background:#fff; border:1px solid #e5e7eb; border-radius:14px; padding:20px;
      box-shadow:0 8px 22px rgba(46,125,50,.10);
    }
    .thumb{max-height:140px;border-radius:10px;border:1px solid #e5e7eb}
  </style>
</head>
<body>

<?php include INC_PATH . '/navbar_pub.php'; ?>
<div style="height:72px"></div>

<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">

      <h2 class="mb-3"><i class="fa-solid fa-cart-plus me-2"></i>Course Registration Form</h2>

      <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
      <?php endif; ?>

      <div class="card-lite mb-4">
        <div class="row g-3 align-items-center">
          <div class="col-md-3 text-center">
            <?php if (!empty($course['image_url'])): ?>
              <img class="thumb img-fluid" src="<?= e($course['image_url']) ?>" alt="Course">
            <?php else: ?>
              <div class="text-muted">Tidak ada gambar</div>
            <?php endif; ?>
          </div>
          <div class="col-md-9">
            <h4 class="mb-1"><?= e($course['name']) ?></h4>
            <?php if (!empty($course['short_desc'])): ?>
              <div class="text-muted mb-2"><?= e($course['short_desc']) ?></div>
            <?php endif; ?>
            <div>
              <?php if ($plan): ?>
                <div><span class="badge bg-success me-2">Plan</span> <?= e($plan['name']) ?></div>
              <?php endif; ?>
              <div class="fs-5 mt-2">
                Total: <strong>Rp<?= number_format((int)$final_price,0,',','.') ?></strong> / bulan
              </div>
            </div>
          </div>
        </div>
      </div>

      <form method="post" class="card-lite">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nama</label>
            <input type="text" class="form-control" value="<?= e($user['nama']) ?>" disabled>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
          </div>
          <div class="col-12">
            <label class="form-label">Notes (optional)</label>
            <textarea class="form-control" name="note" rows="3" placeholder="Additional notes for admin (optional)"></textarea>
          </div>
          <div class="col-12 form-check">
            <input class="form-check-input" type="checkbox" value="1" id="agree" name="agree" required>
            <label class="form-check-label" for="agree">
              I agree to the terms and conditions.
            </label>
          </div>
          <div class="col-12">
            <button class="btn btn-success">
              <i class="fa-solid fa-check me-1"></i>Registration Confirmation
            </button>
            <a href="<?= url('pages/public/index.php') ?>" class="btn btn-secondary ms-2">Cancel</a>
          </div>
        </div>
      </form>

    </div>
  </div>
</div>

<footer class="py-5">
  <div class="text-center py-3 text-black">
    &copy; Copyright <strong>SkillXpert</strong> All Rights Reserved
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
