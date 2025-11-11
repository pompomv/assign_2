<?php
  require_once("../../config/app.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Create Account - SkillXpert</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= $baseURL ?>/assets/css/style.css">
</head>
<body>
  <div class="center-screen px-3">
    <div class="register-card">
      <h3 class="text-center mb-4 fw-bold">Create Your Account</h3>

      <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger" id="alertBox"><?= htmlspecialchars($_GET['error']) ?></div>
      <?php elseif (!empty($_GET['success'])): ?>
        <div class="alert alert-success" id="alertBox"><?= htmlspecialchars($_GET['success']) ?></div>
      <?php endif; ?>

      <form method="post" action="<?= url('pages/auth/register_process.php') ?>">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-control" name="name" placeholder="Enter your full name" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" class="form-control" name="password" placeholder="Create a password" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <input type="password" class="form-control" name="confirm_password" placeholder="Confirm your password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
      </form>

      <div class="text-center mt-3">
        <p>Already have an account? <a href="<?= url('pages/auth/login.php') ?>" class="muted-link">Login here</a></p>
      </div>
    </div>
  </div>

  <script>
    // Auto-hide any .alert after 5s
    window.addEventListener('DOMContentLoaded', function () {
      const alertEl = document.getElementById('alertBox');
      if (!alertEl) return;
      setTimeout(() => {
        alertEl.style.transition = 'opacity .4s ease';
        alertEl.style.opacity = '0';
        setTimeout(() => alertEl.remove(), 400);
      }, 5000);
    });
  </script>
</body>
</html>
