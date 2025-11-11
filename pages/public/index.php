<?php
  include("../../config/app.php");

$slides = [];
$q = "SELECT image_url FROM carousel_slides WHERE is_active = 1 ORDER BY id ASC";
$r = mysqli_query($conn, $q);
while ($row = mysqli_fetch_assoc($r)) $slides[] = $row;


if (!function_exists('e')) {
  function safe($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

$defaults = [
  ['h2'=>'SkillXpert Online Courses', 'p'=>'Improve your skills with high-quality online courses.'],
  ['h2'=>'Professional Instructor',    'p'=>'Guided by experienced instructors who are experts in their fields.'],
  ['h2'=>'Interactive Materials',         'p'=>'Learning is more enjoyable with interactive learning methods.'],
  ['h2'=>'Assignments & Interactive Quizzes',   'p'=>'Test your understanding with interactive assignments and quizzes.'],
  ['h2'=>'Lifetime Access to Materials', 'p'=>'Get lifetime access to course materials and study anytime.'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SkillXpert Kursus Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= $baseURL ?>/assets/css/style.css">
</head>
<body>
<?php include INC_PATH . '/navbar_pub.php'; ?>
    <div style="height:72px"></div>

    <header id="carouselHeader" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
  <!-- Indicators -->
  <div class="carousel-indicators">
    <?php if ($slides): ?>
      <?php foreach ($slides as $i => $_): ?>
        <button type="button"
                data-bs-target="#carouselHeader"
                data-bs-slide-to="<?= $i ?>"
                class="<?= $i===0 ? 'active' : '' ?>"
                aria-current="<?= $i===0 ? 'true' : 'false' ?>"
                aria-label="Slide <?= $i+1 ?>"></button>
      <?php endforeach; ?>
    <?php else: ?>
      <button type="button" data-bs-target="#carouselHeader" data-bs-slide-to="0" class="active"
              aria-current="true" aria-label="Slide 1"></button>
    <?php endif; ?>
  </div>

  <div class="carousel-inner">
    <?php if ($slides): ?>
      <?php foreach ($slides as $i => $s): 
        $cap = $defaults[$i % count($defaults)];
      ?>
        <div class="carousel-item <?= $i===0 ? 'active' : '' ?>">
          <img src="<?= e($s['image_url']) ?>" class="d-block w-100" alt="<?= e($cap['h2']) ?>">
          <div class="carousel-caption d-none d-md-block">
            <h2><?= e($cap['h2']) ?></h2>
            <p><?= e($cap['p']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="carousel-item active">
        <img src="<?= $baseURL ?>/assets/img/bg.png" class="d-block w-100" alt="SkillXpert">
        <div class="carousel-caption d-none d-md-block">
          <h2><?= e($defaults[0]['h2']) ?></h2>
          <p><?= e($defaults[0]['p']) ?></p>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <button class="carousel-control-prev" type="button" data-bs-target="#carouselHeader" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carouselHeader" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
</header>

    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2>Welcome to SkillXpert Online Courses</h2>
                <p><em>SkillXpert Online Courses is a flexible, high-quality online learning platform that offers a wide range of educational programs.</em></p>
                <p><em>SkillXpert Online Courses is a comprehensive online learning platform designed to provide high-quality education to students and professionals around the world.</em></p>

                <div class="row gy-2 gx-4 mb-4">
                    <div class="col-sm-6"><p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Skilled Instructors</p></div>
                    <div class="col-sm-6"><p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Online Classes</p></div>
                    <div class="col-sm-6"><p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>International Certificate</p></div>
                    <div class="col-sm-6"><p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Skilled Instructors</p></div>
                    <div class="col-sm-6"><p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Online Classes</p></div>
                    <div class="col-sm-6"><p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>International Certificate</p></div>
                </div>

                <a href="<?= $baseURL ?>/pages/public/about.php" class="btn btn-success">Read More →</a>
            </div>
            <div class="col-md-6 text-center">
                <img src="<?= $baseURL ?>/assets/img/S2.png" alt="Tentang Kami" class="img-fluid rounded">
            </div>
        </div>
    </div>

    <div class="text-center my-4">
        <h6 class="section-subtitle"><span>Category</span></h6>
        <h2 class="section-title">Course Material</h2>
    </div>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card course-card" style="background-image: url('<?= $baseURL ?>/assets/img/math.jpg');">
                    <div class="card-body">
                        <h5 class="card-title">Matematika</h5>
                        <p class="card-text">Learn mathematical concepts easily and enjoyably.</p>
                        <a href="<?= $baseURL ?>/pages/public/course.php?course=Mathematics" class="btn btn-success">Learn</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card course-card" style="background-image: url('<?= $baseURL ?>/assets/img/indo.png');">
                    <div class="card-body">
                        <h5 class="card-title">Indonesian</h5>
                        <p class="card-text">Improve your Indonesian language skills effectively.</p>
                        <a href="<?= $baseURL ?>/pages/public/course.php?course=Indonesian" class="btn btn-success">Learn</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card course-card" style="background-image: url('<?= $baseURL ?>/assets/img/english.jpg');">
                    <div class="card-body">
                        <h5 class="card-title">English</h5>
                        <p class="card-text">Learn the international language of English from the basics to proficiency.</p>
                        <a href="<?= $baseURL ?>/pages/public/course.php?course=English" class="btn btn-success">Learn</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card course-card" style="background-image: url('<?= $baseURL ?>/assets/img/biology.jpg');">
                    <div class="card-body">
                        <h5 class="card-title">Biology</h5>
                        <p class="card-text">Explore the world of biology with a deep understanding.</p>
                        <a href="<?= $baseURL ?>/pages/public/course.php?course=Biology" class="btn btn-success">Learn</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- typo diperbaiki: pyhsics.jpg -> physics.jpg -->
                <div class="card course-card" style="background-image: url('<?= $baseURL ?>/assets/img/pyhsics.jpg');">
                    <div class="card-body">
                        <h5 class="card-title">Physics</h5>
                        <p class="card-text">Learn the laws of physics in depth with an easy-to-understand approach. </p>
                        <a href="<?= $baseURL ?>/pages/public/course.php?course=Pyhsics" class="btn btn-success">Learn</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card course-card" style="background-image: url('<?= $baseURL ?>/assets/img/history.jpg');">
                    <div class="card-body">
                        <h5 class="card-title">Indonesian History</h5>
                        <p class="card-text">Understand world and Indonesian history from a new perspective.</p>
                        <a href="<?= $baseURL ?>/pages/public/course.php?course=History" class="btn btn-success">Learn</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="py-5" style="background:#eaf7e8;">
        <div class="text-center py-3 bg-s text-black"><!-- bg-liht -> bg-light -->
            &copy; Copyright <strong>SkillXpert</strong> All Rights Reserved
        </div>
    </footer>

    <!-- Vendor JS + App JS (di akhir body) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="<?= $baseURL ?>/assets/js/script.js"></script>
</body>
</html>
