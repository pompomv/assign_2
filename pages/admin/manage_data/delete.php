<?php
require_once("../../../config/app.php");
require_once INC_PATH . '/require_login.php';
if (($_SESSION['role'] ?? '') !== 'admin') redirect_to('pages/auth/login.php?error=' . rawurlencode('Admin only'));

$SCHEMA = [
  'carousel_slides'=>['pk'=>'id'],
  'contacts' =>['pk'=>'id'],
  'courses'  =>['pk'=>'id'],
  'enrollments'=>['pk'=>'id'],
];
$table = $_GET['table'] ?? ''; if (!isset($SCHEMA[$table])) die('Invalid table');
$pk = $SCHEMA[$table]['pk'];
$id = (int)($_GET['id'] ?? 0); if (!$id) die('Missing id');

$stmt = mysqli_prepare($conn, "DELETE FROM `$table` WHERE `$pk`=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
redirect_crud_success($table, 'Deleted successfully');
exit;

