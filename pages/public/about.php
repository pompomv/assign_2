<?php
require_once __DIR__ . "/../../config/app.php";
if (session_status() === PHP_SESSION_NONE) session_start();
if (!function_exists('e')) { function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }
$baseURL = isset($baseURL) ? rtrim($baseURL,'/') : (isset($base_url)? rtrim($base_url,'/') : '');
$user_name = null;
if (isset($_SESSION['user_id']) && !empty($conn)) {
  $uid = (int)$_SESSION['user_id'];
  if ($stmt = mysqli_prepare($conn, "SELECT nama FROM users WHERE id=? LIMIT 1")) {
    mysqli_stmt_bind_param($stmt, 'i', $uid);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $colNama);
    if (mysqli_stmt_fetch($stmt)) $user_name = $colNama;
    mysqli_stmt_close($stmt);
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>About • SkillXpert</title>

  <!-- Vendor CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- App CSS -->
  <link rel="stylesheet" href="<?= $baseURL ?>/assets/css/style.css">
</head>
<body>
  <!-- NAVBAR (disamakan dengan index) -->
<?php include INC_PATH . '/navbar_pub.php'; ?>

  <!-- spacer utk fixed-top -->
  <div style="height:72px"></div>

  <!-- ABOUT CONTENT -->
  <section class="py-5" style="background:#eaf7e8;">
    <div class="container">
      <h2 class="text-success mb-4">About SkillXpert</h2>

      <div class="p-4 p-md-5 rounded" style="background:#fff; border:1px solid #e5e7eb;">
        <div class="row g-4 align-items-center">
          <div class="col-lg-5">
            <img src="<?= $baseURL ?>/assets/img/boosk.png" class="img-fluid rounded shadow-sm" alt="Person with Books">
          </div>

          <div class="col-lg-7">
            <h5 class="text-success mb-2">About Us</h5>
            <h3 class="fw-bold mb-3">Welcome to SkillXpert</h3>

            <p class="mb-3">
            SkillXpert Online Courses is a comprehensive online learning platform designed to provide high-quality education to students and professionals around the world.Kursus Online SkillXpert adalah platform pembelajaran online komprehensif yang dirancang untuk memberikan pendidikan berkualitas tinggi kepada siswa dan profesional di seluruh dunia.
            </p>
            <p class="mb-4">
            With a wide range of courses, SkillXpert allows students to improve their knowledge and skills from their own homes. The platform focuses on flexibility, making it easy for everyone to learn at their own pace while gaining valuable insights from expert instructors.
            </p>

            <div class="row gy-2">
              <div class="col-sm-6">
                <p class="mb-1"><i class="fa fa-arrow-right text-success me-2"></i>Skilled Instructors</p>
                <p class="mb-1"><i class="fa fa-arrow-right text-success me-2"></i>International Certificate</p>
                <p class="mb-0"><i class="fa fa-arrow-right text-success me-2"></i>Online Classes</p>
              </div>
              <div class="col-sm-6">
                <p class="mb-1"><i class="fa fa-arrow-right text-success me-2"></i>Online Classes</p>
                <p class="mb-1"><i class="fa fa-arrow-right text-success me-2"></i>Skilled Instructors</p>
                <p class="mb-0"><i class="fa fa-arrow-right text-success me-2"></i>International Certificate</p>
              </div>
            </div>
          </div>
        </div>
      </div><!-- /card -->
    </div>
  </section>

  <footer class="py-5" style="background:#eaf7e8;">
    <div class="text-center py-3 text-black">
      &copy; Copyright <strong>SkillXpert</strong> All Rights Reserved
    </div>
  </footer>

  <!-- Vendor JS + App JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script defer src="<?= $baseURL ?>/assets/js/script.js"></script>
</body>
</html>