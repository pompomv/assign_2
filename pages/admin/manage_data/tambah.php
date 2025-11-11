<?php
require_once("../../../config/app.php");
require_once INC_PATH . '/require_login.php';
if (($_SESSION['role'] ?? '') !== 'admin') redirect_to('pages/auth/login.php?error=' . rawurlencode('Admin only'));

function safe($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }


/* === SCHEMA === */
$SCHEMA = [
  'carousel_slides' => [
    'label' => 'Carousel Slides',
    'fields'=> [
      ['name'=>'title','label'=>'Title','type'=>'text','required'=>true],
      ['name'=>'is_active','label'=>'Active?','type'=>'bool'],
    ],
    'uploads'=>[
      'image_file'=>['carousels','image_url','required'=>true,'label'=>'Image']
    ]
  ],
  'contacts'=>[
    'label'=>'Contacts',
    'fields'=>[
      ['name'=>'name','label'=>'Name','type'=>'text','required'=>true],
      ['name'=>'email','label'=>'Email','type'=>'email','required'=>true],
      ['name'=>'subject','label'=>'Subject','type'=>'text'],
      ['name'=>'message','label'=>'Message','type'=>'textarea','required'=>true],
    ]
  ],
  'courses'=>[
    'label'=>'Courses',
    'fields'=>[
      ['name'=>'name','label'=>'Course Name','type'=>'text','required'=>true],
      ['name'=>'category_id','label'=>'Category','type'=>'select:category','required'=>true],
      ['name'=>'short_desc','label'=>'Short Description','type'=>'textarea'],
      ['name'=>'price','label'=>'Price (Rp)','type'=>'number','min'=>'0'],
      ['name'=>'is_published','label'=>'Published?','type'=>'bool'],
    ],
    'uploads'=>[
      'image_file'=>['courses','image_url','required'=>false,'label'=>'Image (image_url)']
    ]
  ],
  'enrollments'=>[
    'label'=>'Enrollments',
    'fields'=>[
      ['name'=>'user_id','label'=>'User','type'=>'select:user','required'=>true],
      ['name'=>'course_id','label'=>'Course','type'=>'select:course','required'=>true],
    ]
  ],
];

/* === Validasi tabel === */
$table = $_GET['table'] ?? '';
if (!isset($SCHEMA[$table])) die('Invalid table');
$meta   = $SCHEMA[$table];
$fields = $meta['fields'];

/* === Data dropdown === */
$users=$courses=$categories=[];
if ($table==='enrollments'){
  $u=mysqli_query($conn,"SELECT id,nama FROM users ORDER BY nama");
  while($r=mysqli_fetch_assoc($u)) $users[]=$r;
  $c=mysqli_query($conn,"SELECT id,name FROM courses ORDER BY name");
  while($r=mysqli_fetch_assoc($c)) $courses[]=$r;
}
if ($table==='courses'){
  $g = mysqli_query($conn,"SELECT id, name, price FROM categories WHERE is_active=1 ORDER BY sort_order ASC, name ASC");
  $categories = [];
  while($r = mysqli_fetch_assoc($g)) $categories[] = $r;
}

/* === Handle POST === */
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST'){
  $cols=[]; $place=[]; $vals=[]; $types='';

  foreach($fields as $f){
    $n=$f['name']; $t=$f['type'];

    if ($t==='bool'){
      $v = isset($_POST[$n]) ? 1 : 0;
      $types.='i';
    } elseif ($t==='number'){
      $v = (int)($_POST[$n] ?? 0);
      $types.='i';
    } elseif ($t==='select:user' || $t==='select:course' || $t==='select:category'){
      $v = (int)($_POST[$n] ?? 0);
      $types.='i';
    } else {
      $v = trim($_POST[$n] ?? '');
      $v = ($v==='')?null:$v;
      $types.='s';
    }

    $cols[]="`$n`"; $place[]='?'; $vals[]=$v;
  }

  /* === Upload File (image_url) === */
  if (!empty($meta['uploads'])){
    foreach ($meta['uploads'] as $inputName => $cfg){
      $subdir = $cfg[0];
      $col = $cfg[1];
      $isReq = $cfg['required'] ?? false;

      if (!empty($_FILES[$inputName]['name'])){
        $f = $_FILES[$inputName];
        if ($f['error']===UPLOAD_ERR_OK){
          $mime = mime_content_type($f['tmp_name']);
          $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
          if (!isset($allowed[$mime])) $error='Invalid file type';
          else {
            $ext=$allowed[$mime];
            $fname=$subdir.'_'.date('Ymd_His').'_'.$f['name'];
            $dir='../../../../assets/uploads/'.$subdir.'/';
            if(!is_dir($dir)) @mkdir($dir,0775,true);
            if(move_uploaded_file($f['tmp_name'],$dir.$fname)){
              $cols[]="`$col`";
              $place[]='?';
              $vals[]='/assets/uploads/'.$subdir.'/'.$fname;
              $types.='s';
            } else $error='Failed to move uploaded file';
          }
        } else $error='Upload error';
      } elseif($isReq){
        $error='File is required';
      }
    }
  }

  /* === Tambahkan kolom category (string) otomatis === */
  if ($table==='courses' && empty($error)){
    $catId = (int)($_POST['category_id'] ?? 0);
    if ($catId>0){
      $res=mysqli_query($conn,"SELECT name FROM categories WHERE id=$catId LIMIT 1");
      if($res && $row=mysqli_fetch_assoc($res)){
        $cols[]='`category`';
        $place[]='?';
        $vals[]=$row['name'];
        $types.='s';
      }
    }
  }

  if ($error===''){
    $sql="INSERT INTO `$table` (".implode(',',$cols).") VALUES (".implode(',',$place).")";
    $stmt=mysqli_prepare($conn,$sql);
    mysqli_stmt_bind_param($stmt,$types,...$vals);
    if (mysqli_stmt_execute($stmt))
      redirect_crud_success($table,'Added successfully');
    else
      $error='DB Error: '.mysqli_error($conn);
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add <?= safe($meta['label']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
  <style>
    .img-preview{display:none;margin-top:8px;max-height:140px;border:1px solid #e5e7eb;border-radius:8px}
  </style>
</head>
<body class="bg-light">
<?php include INC_PATH . '/navbar.php'; ?>
<div class="main-content p-4">
  <h3 class="mb-3">Add <?= safe($meta['label']) ?></h3>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= safe($error) ?></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm">
    <input type="hidden" name="table" value="<?= safe($table) ?>">

    <?php foreach($fields as $f):
      $n=$f['name']; $l=$f['label']; $t=$f['type']; $req=!empty($f['required']); ?>
      <div class="mb-3">
        <label class="form-label" for="<?= safe($n) ?>"><?= safe($l) ?></label>
        <?php if ($t==='textarea'): ?>
          <textarea name="<?= $n ?>" class="form-control" rows="4" <?= $req?'required':'' ?>></textarea>
        <?php elseif ($t==='bool'): ?>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="<?= $n ?>" id="<?= $n ?>">
            <label class="form-check-label" for="<?= $n ?>">Yes</label>
          </div>
        <?php elseif ($t==='select:user'): ?>
          <select name="<?= $n ?>" class="form-select" required>
            <option value="">-- Choose User --</option>
            <?php foreach($users as $u): ?><option value="<?= $u['id'] ?>"><?= safe($u['nama']) ?></option><?php endforeach; ?>
          </select>
        <?php elseif ($t==='select:category'): ?>
          <select id="category_id" name="<?= $n ?>" class="form-select" <?= $req?'required':'' ?>>
            <option value="">-- Choose Category --</option>
            <?php foreach($categories as $cat): ?>
              <option value="<?= (int)$cat['id'] ?>" data-price="<?= (int)$cat['price'] ?>">
                <?= safe($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        <?php elseif ($n==='price'):  ?>
          <input id="price" type="number" name="price" class="form-control" min="0" step="1">
        <?php elseif ($t==='select:category'): ?>
          <select name="<?= $n ?>" class="form-select" required>
            <option value="">-- Choose Category --</option>
            <?php foreach($categories as $cat): ?><option value="<?= $cat['id'] ?>"><?= safe($cat['name']) ?></option><?php endforeach; ?>
          </select>
        <?php else: ?>
          <input type="<?= $t ?>" name="<?= $n ?>" class="form-control" <?= $req?'required':'' ?>>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

    <?php if (!empty($meta['uploads'])): ?>
      <?php foreach($meta['uploads'] as $inputName => $cfg): 
        $labelText = $cfg['label'] ?? 'File'; $req = !empty($cfg['required']);
        $inputId = safe($inputName); $imgPrevId = safe($inputName.'_prev');
      ?>
        <div class="mb-3">
          <label class="form-label" for="<?= $inputId ?>"><?= safe($labelText) ?> <?= $req?'<span class="text-danger">*</span>':'' ?></label>
          <input type="file" id="<?= $inputId ?>" name="<?= $inputName ?>" class="form-control" accept="image/*" <?= $req?'required':'' ?> onchange="previewImg(this,'<?= $imgPrevId ?>')">
          <img id="<?= $imgPrevId ?>" class="img-preview" alt="Preview">
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <button class="btn btn-success">Save</button>
    <a class="btn btn-secondary" href="<?= admin_table_url($table) ?>">Cancel</a>
  </form>
</div>

<script>
function previewImg(input,id){
  const f=input.files&&input.files[0];
  const img=document.getElementById(id);
  if(!f){img.style.display='none';img.src='';return;}
  img.src=URL.createObjectURL(f);
  img.style.display='block';
}

document.addEventListener('DOMContentLoaded', function(){
  const selCat = document.getElementById('category_id');
  const price  = document.getElementById('price');
  if (!selCat || !price) return;

  function fillPriceFromCategory(){
    const opt = selCat.options[selCat.selectedIndex];
    if (!opt) return;
    const p = opt.getAttribute('data-price');
    if (p !== null && p !== '' && !isNaN(p)) {
      price.value = p;       // auto isi harga
    }
  }

  selCat.addEventListener('change', fillPriceFromCategory);
  // auto isi saat pertama kali load jika sudah ada pilihan
  if (selCat.value) fillPriceFromCategory();
});
</script>
</body>
</html>
