<?php
// pastikan baseURL, url(), asset(), e() tersedia
require_once __DIR__ . '/../../config/app.php';
if (!function_exists('e')) { function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }

$displayName = $_SESSION['user_name_display']
  ?? (($_SESSION['role'] ?? '')==='admin' ? 'Admin' : ($_SESSION['user_name'] ?? 'User'));
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="<?= asset('/css/style.css') ?>">

<!-- ===== SIDEBAR ===== -->
<div class="sidebar">
  <div class="sidebar-header">
    <i class="fa-solid fa-book-open-reader"></i>
    <span>Courseboard</span>
  </div>

  <ul>
    <li>
      <a href="<?= url('pages/admin/index2.php') ?>">
        <i class="fa-solid fa-house"></i><span>Home</span>
      </a>
    </li>
  </ul>

  <div class="menu-header" onclick="toggleMenu(this,'tablesMenu')">
    <span><i class="fa-solid fa-table"></i> <span>Tables</span></span>
    <span class="toggle-icon fa-solid fa-chevron-down"></span>
  </div>
  <ul id="tablesMenu" class="menu-content">
    <li><a href="<?= url('pages/admin/carousel_table.php') ?>"><i class="fa-solid fa-images"></i><span>Carousel Slides</span></a></li>
    <li><a href="<?= url('pages/admin/contact_table.php') ?>"><i class="fa-solid fa-envelope"></i><span>Contact Messages</span></a></li>
    <li><a href="<?= url('pages/admin/courses_table.php') ?>"><i class="fa-solid fa-book"></i><span>Courses</span></a></li>
    <li><a href="<?= url('pages/admin/enrollments_table.php') ?>"><i class="fa-solid fa-graduation-cap"></i><span>Enrollments</span></a></li>
  </ul>
</div>

<!-- ===== TOPBAR (fixed, gelap, nempel kanan sidebar) ===== -->
<div class="admin-topbar">
  <div class="tb-left">
  </div>

  <div class="tb-right">
    <div class="user-menu" id="userMenu">
      <span class="user-name"><?= e($displayName) ?></span>
      <i class="fa-solid fa-chevron-down chevron"></i>

      <div class="user-dropdown" id="userDropdown">
        <a href="<?= url('pages/user/profile.php') ?>"><i class="fa-regular fa-user"></i> Profile</a>
        <div class="divider"></div>
        <a href="<?= url('pages/auth/logout.php') ?>" class="logout">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
      </div>
    </div>
  </div>
</div>

<script>
  // toggle submenu "Tables"
  function toggleMenu(triggerEl, menuId){
    const menu = document.getElementById(menuId);
    const icon = triggerEl.querySelector('.toggle-icon');
    if (!menu || !icon) return;
    menu.classList.toggle('active');
    icon.classList.toggle('fa-chevron-down', !menu.classList.contains('active'));
    icon.classList.toggle('fa-chevron-up', menu.classList.contains('active'));
  }

  // dropdown profil
  (function(){
    const menu = document.getElementById('userMenu');
    if (!menu) return;
    menu.addEventListener('click', function(e){
      menu.classList.toggle('open');
      e.stopPropagation();
    });
    document.addEventListener('click', function(){ menu.classList.remove('open'); });
  })();
</script>
