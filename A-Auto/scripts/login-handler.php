<?php
session_start();
require_once '../config/db.php';

$login = trim($_POST['login'] ?? '');
$pass  = trim($_POST['password'] ?? '');

if ($login === '' || $pass === '') {
  $_SESSION['login_error'] = 'Введите логин и пароль.';
  header('Location: ../public/login.php');
  exit;
}

// Ищем пользователя по логину и активному статусу
$stmt = $pdo->prepare("SELECT * FROM users WHERE login = :login AND is_active = 1");
$stmt->execute([':login' => $login]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);


// Проверяем найден ли пользователь и совпадает ли пароль
if ($user && password_verify($pass, $user['password_hash'])) {

  unset($_SESSION['login_error']);
  $_SESSION['user'] = [
    'id'    => $user['id'],
    'login' => $user['login'],
    'role_id' => $user['role_id']
  ];

  // Перенаправляем по роли
  switch ($user['role_id']) {
    case 1:
      header('Location: ../public/admin.php'); break;
    case 2:
      header('Location: ../public/departments.php'); break;
    case 3:
    default:
      header('Location: ../public/personal-account.php'); break;
  }
  exit;

} else {
  $_SESSION['login_error'] = 'Неверный логин или пароль.';
  header('Location: ../public/login.php');
  exit;
}
