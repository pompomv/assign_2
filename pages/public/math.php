<?php
  include("../../config/app.php"); // $baseURL, $conn
  if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kursus Matematika • SkillXpert</title>

  <!-- Vendor CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

  <!-- (Opsional) App CSS global kamu -->
  <link rel="stylesheet" href="<?= $baseURL ?>/assets/css/style.css">

  <style>
    body { background-color: #e8f5e9; }
    .hero{
      /* pakai baseURL agar path selalu benar */
      background: url('<?= $baseURL ?>/assets/img/math.png') center/cover no-repeat;
      color:#fff; text-align:center; padding:100px 20px; animation:fadeIn 2s;
    }
    .pricing-card{ transition:transform .3s; }
    .pricing-card:hover{ transform:scale(1.05); }
    @keyframes fadeIn { from{opacity:0} to{opacity:1} }
  </style>
</head>
<body>

  <!-- NAVBAR (samakan dengan index: fixed-top + warna terlihat) -->
  <nav class="navbar navbar-expand-lg fixed-top" style="background-color:#8f998e;">
    <div class="container">
      <a class="navbar-brand" href="<?= $baseURL ?>/pages/public/index.php">
        <img src="<?= $baseURL ?>/assets/img/S.png" alt="SkillXpert Logo" height="40"> SkillXpert
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">

          <li class="nav-item"><a class="nav-link" href="<?= $baseURL ?>/index.php">Home</a></li>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="kursusDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Kursus</a>
            <ul class="dropdown-menu" aria-labelledby="kursusDropdown">
              <li><a class="dropdown-item active" href="<?= $baseURL ?>/pages/public/math.php">Mathematics</a></li>
              <li><a class="dropdown-item" href="<?= $baseURL ?>/pages/public/indo.php">Indonesian</a></li>
              <li><a class="dropdown-item" href="<?= $baseURL ?>/pages/public/english.php">English</a></li>
              <li><a class="dropdown-item" href="<?= $baseURL ?>/pages/public/biology.php">Biology</a></li>
              <li><a class="dropdown-item" href="<?= $baseURL ?>/pages/public/physics.php">Physics</a></li>
              <li><a class="dropdown-item" href="<?= $baseURL ?>/pages/public/history.php">History</a></li>
            </ul>
          </li>

          <li class="nav-item"><a class="nav-link" href="<?= $baseURL ?>/pages/public/about.php">About Us</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= $baseURL ?>/pages/public/contact.php">Contact</a></li>

          <?php if (isset($_SESSION['user_id'])): ?>
            <?php
              // mengikuti index: query sederhana (nanti bisa diganti prepared)
              $user_id = $_SESSION['user_id'];
              $query = mysqli_query($conn, "SELECT nama FROM users WHERE id = '$user_id'");
              $user  = mysqli_fetch_assoc($query);
            ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-user"></i> <?= htmlspecialchars($user['nama'] ?? 'User') ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="<?= $baseURL ?>/pages/user/profile.php">Profile</a></li>
                <li><a class="dropdown-item" href="<?= $baseURL ?>/pages/auth/logout.php">Logout</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= $baseURL ?>/pages/auth/login.php">Login</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- spacer utk fixed-top -->
  <div style="height:72px"></div>

  <!-- HERO -->
  <div class="hero">
    <h1>Kursus Matematika</h1>
    <p>Pelajari konsep matematika dengan mudah dan menyenangkan!</p>
  </div>

  <!-- INTRO -->
  <div class="container mt-5">
    <h2 class="text-center">Mengapa Belajar Matematika Itu Penting?</h2>
    <p class="text-center">
      Matematika adalah dasar dari banyak bidang ilmu dan memiliki peran penting dalam kehidupan sehari-hari.
      Dari menghitung pengeluaran harian hingga memahami konsep teknologi canggih, matematika membantu kita
      berpikir secara logis dan memecahkan masalah dengan lebih efektif. Dengan memahami matematika,
      Anda dapat meningkatkan keterampilan analitis dan membuka peluang lebih luas dalam dunia akademik maupun profesional.
    </p>
    <p class="text-center">
      Di SkillXpert, kami menghadirkan metode pembelajaran interaktif yang dirancang agar mudah dipahami
      dan menyenangkan bagi semua orang. Bergabunglah dengan kursus kami dan mulai perjalanan Anda dalam
      menguasai matematika dengan cara yang lebih menarik dan efektif!
    </p>
  </div>

  <!-- FEATURES -->
  <div class="container mt-5">
    <div class="row text-center">
      <div class="col-md-4">
        <i class="fa-solid fa-calculator fa-3x text-primary"></i>
        <h4>Konsep Mudah</h4>
        <p>Materi dirancang agar mudah dipahami dengan metode interaktif.</p>
      </div>
      <div class="col-md-4">
        <i class="fa-solid fa-chalkboard-user fa-3x text-success"></i>
        <h4>Instruktur Berpengalaman</h4>
        <p>Belajar dari para ahli yang sudah berpengalaman di bidangnya.</p>
      </div>
      <div class="col-md-4">
        <i class="fa-solid fa-book-open fa-3x text-danger"></i>
        <h4>Materi Lengkap</h4>
        <p>Mulai dari dasar hingga tingkat lanjutan, tersedia untuk semua level.</p>
      </div>
    </div>
  </div>

  <!-- PRICING -->
  <div class="container mt-5">
    <h2 class="text-center">Paket Langganan</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card pricing-card h-100">
          <div class="card-body text-center">
            <h3>Pemula</h3>
            <p><i class="fa-solid fa-star text-warning"></i> Dasar-dasar Matematika</p>
            <h4>Rp50.000/bulan</h4>
            <a href="<?= $baseURL ?>/pages/public/contact.php" class="btn btn-primary">Langganan</a>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card pricing-card h-100">
          <div class="card-body text-center">
            <h3>Menengah</h3>
            <p><i class="fa-solid fa-star text-warning"></i> Materi Lanjutan</p>
            <h4>Rp100.000/bulan</h4>
            <a href="<?= $baseURL ?>/pages/public/contact.php" class="btn btn-success">Langganan</a>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card pricing-card h-100">
          <div class="card-body text-center">
            <h3>Profesional</h3>
            <p><i class="fa-solid fa-star text-warning"></i> Semua Materi + Sertifikat</p>
            <h4>Rp200.000/bulan</h4>
            <a href="<?= $baseURL ?>/pages/public/contact.php" class="btn btn-danger">Langganan</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- FOOTER -->
  <footer class="py-5">
    <div class="text-center py-3 text-black">
      &copy; Copyright <strong>SkillXpert</strong> All Rights Reserved
    </div>
  </footer>

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
