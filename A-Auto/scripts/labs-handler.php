<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_auth();

$user = $_SESSION['user'];
$role = $user['role_id'];

// Только admin и engineer могут изменять
if ($role > 2) {
  http_response_code(403);
  exit('Доступ запрещён');
}

if (isset($_POST['save'])) {
  $id = (int)($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $desc = trim($_POST['description'] ?? '');

  if ($id > 0) {
    $stmt = $pdo->prepare("UPDATE labs SET name=?, description=? WHERE id=?");
    $stmt->execute([$name, $desc, $id]);
  } else {
    $stmt = $pdo->prepare("INSERT INTO labs (name, description) VALUES (?, ?)");
    $stmt->execute([$name, $desc]);
  }

  header('Location: ../public/labs.php');
  exit;
}

if (isset($_GET['delete']) && $role == 1) {
  $id = (int)$_GET['delete'];
  $pdo->prepare("DELETE FROM labs WHERE id=?")->execute([$id]);
  header('Location: ../public/labs.php');
  exit;
}

http_response_code(400);
echo "Bad request";
