<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>A-Auto | Система учёта</title>
  <link rel="stylesheet" href="/A-Auto/assets/css/style.css">
  <script src="https://unpkg.com/lucide@latest"></script>
  <script defer src="/A-Auto/assets/js/script.js"></script>
</head>
<body>
<header>
  <h1><a href="/A-Auto/public/index.php" style="color:#fff;text-decoration:none;">A-Auto</a></h1>

  <nav>
    <a href="/A-Auto/public/index.php">Главная</a>

    <?php if (can('departments.view')): ?>
      <a href="/A-Auto/public/departments.php">Цехи и участки</a>
    <?php endif; ?>

    <?php if (can('employees.view')): ?>
      <a href="/A-Auto/public/employees.php">Кадры и бригады</a>
    <?php endif; ?>

    <?php if (can('products.view')): ?>
      <a href="/A-Auto/public/products.php">Изделия</a>
    <?php endif; ?>

    <?php if (can('labs.view')): ?>
      <a href="/A-Auto/public/labs.php">Лаборатории</a>
    <?php endif; ?>

    <?php if (!empty($_SESSION['user'])): ?>
      <a href="/A-Auto/public/personal-account.php">Личный кабинет</a>
      <a href="/A-Auto/public/logout.php">Выход</a>
    <?php else: ?>
      <a href="/A-Auto/public/login.php">Войти</a>
    <?php endif; ?>
  </nav>
</header>

<main>
