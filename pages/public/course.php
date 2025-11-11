<?php
require_once("../../config/app.php");

$courseName = trim($_GET['course'] ?? '');
if ($courseName === '') {
  $rs = mysqli_query($conn, "SELECT name FROM courses WHERE is_published=1 AND name<>'' GROUP BY name ORDER BY name ASC LIMIT 1");
  $row = mysqli_fetch_assoc($rs);
  $courseName = $row['name'] ?? 'Course';
}

function safe($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// Ambil paket/plan utk course ini
$plans = [];
$stmt = mysqli_prepare($conn,
  "SELECT id, category, price, short_desc, image_url
     FROM courses
    WHERE name = ? AND is_published = 1
    ORDER BY price ASC, id ASC"
);
mysqli_stmt_bind_param($stmt, 's', $courseName);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($r = mysqli_fetch_assoc($res)) $plans[] = $r;
mysqli_stmt_close($stmt);

// Deskripsi utama → ambil dari short_desc paket pertama (kalau ada)
$main_desc = $plans[0]['short_desc'] ?? "Belajar {$courseName} dengan metode interaktif & fleksibel di SkillXpert.";

// Hero image (fallback ke assets/img/<slug>.jpg/png → bg.png)
$slug = strtolower(preg_replace('/\s+/', '-', $courseName));
$heroFallbacks = [
  $baseURL . "/assets/img/{$slug}.jpg",
  $baseURL . "/assets/img/{$slug}.png",
  $baseURL . "/assets/img/bg.png",
];
$heroImage = $plans[0]['image_url'] ?: $heroFallbacks[0];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($courseName) ?> • SkillXpert</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="<?= $baseURL ?>/assets/css/style.css">
  <style>
    body{ background:#e8f5e9; }
    .hero{ background:url('<?= e($heroImage) ?>') center/cover no-repeat; color:#fff; text-align:center; padding:100px 20px; }
    .pricing-card{ transition:transform .3s; }
    .pricing-card:hover{ transform:scale(1.05); }
  </style>
</head>
<body>

  <?php include INC_PATH . '/navbar_pub.php'; ?>
  <div style="height:72px"></div>

  <!-- HERO -->
  <section class="hero">
    <div class="container">
      <h1 class="fw-bold">Kursus <?= e($courseName) ?></h1>
      <p class="lead"></p>
    </div>
  </section>

  <!-- WHY SECTION -->
  <div class="container mt-5">
    <h2 class="text-center mb-3">Why Learning <?= e($courseName) ?> Is Important?</h2>
    <p class="text-center">
      <?= e("$courseName $main_desc") ?>
    </p>
    <p class="text-center mb-5">
        At SkillXpert, we provide an interactive and engaging learning experience across a wide range of subjects. 
        Learn with experienced instructors, explore essential concepts, and build your knowledge in a fun, accessible, and easy-to-understand way.
    </p>
  </div>

    <div class="container mt-5">
    <div class="row text-center">
      <div class="col-md-4">
        <i class="fa-solid fa-calculator fa-3x text-primary"></i>
        <h4>Simple Concept</h4>
        <p>The material is designed to be easy to understand using interactive methods.</p>
      </div>
      <div class="col-md-4">
        <i class="fa-solid fa-chalkboard-user fa-3x text-success"></i>
        <h4>Experienced Instructor</h4>
        <p>Learn from experts who are already experienced in their field.</p>
      </div>
      <div class="col-md-4">
        <i class="fa-solid fa-book-open fa-3x text-danger"></i>
        <h4>Complete Material</h4>
        <p>From basic to advanced levels, available for all levels.</p>
      </div>
    </div>
  </div>

  <!-- PLANS -->
  <div class="container mt-3">
    <h2 class="text-center mb-4">Subscription Package</h2>

    <?php if (!$plans): ?>
      <p class="text-center text-muted">There are no active packages for this course yet.</p>
    <?php else: ?>
      <div class="row g-4 justify-content-center">
        <?php foreach ($plans as $p): ?>
          <div class="col-md-4">
            <div class="card pricing-card h-100 shadow-sm">
              <?php if (!empty($p['image_url'])): ?>
                <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['category']) ?>" class="card-img-top">
              <?php endif; ?>
              <div class="card-body text-center d-flex flex-column">
                <h3 class="mb-3">⭐ <?= e($p['category']) ?></h3>
                <!-- short_desc DIHAPUS dari kartu sesuai permintaan -->
                <h4 class="mb-4">Rp<?= number_format((int)$p['price'],0,',','.') ?>/bulan</h4>
                <a href="<?= $baseURL ?>/pages/user/enroll.php?course=<?= urlencode($courseName) ?>&plan=<?= urlencode($p['category']) ?>"
                   class="btn btn-success mt-auto">Langganan</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <footer class="py-5">
    <div class="text-center py-3 text-black">
      &copy; Copyright <strong>SkillXpert</strong> All Rights Reserved
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
