<?php
// ---- BOOTSTRAP PHP & CONFIG ----
require_once __DIR__ . "/../../config/app.php";
if (session_status() === PHP_SESSION_NONE) session_start();

// Helper aman (pakai helper dari app.php kalau sudah ada)
if (!function_exists('e')) {
  function safe($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
if (!isset($baseURL)) {
  // fallback jika di config namanya $base_url
  $baseURL = isset($base_url) ? rtrim($base_url, '/') : '';
}

// ---- CSRF TOKEN ----
if (empty($_SESSION['csrf_contact'])) {
  $_SESSION['csrf_contact'] = bin2hex(random_bytes(16));
}
$csrfToken = $_SESSION['csrf_contact'];

// ---- HANDLE POST (INSERT) ----
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Cek CSRF
  if (!isset($_POST['_token']) || !hash_equals($_SESSION['csrf_contact'], (string)$_POST['_token'])) {
    $errors[] = 'Invalid request token. Please refresh the page.';
  }

  // Ambil & validasi input
  $name    = trim($_POST['name']    ?? '');
  $email   = trim($_POST['email']   ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $message = trim($_POST['message'] ?? '');

  if ($name === '')   $errors[] = 'Nama wajib diisi.';
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
  if ($message === '') $errors[] = 'Pesan wajib diisi.';
  // subject opsional → boleh kosong

  if (!$errors) {
    // Insert ke contact_messages (id auto, created_at NOW())
    $sql = "INSERT INTO contact_messages (name, email, subject, message, created_at)
            VALUES (?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
      mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $subject, $message);
      if (mysqli_stmt_execute($stmt)) {
        // flash message + redirect (hindari resubmission F5)
        $_SESSION['flash_success'] = 'Pesan berhasil dikirim!';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
      } else {
        $errors[] = 'Gagal menyimpan ke database: ' . e(mysqli_error($conn));
      }
      mysqli_stmt_close($stmt);
    } else {
      $errors[] = 'Gagal menyiapkan query.';
    }
  }
}

// Ambil flash (jika ada)
if (!empty($_SESSION['flash_success'])) {
  $success = true;
  $flashMsg = $_SESSION['flash_success'];
  unset($_SESSION['flash_success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kontak - SkillXpert</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    html, body { height: 100%; margin: 0; }
    .container { flex: 1; }
    footer { background-color: #f8f9fa; text-align: center; padding: 20px; margin-top: auto; }
    .wrapper { display: flex; flex-direction: column; min-height: 100vh; }
  </style>
</head>
<body>
<div class="wrapper">
  <!-- NAVBAR -->
<?php include INC_PATH . '/navbar_pub.php'; ?>
    <div style="height:72px"></div>

  <!-- CONTENT -->
  <div class="container mt-5 pt-5">
    <h2 class="text-center mb-4 text-success">Contact Us</h2>

    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $err): ?>
            <li><?= e($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php elseif ($success && !empty($flashMsg)): ?>
      <div class="alert alert-success" id="crudAlert"><?= e($flashMsg) ?></div>
    <?php endif; ?>

    <div class="row">
      <div class="col-md-6">
        <h4>Alamat Kami</h4>
        <p>Jl. Prof. DR. Ir R Roosseno, Kukusan, Kecamatan Beji, Kota Depok, Jawa Barat 16425</p>

        <!-- Map responsive -->
        <div class="ratio ratio-4x3">
          <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3965.2509767133947!2d106.82133427530327!3d-6.36155476223354!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69ec192c30aa47%3A0x72f29bad0571e98c!2sFaculty%20of%20Engineering%20Universitas%20Indonesia!5e0!3m2!1sen!2sid!4v1742369516626!5m2!1sen!2sid"
              style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

        <h4 class="mt-3">Contact</h4>
        <p><strong>Telphone Number:</strong> +62 812-3456-7890</p>
        <p><strong>Email:</strong> contact@skillxpert.com</p>
      </div>

      <div class="col-md-6">
        <h4>Send Message</h4>
        <form id="contactForm" method="post" action="<?= e($_SERVER['PHP_SELF']) ?>" novalidate>
          <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">
          <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name"
                   value="<?= e($_POST['name'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email"
                   value="<?= e($_POST['email'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label for="subject" class="form-label">Subject (optional)</label>
            <input type="text" class="form-control" id="subject" name="subject"
                   value="<?= e($_POST['subject'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea class="form-control" id="message" name="message" rows="4" required><?= e($_POST['message'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn btn-success">Sent</button>
        </form>
      </div>
    </div>
  </div>

  <footer class="py-4 mt-5">
    <div class="text-center py-3 text-black">
      &copy; Copyright <strong>SkillXpert</strong> All Rights Reserved
    </div>
  </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Optional: auto-hide alert sukses
document.addEventListener("DOMContentLoaded", function () {
  const alertBox = document.getElementById('crudAlert');
  if (alertBox) {
    setTimeout(() => {
      alertBox.classList.add('fade');
      setTimeout(() => alertBox.remove(), 300);
    }, 1600);
  }
});
</script>
</body>
</html>
