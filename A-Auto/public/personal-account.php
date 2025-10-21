<?php
session_start();
require_once '../config/db.php';

// Если пользователь не авторизован — отправляем на login.php
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
?>
<?php include '../includes/header.php'; ?>

<link rel="stylesheet" href="/A-Auto/assets/css/personal-account.css">

<main class="account-page">
  <section class="account-card">
    <h2>Личный кабинет</h2>
    <p class="welcome">
      Добро пожаловать, <strong><?= htmlspecialchars($user['login']) ?></strong>!
    </p>

    <div class="user-info">
      <div class="info-item">
        <span>ID:</span> <?= (int)$user['id'] ?>
      </div>

      <div class="info-item">
        <span>Логин:</span> <?= htmlspecialchars($user['login']) ?>
      </div>

      <div class="info-item">
        <span>Роль:</span>
        <?php
          switch ($user['role_id']) {
            case 1: echo 'Администратор'; break;
            case 2: echo 'Менеджер'; break;
            default: echo 'Рабочий';
          }
        ?>
      </div>

      <div class="info-item">
        <span>Дата входа:</span> <?= date('d.m.Y H:i') ?>
      </div>
    </div>

    <div class="actions">
      <a href="logout.php" class="btn logout-btn">
        <i data-lucide="log-out"></i> Выйти из системы
      </a>
      <a href="index.php" class="btn back-btn">
        <i data-lucide="home"></i> На главную
      </a>
    </div>
  </section>
</main>

<?php include '../includes/footer.php'; ?>

<script>
if (window.lucide) lucide.createIcons();
</script>


