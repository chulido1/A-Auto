<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_auth();

$role = (int)$_SESSION['user']['role_id'];
if ($role > 2) { http_response_code(403); exit('Доступ запрещён'); }

$ALLOWED_RESULTS = ['pending','passed','failed'];

if (isset($_POST['save'])) {
  $id   = (int)($_POST['id'] ?? 0);
  $pid  = (int)($_POST['product_id'] ?? 0);
  $lab  = ($_POST['lab_id'] === '' ? null : (int)$_POST['lab_id']);
  $date = ($_POST['test_date'] ?? '') ?: null;
  $emp  = ($_POST['performed_by_employee_id'] === '' ? null : (int)$_POST['performed_by_employee_id']);
  $res  = trim((string)($_POST['result'] ?? 'pending'));
  if (!in_array($res, $ALLOWED_RESULTS, true)) { $res = 'pending'; }
  $prot = trim((string)($_POST['protocol_path'] ?? ''));
  $comm = trim((string)($_POST['comments'] ?? ''));

  if ($pid <= 0 || !$lab || !$date) { http_response_code(400); exit('Некорректные данные'); }

  if ($id > 0) {
    $stmt = $pdo->prepare(
      "UPDATE tests
         SET lab_id = :lab,
             performed_by_employee_id = :emp,
             test_date = :date,
             `result` = :res,
             protocol_path = :prot,
             comments = :comm
       WHERE id = :id"
    );
    $stmt->execute([
      ':lab'=>$lab, ':emp'=>$emp, ':date'=>$date, ':res'=>$res,
      ':prot'=>$prot, ':comm'=>$comm, ':id'=>$id
    ]);
  } else {
    $stmt = $pdo->prepare(
      "INSERT INTO tests
         (product_id, lab_id, performed_by_employee_id, test_date, `result`, protocol_path, comments)
       VALUES
         (:pid, :lab, :emp, :date, :res, :prot, :comm)"
    );
    $stmt->execute([
      ':pid'=>$pid, ':lab'=>$lab, ':emp'=>$emp, ':date'=>$date, ':res'=>$res,
      ':prot'=>$prot, ':comm'=>$comm
    ]);
  }

  header('Location: ../public/product-tests.php?product_id='.$pid);
  exit;
}

if (isset($_GET['delete'])) {
  $id  = (int)$_GET['delete'];
  $pid = (int)($_GET['product_id'] ?? 0);
  if ($id > 0) {
    $pdo->prepare("DELETE FROM tests WHERE id=?")->execute([$id]);
  }
  header('Location: ../public/product-tests.php?product_id='.$pid);
  exit;
}

http_response_code(400);
echo 'Bad request';
