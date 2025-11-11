<?php
// pages/user/profile.php
require_once("../../config/app.php");
require_once INC_PATH . '/require_login.php';

if (!function_exists('e')) {
  function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
if ($user_id <= 0) {
  header("Location: " . url('pages/auth/login.php'));
  exit;
}

/* ------------------ Ambil data user ------------------ */
// Ambil 2 kemungkinan kolom hash (password_hash / password)
$stmt = mysqli_prepare(
  $conn,
  "SELECT id, nama, email, password_hash
     FROM users
    WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$u = mysqli_fetch_assoc($res) ?: [];
mysqli_stmt_close($stmt);

// Pastikan key exist agar tidak notice
$u['nama']          = $u['nama']          ?? '';
$u['email']         = $u['email']         ?? '';
$u['password_hash'] = $u['password_hash'] ?? '';
$u['password']      = $u['password']      ?? '';

// Tentukan hash tersimpan secara robust
$storedHash = '';
if (trim($u['password_hash']) !== '') {
  $storedHash = trim($u['password_hash']);
} elseif (trim($u['password']) !== '') {
  $storedHash = trim($u['password']);
}

// TRUE jika TIDAK ada hash tersimpan (belum punya password)
$noPasswordYet = ($storedHash === '');

$success = $_GET['success'] ?? '';
$error   = $_GET['error'] ?? '';

/* ------------------ Proses POST ------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  // 1) Ubah nama & email
  if ($action === 'profile') {
    $nama  = trim($_POST['nama']  ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($nama === '' || $email === '') {
      $error = 'Nama dan Email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      // @example.com valid, jadi kalau kosong tidak lolos, tapi domain apa pun boleh
      $error = 'Format email tidak valid.';
    } else {
      // Cek email unik (boleh sama dengan milik sendiri)
      $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id <> ?");
      mysqli_stmt_bind_param($stmt, 'si', $email, $user_id);
      mysqli_stmt_execute($stmt);
      $dupe = mysqli_stmt_get_result($stmt);
      $exists = mysqli_fetch_assoc($dupe);
      mysqli_stmt_close($stmt);

      if ($exists) {
        $error = 'Email sudah digunakan akun lain.';
      } else {
        $stmt = mysqli_prepare($conn, "UPDATE users SET nama = ?, email = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ssi', $nama, $email, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // refresh data lokal
        $u['nama']  = $nama;
        $u['email'] = $email;

        $success = 'Profil berhasil diperbarui.';
      }
    }
  }

  // 2) Ganti / Set password
  if ($action === 'password') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($new === '' || $confirm === '') {
      $error = 'Password baru dan konfirmasi wajib diisi.';
    } elseif ($new !== $confirm) {
      $error = 'Konfirmasi password baru tidak sama.';
    } elseif (strlen($new) < 8) {
      $error = 'Password baru minimal 8 karakter.';
    } else {
      // Jika sudah punya password, verifikasi current
      if (!$noPasswordYet && !password_verify($current, $storedHash)) {
        $error = 'Password saat ini salah.';
      } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);

        // Simpan ke kolom yang kamu gunakan. Default: password_hash
        $stmt = mysqli_prepare($conn, "UPDATE users SET password_hash = ? WHERE id = ?");
        // Kalau di DB kamu hanya ada kolom `password`, ganti query di atas jadi:
        // $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");

        mysqli_stmt_bind_param($stmt, 'si', $newHash, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $success = $noPasswordYet ? 'Password berhasil diset.' : 'Password berhasil diganti.';
        $storedHash   = $newHash;
        $noPasswordYet = false;
      }
    }
  }

  // PRG: redirect supaya tidak resubmit saat refresh
  $qs = [];
  if ($success) $qs['success'] = $success;
  if ($error)   $qs['error']   = $error;
  $to = url('pages/user/profile.php') . ($qs ? ('?' . http_build_query($qs)) : '');
  header("Location: $to");
  exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Profil Saya • SkillXpert</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Vendor -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <!-- App -->
  <link rel="stylesheet" href="<?= $baseURL ?>/assets/css/style.css">
  <style>
    body{ background:#eaf7e8; }
    .profile-card{
      background:#fff; border:1px solid #e5e7eb; border-radius:14px; padding:20px;
      box-shadow:0 10px 25px rgba(46,125,50,.12);
    }
  </style>
</head>
<body>

<?php include INC_PATH . '/navbar_pub.php'; ?>
<div style="height:72px"></div>

<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">

      <h2 class="mb-3">Profil Saya</h2>

      <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
      <?php endif; ?>
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
      <?php endif; ?>

      <!-- Ubah nama & email -->
      <div class="profile-card mb-4">
        <h5 class="mb-3"><i class="fa-regular fa-id-card me-2"></i>Ubah Nama & Email</h5>
        <form method="post" class="row g-3">
          <input type="hidden" name="action" value="profile">
          <div class="col-12">
            <label class="form-label">Nama</label>
            <input type="text" name="nama" class="form-control" value="<?= e($u['nama']) ?>" required>
          </div>
          <div class="col-12">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= e($u['email'] ?? '') ?>" required>
            <div class="form-text">Email apa pun valid (contoh: user@example.com).</div>
          </div>
          <div class="col-12">
            <button class="btn btn-success"><i class="fa-solid fa-floppy-disk me-1"></i>Simpan Perubahan</button>
          </div>
        </form>
      </div>

      <!-- Ganti / Set Password -->
      <div class="profile-card">
        <h5 class="mb-3"><i class="fa-solid fa-key me-2"></i>Ganti Password</h5>

        <?php if ($noPasswordYet): ?>
          <div class="alert alert-info">
            Akun ini belum memiliki password. Silakan set password baru di bawah ini.
          </div>
        <?php else: ?>
          <div class="mb-2 text-muted">Masukkan password saat ini untuk verifikasi.</div>
        <?php endif; ?>

        <form method="post" class="row g-3">
          <input type="hidden" name="action" value="password">

          <?php if (!$noPasswordYet): ?>
            <div class="col-12">
              <label class="form-label">Password Saat Ini</label>
              <input type="password" name="current_password" class="form-control" required>
            </div>
          <?php endif; ?>

          <div class="col-md-6">
            <label class="form-label">Password Baru</label>
            <input type="password" name="new_password" class="form-control" minlength="8" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Konfirmasi Password Baru</label>
            <input type="password" name="confirm_password" class="form-control" minlength="8" required>
          </div>
          <div class="col-12">
            <button class="btn btn-primary">
              <i class="fa-solid fa-rotate me-1"></i><?= $noPasswordYet ? 'Set Password' : 'Ganti Password' ?>
            </button>
          </div>
        </form>
      </div>

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
