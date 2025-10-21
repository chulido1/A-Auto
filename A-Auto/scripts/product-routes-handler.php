<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_auth();

$role = (int)$_SESSION['user']['role_id'];
if ($role > 2) { http_response_code(403); exit('Доступ запрещён'); }

if (isset($_POST['save'])) {
  $id      = (int)($_POST['id'] ?? 0);
  $pid     = (int)($_POST['product_id'] ?? 0);
  $section = ($_POST['section_id'] === '' ? null : (int)$_POST['section_id']);
  $brigade = ($_POST['brigade_id'] === '' ? null : (int)$_POST['brigade_id']);
  $start   = $_POST['start_datetime'] ?: null;
  $end     = $_POST['end_datetime']   ?: null;

  if ($pid <= 0 || !$section || !$start) { http_response_code(400); exit('Некорректные данные'); }

  if ($id > 0) {
    $stmt = $pdo->prepare("UPDATE product_routes
                           SET section_id=?, brigade_id=?, start_datetime=?, end_datetime=?
                           WHERE id=?");
    $stmt->execute([$section, $brigade, $start, $end, $id]);
  } else {
    $stmt = $pdo->prepare("INSERT INTO product_routes (product_id, section_id, brigade_id, start_datetime, end_datetime)
                           VALUES (?,?,?,?,?)");
    $stmt->execute([$pid, $section, $brigade, $start, $end]);
  }
  header('Location: ../public/product-routes.php?product_id='.$pid);
  exit;
}

if (isset($_GET['delete'])) {
  $id  = (int)$_GET['delete'];
  $pid = (int)($_GET['product_id'] ?? 0);
  if ($id > 0) {
    $pdo->prepare("DELETE FROM product_routes WHERE id=?")->execute([$id]);
  }
  header('Location: ../public/product-routes.php?product_id='.$pid);
  exit;
}

http_response_code(400);
echo 'Bad request';
