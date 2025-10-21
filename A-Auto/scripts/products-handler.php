<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_auth();

$role = (int)$_SESSION['user']['role_id'];
// admin(1) и engineer(2) могут изменять
if ($role > 2) {
  http_response_code(403);
  exit('Доступ запрещён');
}

if (isset($_POST['save'])) {
  $id    = (int)($_POST['id'] ?? 0);
  $model = trim($_POST['model_name'] ?? '');
  $serial= trim($_POST['serial_number'] ?? '');
  $cat   = ($_POST['category_id'] === '' ? null : (int)$_POST['category_id']);
  $dep   = ($_POST['department_id'] === '' ? null : (int)$_POST['department_id']);
  $sec   = ($_POST['current_section_id'] === '' ? null : (int)$_POST['current_section_id']);
  $start = ($_POST['start_date'] ?? '') ?: null;
  $qty   = ($_POST['quantity'] === '' ? null : (int)$_POST['quantity']);
  $status= trim($_POST['status'] ?? '');
  $notes = trim($_POST['notes'] ?? '');

  if ($id > 0) {
    // UPDATE
    $stmt = $pdo->prepare(
      "UPDATE products
          SET model_name = :model,
              serial_number = :serial,
              category_id = :cat,
              department_id = :dep,
              current_section_id = :sec,
              start_date = :start,
              quantity = :qty,
              status = :status,
              notes = :notes
        WHERE id = :id"
    );
    $stmt->execute([
      ':model'=>$model, ':serial'=>$serial, ':cat'=>$cat, ':dep'=>$dep, ':sec'=>$sec,
      ':start'=>$start, ':qty'=>$qty, ':status'=>$status, ':notes'=>$notes, ':id'=>$id
    ]);
  } else {
    // INSERT
    $stmt = $pdo->prepare(
      "INSERT INTO products
        (model_name, category_id, serial_number, start_date, department_id, current_section_id, status, quantity, notes)
       VALUES
        (:model, :cat, :serial, :start, :dep, :sec, :status, :qty, :notes)"
    );
    $stmt->execute([
      ':model'=>$model, ':cat'=>$cat, ':serial'=>$serial, ':start'=>$start,
      ':dep'=>$dep, ':sec'=>$sec, ':status'=>$status, ':qty'=>$qty, ':notes'=>$notes
    ]);
  }

  header('Location: ../public/products.php');
  exit;
}

if (isset($_GET['delete']) && $role == 1) {
  $id = (int)$_GET['delete'];
  $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
  header('Location: ../public/products.php');
  exit;
}

http_response_code(400);
echo "Bad request";
