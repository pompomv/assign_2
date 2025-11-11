<?php
require_once("../../config/app.php"); // pastikan $conn & $baseURL ada

if (!function_exists('e')) {
  function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

/** Ambil daftar nama course dari tabel courses (distinct) */
$pub_courses = [];
$sql = "SELECT DISTINCT name
          FROM courses
         WHERE name IS NOT NULL AND name <> ''
           AND (is_published = 1 OR is_published IS NULL)
      ORDER BY name ASC";
if ($r = mysqli_query($conn, $sql)) {
  while ($row = mysqli_fetch_assoc($r)) $pub_courses[] = $row['name'];
}

/** Course aktif dari query (?course=...) — fallback dukung ?cat= biar kompatibel */
$currentCourse = $_GET['course'] ?? ($_GET['cat'] ?? '');
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">

<nav class="navbar navbar-expand-lg fixed-top">
  <div class="container">
    <a class="navbar-brand" href="<?= $baseURL ?>/pages/public/index.php">
      <img src="<?= $baseURL ?>/assets/img/S.png" alt="SkillXpert" height="40">
      SkillXpert
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPublic"
            aria-controls="navbarPublic" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarPublic">
      <ul class="navbar-nav ms-auto">

        <li class="nav-item">
          <a class="nav-link" href="<?= $baseURL ?>/pages/public/index.php">Home</a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= $currentCourse ? 'active' : '' ?>"
             href="#" id="kursusDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Kursus
          </a>
          <ul class="dropdown-menu" aria-labelledby="kursusDropdown">
            <?php if (!empty($pub_courses)): ?>
              <?php foreach ($pub_courses as $nm): ?>
                <li>
                  <a class="dropdown-item <?= ($currentCourse === $nm ? 'active' : '') ?>"
                     href="<?= $baseURL ?>/pages/public/course.php?course=<?= urlencode($nm) ?>">
                    <?= e($nm) ?>
                  </a>
                </li>
              <?php endforeach; ?>
            <?php else: ?>
              <!-- Fallback jika belum ada data -->
              <li><a class="dropdown-item" href="<?= $baseURL ?>/pages/public/course.php?course=Mathematics">Mathematics</a></li>
              <li><a class="dropdown-item" href="<?= $baseURL ?>/pages/public/course.php?course=Indonesian">Indonesian</a></li>
              <li><a class="dropdown-item" href="<?= $baseURL ?>/pages/public/course.php?course=English">English</a></li>
              <li><a class="dropdown-item" href="<?= $baseURL ?>/pages/public/course.php?course=Biology">Biology</a></li>
              <li><a class="dropdown-item" href="<?= $baseURL ?>/pages/public/course.php?course=Physics">Physics</a></li>
              <li><a class="dropdown-item" href="<?= $baseURL ?>/pages/public/course.php?course=History">History</a></li>
            <?php endif; ?>
          </ul>
        </li>

        <li class="nav-item"><a class="nav-link" href="<?= $baseURL ?>/pages/public/about.php">About Us</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $baseURL ?>/pages/public/contact.php">Contact</a></li>

        <?php if (!empty($_SESSION['user_id'])): ?>
          <?php
            $uid = (int)$_SESSION['user_id'];
            $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama FROM users WHERE id=$uid"));
          ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
               data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fa fa-user"></i> <?= e($u['nama'] ?? 'User') ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
              <li><a class="dropdown-item" href="<?= $baseURL ?>/pages/user/profile.php">Profile</a></li>
              <li><a class="dropdown-item" href="<?= $baseURL ?>/pages/auth/logout.php">Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= $baseURL ?>/pages/auth/login.php">Login</a></li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>
