<?php
require_once("../../../config/app.php");
require_once INC_PATH . '/require_login.php';
if (($_SESSION['role'] ?? '') !== 'admin') redirect_to('pages/auth/login.php?error=' . rawurlencode('Admin only'));

if (!function_exists('e')) {
  function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

/** === Helpers (wajib ada di file ini) === */
if (!function_exists('admin_table_url')) {
  function admin_table_url(string $t): string {
    switch ($t) {
      case 'carousel_slides': return url('pages/admin/carousel_table.php');
      case 'contacts':        return url('pages/admin/contact_table.php');
      case 'courses':         return url('pages/admin/courses_table.php');
      case 'enrollments':     return url('pages/admin/enrollments_table.php');
      default:                return url('pages/admin/index2.php');
    }
  }
}
if (!function_exists('redirect_crud_success')) {
  function redirect_crud_success(string $t, string $msg): void {
    $to = admin_table_url($t) . '?msg=' . rawurlencode($msg);
    header("Location: $to"); exit;
  }
}

/** ===== SCHEMA per tabel ===== */
$SCHEMA = [
  'carousel_slides'=>[
    'label'=>'Carousels','pk'=>'id',
    'fields'=>[
      ['name'=>'title','label'=>'Title','type'=>'text','required'=>true],
      ['name'=>'is_active','label'=>'Active?','type'=>'bool'],
      // image_url via upload
    ],
    'uploads'=>[
      'image_file' => ['carousels','image_url','required'=>false,'label'=>'Image']
    ]
  ],
  'contacts'=>[
    'label'=>'Contacts','pk'=>'id',
    'fields'=>[
      ['name'=>'name','label'=>'Name','type'=>'text','required'=>true],
      ['name'=>'email','label'=>'Email','type'=>'email','required'=>true],
      ['name'=>'subject','label'=>'Subject','type'=>'text'],
      ['name'=>'message','label'=>'Message','type'=>'textarea','required'=>true],
    ]
  ],
  'courses'=>[
    'label'=>'Courses','pk'=>'id',
    'fields'=>[
      ['name'=>'name','label'=>'Course Name','type'=>'text','required'=>true],
      ['name'=>'category_id','label'=>'Category','type'=>'select:category','required'=>true],
      ['name'=>'short_desc','label'=>'Short Description','type'=>'textarea'],
      ['name'=>'price','label'=>'Price (Rp)','type'=>'number','step'=>'1','min'=>'0'],
      ['name'=>'is_published','label'=>'Published?','type'=>'bool'],
    ],
    'uploads'=>[
      'image_file' => ['courses','image_url','required'=>false,'label'=>'Image (image_url)']
    ]
  ],
  'enrollments'=>[
    'label'=>'Enrollments','pk'=>'id',
    'fields'=>[
      ['name'=>'user_id','label'=>'User','type'=>'select:user','required'=>true],
      ['name'=>'course_id','label'=>'Course','type'=>'select:course','required'=>true],
      ['name'=>'plan_id','label'=>'Plan (optional)','type'=>'select:plan'],
      ['name'=>'price','label'=>'Price (Rp)','type'=>'number','step'=>'1','min'=>'0'],
      ['name'=>'status','label'=>'Status','type'=>'select:status'],
    ]
  ],
];

$table = $_GET['table'] ?? '';
if (!isset($SCHEMA[$table])) die('Invalid table');
$meta   = $SCHEMA[$table];
$pk     = $meta['pk'];
$fields = $meta['fields'];

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die('Missing id');

/** ===== Data dropdown ===== */
$users=$courses=$categories=$plans=[];
if ($table==='enrollments'){
  $uQ=mysqli_query($conn,"SELECT id,nama FROM users ORDER BY nama");
  while($u=mysqli_fetch_assoc($uQ)) $users[]=$u;

  $cQ=mysqli_query($conn,"SELECT id, COALESCE(name,name) AS name FROM courses ORDER BY name");
  while($c=mysqli_fetch_assoc($cQ)) $courses[]=$c;

  $pQ=mysqli_query($conn,"SELECT id,name,price FROM categories WHERE is_active=1 ORDER BY sort_order ASC, name ASC");
  while($p=mysqli_fetch_assoc($pQ)) $plans[]=$p;
}
if ($table==='courses'){
  $gQ=mysqli_query($conn,"SELECT id,name,price FROM categories WHERE is_active=1 ORDER BY sort_order ASC, name ASC");
  while($g=mysqli_fetch_assoc($gQ)) $categories[]=$g;
}

/** ===== Data saat ini ===== */
$curRes = mysqli_query($conn,"SELECT * FROM `$table` WHERE `$pk`=$id");
$cur = mysqli_fetch_assoc($curRes);
if (!$cur) die('Data not found');

$error = '';

/** ===== PROSES UPDATE ===== */
if ($_SERVER['REQUEST_METHOD']==='POST') {

  $sets=[]; $vals=[]; $types='';

  foreach($fields as $f){
    $n=$f['name']; $t=$f['type'];

    // Khusus enrollments.plan_id: kalau kosong → set NULL (tanpa bind)
    if ($table==='enrollments' && $n==='plan_id') {
      $planRaw = $_POST['plan_id'] ?? '';
      if ($planRaw === '' || $planRaw === null) {
        $sets[]="`plan_id`=NULL";
        continue; // skip binding
      } else {
        $val = (int)$planRaw; $types.='i';
        $sets[]="`plan_id`=?"; $vals[]=$val;
        continue;
      }
    }

    if ($t==='bool'){
      $val = isset($_POST[$n]) ? 1 : 0; $types.='i';
    } elseif ($t==='number'){
      $val = (int)($_POST[$n] ?? 0); $types.='i';
    } elseif ($t==='select:user' || $t==='select:course' || $t==='select:category'){
      $val = (int)($_POST[$n] ?? 0); $types.='i';
    } elseif ($t==='select:status'){
      $allowed = ['pending','active','cancelled'];
      $raw = strtolower(trim($_POST[$n] ?? 'pending'));
      $val = in_array($raw,$allowed,true) ? $raw : 'pending'; $types.='s';
    } elseif ($t==='select:plan'){
      // handled above (plan_id)
      continue;
    } else {
      $val = trim($_POST[$n] ?? ''); $val = ($val==='')?null:$val; $types.='s';
    }

    $sets[]="`$n`=?"; $vals[]=$val;
  }

  /** ===== Upload (generic berdasar schema['uploads']) ===== */
  if (empty($error) && !empty($meta['uploads'])){
    foreach ($meta['uploads'] as $inputName => $cfg){
      $subdir = $cfg[0];           // ex: 'courses'
      $col    = $cfg[1];           // ex: 'image_url'
      $isReq  = $cfg['required'] ?? false;

      $hasFile = !empty($_FILES[$inputName]['name']);
      if (!$hasFile && $isReq){
        $error = 'Please choose a file for '.$inputName;
        break;
      }
      if ($hasFile){
        $f = $_FILES[$inputName];
        if ($f['error'] !== UPLOAD_ERR_OK) { $error='Upload error'; break; }
        if ($f['size'] > 2*1024*1024) { $error='File too large (max 2MB)'; break; }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $f['tmp_name']);
        finfo_close($finfo);
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
        if (!isset($allowed[$mime])) { $error='Invalid image type'; break; }

        // simpan ke /assets/uploads/{subdir}/
        $projectRoot = dirname(__DIR__, 3); // naik 3 level dari file ini
        $uploadDir   = $projectRoot . '/assets/uploads/' . $subdir;
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);

        $ext   = $allowed[$mime];
        $fname = $subdir . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest  = $uploadDir . '/' . $fname;

        if (!move_uploaded_file($f['tmp_name'], $dest)) {
          $error='Failed to save uploaded file'; break;
        }

        // hapus file lama bila berasal dari folder yang sama
        if (!empty($cur[$col])) {
          $oldPath = $projectRoot . '/' . ltrim($cur[$col], '/');
          $folder  = $projectRoot . '/assets/uploads/' . $subdir . '/';
          if (is_file($oldPath) && strpos(realpath($oldPath), realpath($folder))===0) {
            @unlink($oldPath);
          }
        }

        // simpan URL relatif (biar konsisten dengan tampilan lain)
        $relativeUrl = '/assets/uploads/' . $subdir . '/' . $fname;
        $sets[]="`$col`=?"; $vals[]=$relativeUrl; $types.='s';
      }
    }
  }

  /** ===== Sinkron tambahan khusus courses ===== */
  if ($table==='courses' && empty($error)){
    $postedCatId = (int)($_POST['category_id'] ?? 0);
    if ($postedCatId > 0){
      $rs = mysqli_query($conn, "SELECT name, price FROM categories WHERE id=$postedCatId LIMIT 1");
      if ($rs && $row = mysqli_fetch_assoc($rs)){
        // set kolom 'category' (string) agar konsisten
        $sets[] = '`category`=?'; $vals[]=$row['name']; $types.='s';

        // jika price kosong atau 0, pakai harga kategori
        $postedPrice = (int)($_POST['price'] ?? 0);
        if ($postedPrice <= 0){
          // override nilai price yang sudah masuk di $vals (cari indeksnya)
          foreach ($sets as $i=>$s) {
            if ($s==='`price`=?') { $vals[$i]=(int)$row['price']; break; }
          }
        }
      }
    }
  }

  if ($error===''){
    $sql="UPDATE `$table` SET ".implode(',',$sets)." WHERE `$pk`=?";
    $vals[]=$id; $types.='i';

    $stmt=mysqli_prepare($conn,$sql);
    if (!$stmt){ $error='Prepare failed: '.e(mysqli_error($conn)); }
    else{
      mysqli_stmt_bind_param($stmt,$types,...$vals);
      if (!mysqli_stmt_execute($stmt)) $error='DB error: '.e(mysqli_error($conn));
      else redirect_crud_success($table, 'Updated successfully');
      mysqli_stmt_close($stmt);
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit <?= e($meta['label']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= url('/assets/css/style.css') ?>">
  <style>
    .img-preview{max-height:140px;border:1px solid #e5e7eb;border-radius:8px;margin-top:8px}
  </style>
</head>
<body class="bg-light">
<?php include INC_PATH . '/navbar.php'; ?>

<div class="main-content p-4">
  <h3 class="mb-3">Edit <?= e($meta['label']) ?></h3>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm">
    <input type="hidden" name="table" value="<?= e($table) ?>">

    <?php foreach($fields as $f):
      $n=$f['name']; $l=$f['label']; $t=$f['type']; $req=!empty($f['required']); $val=$cur[$n]??''; ?>

      <div class="mb-3">
        <label class="form-label" for="<?= e($n) ?>"><?= e($l) ?></label>

        <?php if ($t==='textarea'): ?>
          <textarea id="<?= e($n) ?>" name="<?= e($n) ?>" class="form-control" rows="4" <?= $req?'required':'' ?>><?= e($val) ?></textarea>

        <?php elseif ($t==='bool'): ?>
          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="<?= e($n) ?>" name="<?= e($n) ?>" <?= $val? 'checked':'' ?>>
            <label class="form-check-label" for="<?= e($n) ?>">Yes</label>
          </div>

        <?php elseif ($t==='select:user'): ?>
          <select id="<?= e($n) ?>" name="<?= e($n) ?>" class="form-select" <?= $req?'required':'' ?>>
            <option value="">-- Choose User --</option>
            <?php foreach($users as $u): ?>
              <option value="<?= (int)$u['id'] ?>" <?= ((int)$val===(int)$u['id'])?'selected':'' ?>><?= e($u['nama']) ?></option>
            <?php endforeach; ?>
          </select>

        <?php elseif ($t==='select:course'): ?>
          <select id="<?= e($n) ?>" name="<?= e($n) ?>" class="form-select" <?= $req?'required':'' ?>>
            <option value="">-- Choose Course --</option>
            <?php foreach($courses as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= ((int)$val===(int)$c['id'])?'selected':'' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>

        <?php elseif ($t==='select:category'): ?>
          <select id="category_id" name="<?= e($n) ?>" class="form-select" <?= $req?'required':'' ?>>
            <option value="">-- Choose Category --</option>
            <?php foreach($categories as $cat): ?>
              <option value="<?= (int)$cat['id'] ?>"
                      data-price="<?= (int)$cat['price'] ?>"
                      <?= ((int)$val===(int)$cat['id'])?'selected':'' ?>>
                <?= e($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>

        <?php elseif ($t==='select:plan'): ?>
          <select id="<?= e($n) ?>" name="<?= e($n) ?>" class="form-select">
            <option value="">-- No Plan --</option>
            <?php foreach($plans as $p): ?>
              <option value="<?= (int)$p['id'] ?>" <?= ((int)$val===(int)$p['id'])?'selected':'' ?>>
                <?= e($p['name']) ?> <?= $p['price']!==null ? (' • Rp'.number_format((int)$p['price'],0,',','.')) : '' ?>
              </option>
            <?php endforeach; ?>
          </select>

        <?php elseif ($t==='select:status'): ?>
          <?php $opts = ['pending'=>'Pending','active'=>'Active','cancelled'=>'Cancelled']; ?>
          <select id="<?= e($n) ?>" name="<?= e($n) ?>" class="form-select">
            <?php foreach($opts as $k=>$v): ?>
              <option value="<?= $k ?>" <?= ($val===$k?'selected':'') ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>

        <?php else:
          $attrs=''; foreach(['step','min','max','placeholder'] as $k){ if(isset($f[$k])) $attrs.=" $k='{$f[$k]}'"; }
          $idAttr = ($n==='price') ? 'id="price"' : ''; ?>
          <input <?= $idAttr ?> type="<?= e($t) ?>" name="<?= e($n) ?>" class="form-control" value="<?= e($val) ?>" <?= $req?'required':'' ?> <?= $attrs ?>>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

    <?php if (!empty($meta['uploads'])): ?>
      <?php foreach($meta['uploads'] as $inputName => $cfg):
          $col = $cfg[1]; $labelText = $cfg['label'] ?? 'Image'; $curUrl = $cur[$col] ?? null;
          $inputId = e($inputName); $imgPrevId = e($inputName.'_prev'); ?>
        <div class="mb-3">
          <label class="form-label" for="<?= $inputId ?>"><?= e($labelText) ?></label>
          <?php if ($curUrl): ?>
            <div class="mb-2">
              <a href="<?= e($curUrl) ?>" target="_blank" rel="noopener"><?= e($curUrl) ?></a><br>
              <img src="<?= e($curUrl) ?>" alt="current image" class="img-preview">
            </div>
          <?php endif; ?>
          <input type="file" id="<?= $inputId ?>" name="<?= $inputName ?>" class="form-control" accept="image/*" onchange="previewImg(this,'<?= $imgPrevId ?>')">
          <img id="<?= $imgPrevId ?>" class="img-preview" style="display:none" alt="Preview">
          <div class="form-text">Leave empty to keep current image.</div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <button class="btn btn-success">Update</button>
    <a class="btn btn-secondary ms-2" href="<?= admin_table_url($table) ?>">Cancel</a>
  </form>
</div>

<script>
function previewImg(input,id){
  const f=input.files&&input.files[0];
  const img=document.getElementById(id);
  if(!img) return;
  if(!f){img.style.display='none';img.src='';return;}
  img.src=URL.createObjectURL(f);
  img.style.display='block';
}

// Auto-fill price dari category (khusus courses)
document.addEventListener('DOMContentLoaded', function(){
  const selCat = document.getElementById('category_id');
  const price  = document.getElementById('price');
  if (!selCat || !price) return;

  function fillPrice(){
    const opt = selCat.options[selCat.selectedIndex];
    if (!opt) return;
    const p = opt.getAttribute('data-price');
    if (p !== null && p !== '' && !isNaN(p)) price.value = p;
  }

  selCat.addEventListener('change', fillPrice);
  if ((price.value === '' || Number(price.value) <= 0) && selCat.value) fillPrice();
});
</script>
</body>
</html>
