<?php
require_once '../config/db.php';
include '../includes/header.php';

$stats = [
  'departments' => (int)$pdo->query('SELECT COUNT(*) FROM departments')->fetchColumn(),
  'employees'   => (int)$pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn(),
  'products'    => (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
  'tests'       => (int)$pdo->query('SELECT COUNT(*) FROM tests')->fetchColumn(),
];
?>

<section class="hero-banner" style="background-image:url('/A-Auto/assets/images/hero.jpg');">
  <div class="hero-overlay">
    <h1>A-Auto</h1>
    <p>Веб-приложение для учёта цехов, участков, кадров, изделий и испытаний на автомобилестроительном предприятии.</p>
  </div>
</section>

<section class="modules">
  <a class="module-card" href="departments.php">
    <div class="module-img" style="background-image:url('/A-Auto/assets/images/feature-departments.webp');"></div>
    <div class="module-body">
      <h3>Цехи и участки</h3>
      <p>Иерархия подразделений, участков и бригад.</p>
    </div>
  </a>

  <a class="module-card" href="employees.php">
    <div class="module-img" style="background-image:url('/A-Auto/assets/images/feature-employees.webp');"></div>
    <div class="module-body">
      <h3>Кадры и бригады</h3>
      <p>Сотрудники, профессии, разряды и распределение.</p>
    </div>
  </a>

  <a class="module-card" href="products.php">
    <div class="module-img" style="background-image:url('/A-Auto/assets/images/feature-products.webp');"></div>
    <div class="module-body">
      <h3>Изделия</h3>
      <p>Категории, характеристики и стадия сборки.</p>
    </div>
  </a>

  <a class="module-card" href="labs.php">
    <div class="module-img" style="background-image:url('/A-Auto/assets/images/feature-labs.webp');"></div>
    <div class="module-body">
      <h3>Испытательные лаборатории</h3>
      <p>Оборудование, протоколы и результаты испытаний.</p>
    </div>
  </a>

  <a class="module-card" href="login.php">
    <div class="module-img" style="background-image:url('/A-Auto/assets/images/feature-cabinet.webp');"></div>
    <div class="module-body">
      <h3>Личный кабинет</h3>
      <p>Вход; доступ к административным функциям по роли.</p>
    </div>
  </a>
</section>

<section class="stats">
  <div class="kpi">
    <div class="kpi-num"><?= $stats['departments'] ?></div>
    <div class="kpi-label">Цехов/участков</div>
  </div>
  <div class="kpi">
    <div class="kpi-num"><?= $stats['employees'] ?></div>
    <div class="kpi-label">Сотрудников</div>
  </div>
  <div class="kpi">
    <div class="kpi-num"><?= $stats['products'] ?></div>
    <div class="kpi-label">Изделий</div>
  </div>
  <div class="kpi">
    <div class="kpi-num"><?= $stats['tests'] ?></div>
    <div class="kpi-label">Испытаний</div>
  </div>
</section>

<section class="contacts">
  <h3>Контакты</h3>
  <div class="contacts-grid">
    <div>
      <p><strong>ИТ-поддержка:</strong> <br> ceo_e.n.ndreev@a-auto.local <br> +7 (903) 277-73-01</p>
      <p><strong>Адрес:</strong> 121059, Москва, Бережковская набережная, 12</p>
    </div>

  </div>
</section>

<?php include '../includes/footer.php'; ?>
