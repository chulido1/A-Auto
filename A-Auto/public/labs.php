<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
include '../includes/header.php';
require_auth();

$user = $_SESSION['user'];
$role = $user['role_id'];

// Поиск лабораторий
$q = trim($_GET['q'] ?? '');
$params = [];

$sql = "SELECT * FROM labs";
if ($q !== '') {
  $sql .= " WHERE name LIKE :q OR description LIKE :q2";
  $params = [':q' => "%$q%", ':q2' => "%$q%"];
}
$sql .= " ORDER BY id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$labs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section>
  <h2>Лаборатории</h2>
  <p>Информация об испытательных лабораториях и их оборудовании.</p>

  <div class="actions-bar">
    <form class="search-form" method="get">
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Поиск по названию или описанию...">
      <button class="btn search-btn" type="submit"><i data-lucide="search"></i> Найти</button>
    </form>

    <?php if ($role == 1 || $role == 2): ?>
      <button type="button" class="btn add-btn" onclick="openLabModal()">
        <i data-lucide="plus"></i> Добавить лабораторию
      </button>
    <?php endif; ?>
  </div>

  <div class="table-wrap">
    <table class="table-employees">
      <thead>
        <tr>
          <th style="width:60px;">ID</th>
          <th style="width:220px;">Название</th>
          <th>Описание</th>
          <th style="width:300px;text-align:center;">Действия</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$labs): ?>
          <tr><td colspan="4">Лабораторий пока нет.</td></tr>
        <?php else: foreach ($labs as $lab): ?>
          <tr>
            <td><?= $lab['id'] ?></td>
            <td><?= htmlspecialchars($lab['name']) ?></td>
            <td style="white-space:normal;"><?= htmlspecialchars($lab['description']) ?></td>

            <!-- Все кнопки в одной ячейке -->
            <td class="lab-actions">
              <div class="lab-buttons">
                <!-- Просмотр -->
                <button class="btn small-btn" onclick="openEquipmentModal(<?= $lab['id'] ?>, '<?= htmlspecialchars($lab['name']) ?>')">
                  <i data-lucide="list"></i> Просмотр
                </button>

                <!-- Редактирование -->
                <?php if ($role == 1 || $role == 2): ?>
                  <button class="btn edit-btn" onclick='editLab(<?= $lab["id"] ?>, <?= json_encode($lab["name"]) ?>, <?= json_encode($lab["description"]) ?>)'>
                    <i data-lucide="pencil"></i>
                  </button>
                <?php endif; ?>

                <!-- Удаление -->
                <?php if ($role == 1): ?>
                  <button class="btn delete-btn" onclick="deleteLab(<?= $lab['id'] ?>)">
                    <i data-lucide="trash-2"></i>
                  </button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- Модалка лаборатории -->
<div class="modal" id="labModal">
  <div class="modal-content modal-lg">
    <h3 id="labTitle">Добавить лабораторию</h3>
    <form id="labForm" method="post" action="../scripts/labs-handler.php">
      <input type="hidden" name="id" id="labId">

      <label>Название лаборатории</label>
      <input type="text" name="name" id="labName" required>

      <label>Описание</label>
      <textarea name="description" id="labDesc" rows="3"></textarea>

      <div class="modal-actions">
        <button class="btn add-btn" type="submit" name="save">
          <i data-lucide="save"></i> Сохранить
        </button>
        <button class="btn cancel" type="button" onclick="closeLabModal()">Отмена</button>
      </div>
    </form>
  </div>
</div>

<!-- Модалка оборудования -->
<div class="modal" id="equipmentModal">
  <div class="modal-content modal-lg">
    <div class="modal-header">
      <h3 id="equipTitle">Оборудование лаборатории</h3>
      <button type="button" class="btn cancel" onclick="closeEquipmentModal()">Закрыть</button>
    </div>
    <div id="equipmentWrap" style="margin-top:12px;"></div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
if (window.lucide) lucide.createIcons();
</script>
