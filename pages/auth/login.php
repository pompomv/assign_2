<?php
require_once("../../config/app.php");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login - SkillXpert</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= $baseURL ?>/assets/css/style.css">

</head>
<body>
    <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-sm-10 col-md-8 col-lg-5">
        <div class="auth-card">
          <div class="auth-head">
            <h3>Login</h3>
          </div>

          <div class="p-4 p-md-5">

            <?php if (!empty($_SESSION['flash_success'])): ?>
            <div id="flashAlert" class="alert alert-success mb-4" role="alert">
                <?= htmlspecialchars($_SESSION['flash_success']) ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (!empty($_GET['error'])): ?>
            <div id="errorAlert" class="alert alert-danger mb-4" role="alert">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
            <?php endif; ?>

            <form method="post" action="<?= url('pages/auth/login_process.php') ?>" novalidate>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" required autocomplete="username" autofocus>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required autocomplete="current-password">
              </div>
              <button type="submit" class="btn btn-primary w-100">Log in</button>
              <div class="text-center mt-3">
                <span class="text-muted">Don’t have an account?</span>
                <a href="<?= url('pages/auth/register.php') ?>" class="muted-link ms-1">Register here</a>
            </div>
            </form>

            <div class="text-center mt-4">
              <a href="<?= url('pages/public/') ?>" class="muted-link">← Back to Home</a>
            </div>
          </div>
        </div>
        <p class="text-center tiny-foot mt-3 mb-0">&copy; <?= date('Y') ?> SkillXpert</p>
      </div>
    </div>
  </div>
</body>
</html>
