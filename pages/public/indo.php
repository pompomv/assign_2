<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kursus Bahasa Indonesia</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body{
            background-color: #e8f5e9;
        }
        .hero {
            background: url('assets/img/indo.png') center/cover no-repeat;
            color: white;
            text-align: center;
            padding: 100px 20px;
            animation: fadeIn 2s;
        }
        .pricing-card {
            transition: transform 0.3s;
        }
        .pricing-card:hover {
            transform: scale(1.05);
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light navbar-transparent fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <img src="assets/img/S.png" alt="SkillXpert Logo" height="40"> SkillXpert
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= $baseURL ?>/pages/public/index.php">Home</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="kursusDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Kursus</a>
                        <ul class="dropdown-menu" aria-labelledby="kursusDropdown">
                            <li><a class="dropdown-item" href="<?= $baseURL ?>/pages/public/math.php">Mathematics</a></li>
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
                        $user_id = $_SESSION['user_id'];
                        $query = mysqli_query($conn, "SELECT nama FROM users WHERE id = '$user_id'");
                        $user = mysqli_fetch_assoc($query);
                    ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-user"></i> <?= htmlspecialchars($user['nama']) ?>
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
    
    <div class="hero">
        <h1>Kursus Bahasa Indonesia</h1>
        <p>Tingkatkan kemampuan berbahasa Indonesia dengan metode yang menyenangkan!</p>
    </div>

    <div class="container mt-5">
        <h2 class="text-center">Mengapa Belajar Bahasa Indonesia Itu Penting?</h2>
        <p class="text-center">
            Bahasa Indonesia adalah bahasa resmi negara dan merupakan alat komunikasi utama dalam berbagai aspek kehidupan. 
            Memahami tata bahasa, ejaan, dan kosakata yang baik akan membantu dalam komunikasi lisan maupun tulisan, 
            baik dalam lingkungan akademik, profesional, maupun sosial.
        </p>
        <p class="text-center">
            Di SkillXpert, kami menawarkan metode pembelajaran yang menyenangkan dan mudah dipahami. 
            Dari dasar hingga tingkat mahir, Anda dapat meningkatkan kemampuan berbahasa dengan materi yang menarik 
            serta bimbingan dari instruktur berpengalaman.
        </p>
    </div>
    
    <div class="container mt-5">
        <div class="row text-center">
            <div class="col-md-4">
                <i class="fa-solid fa-book fa-3x text-primary"></i>
                <h4>Kemampuan Membaca</h4>
                <p>Belajar memahami teks dengan lebih baik melalui teknik membaca yang efektif.</p>
            </div>
            <div class="col-md-4">
                <i class="fa-solid fa-pen fa-3x text-success"></i>
                <h4>Menulis yang Baik</h4>
                <p>Kuasai teknik menulis esai, artikel, dan karya ilmiah dengan baik.</p>
            </div>
            <div class="col-md-4">
                <i class="fa-solid fa-microphone fa-3x text-danger"></i>
                <h4>Komunikasi Lisan</h4>
                <p>Latih keterampilan berbicara agar lebih percaya diri dalam presentasi dan diskusi.</p>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <h2 class="text-center">Paket Langganan</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card pricing-card">
                    <div class="card-body text-center">
                        <h3>Pemula</h3>
                        <p><i class="fa-solid fa-star text-warning"></i> Dasar Tata Bahasa</p>
                        <h4>Rp50.000/bulan</h4>
                        <a href="contact.html" class="btn btn-primary">Langganan</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card pricing-card">
                    <div class="card-body text-center">
                        <h3>Menengah</h3>
                        <p><i class="fa-solid fa-star text-warning"></i> Menulis & Membaca Kritis</p>
                        <h4>Rp100.000/bulan</h4>
                        <a href="contact.html" class="btn btn-success">Langganan</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card pricing-card">
                    <div class="card-body text-center">
                        <h3>Profesional</h3>
                        <p><i class="fa-solid fa-star text-warning"></i> Komunikasi Efektif</p>
                        <h4>Rp200.000/bulan</h4>
                        <a href="contact.html" class="btn btn-danger">Langganan</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="py-5">
        <div class="text-center py-3 text-black">
            &copy; Copyright <strong>SkillXpert</strong> All Rights Reserved
        </div>
    </footer>   
    
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
