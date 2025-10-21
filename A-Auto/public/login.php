<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!empty($_SESSION['user'])) {
    header('Location: personal-account.php');
    exit;
}

$error = '';
$loginVal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $loginVal = htmlspecialchars($login);

    if ($login === '' || $pass === '') {
        $error = 'Введите логин и пароль.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE login = :login LIMIT 1");
        $stmt->execute([':login' => $login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($pass, $user['password_hash'])) {
            $error = 'Неверный логин или пароль.';
        } elseif (!$user['is_active']) {
            $error = 'Учётная запись заблокирована.';
        } else {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'login' => $user['login'],
                'role_id' => $user['role_id']
            ];
            header('Location: personal-account.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Вход в систему A-Auto</title>
  <link rel="stylesheet" href="/A-Auto/assets/css/style.css">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<?php include '../includes/header.php'; ?>

<body class="login-page">
  <div class="login-container">
    <h2>Вход в систему A-Auto</h2>
    <p>Введите логин и пароль для входа</p>

    <div class="form-box">
      <form method="post">
        <label>Логин</label>
        <input type="text" name="login" value="<?= $loginVal ?>" placeholder="Введите логин" required>

        <label>Пароль</label>
        <input type="password" name="password" placeholder="Введите пароль" required>

        <?php if ($error): ?>
          <div class="error-box"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <button type="submit" class="btn login-btn">
          <i data-lucide="log-in"></i> Войти
        </button>
      </form>
    </div>
  </div>

  <script>if(window.lucide) lucide.createIcons();</script>
  <?php include '../includes/footer.php'; ?>

</body>
</html>
