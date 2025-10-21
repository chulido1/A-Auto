<?php
require_once '../config/db.php';
require_once '../includes/auth.php';   // can(), require_auth()
require_auth();                        // пускаем только авторизованных

include '../includes/header.php';

// --- Поиск + сортировка
$search = trim($_GET['search'] ?? '');
$query  = "SELECT * FROM departments";
$params = [];

if ($search !== '') {
    $query .= " WHERE name LIKE :s1 OR chief LIKE :s2";
    $params[':s1'] = "%{$search}%";
    $params[':s2'] = "%{$search}%";
}
$query .= " ORDER BY id ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="departments-section">
  <h2>Цехи и участки</h2>
  <p>Здесь отображается список производственных цехов. Доступные действия зависят от вашей роли.</p>

  <div class="actions-bar">
    <form method="get" class="search-form">
      <input type="text"
             name="search"
             placeholder="Поиск по названию или начальнику..."
             value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn search-btn">
        <i data-lucide="search"></i> Найти
      </button>
    </form>

    <?php if (can('departments.create')): ?>
      <button class="btn add-btn" onclick="openModal()">
        <i data-lucide="plus"></i> Добавить цех
      </button>
    <?php endif; ?>
  </div>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Название цеха</th>
        <th>Начальник</th>
        <th>Описание</th>
        <th>Действия</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$departments): ?>
        <tr><td colspan="5">Цехов не найдено.</td></tr>
      <?php else: foreach ($departments as $dept): ?>
        <tr>
          <td><?= (int)$dept['id'] ?></td>
          <td><?= htmlspecialchars($dept['name']) ?></td>
          <td><?= htmlspecialchars($dept['chief'] ?? '') ?></td>
          <td><?= htmlspecialchars($dept['description'] ?? '') ?></td>
          <td>
            <?php if (can('sections.view')): ?>
              <button class="btn small-btn"
                      onclick="openSectionsModal(<?= (int)$dept['id'] ?>, '<?= htmlspecialchars($dept['name']) ?>')">
                <i data-lucide="list"></i> Участки
              </button>
            <?php endif; ?>

            <?php if (can('departments.edit')): ?>
              <button class="btn edit-btn"
                      onclick="editDepartment(
                        <?= (int)$dept['id'] ?>,
                        '<?= htmlspecialchars($dept['name']) ?>',
                        '<?= htmlspecialchars($dept['chief'] ?? '') ?>',
                        '<?= htmlspecialchars($dept['description'] ?? '') ?>'
                      )">
                <i data-lucide="pencil"></i>
              </button>
            <?php endif; ?>

            <?php if (can('departments.delete')): ?>
              <button class="btn delete-btn" onclick="deleteDepartment(<?= (int)$dept['id'] ?>)">
                <i data-lucide="trash-2"></i>
              </button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</section>

<!-- Модалка: ЦЕХ -->
<div class="modal" id="deptModal">
  <div class="modal-content">
    <h3 id="modalTitle">Добавить цех</h3>
    <form id="deptForm" method="post" action="../scripts/departments-handler.php">
      <input type="hidden" name="id" id="deptId">

      <label>Название</label>
      <input type="text" name="name" id="deptName" required>

      <label>Начальник</label>
      <input type="text" name="chief" id="deptChief">

      <label>Описание</label>
      <textarea name="description" id="deptDesc" rows="3"></textarea>

      <div class="modal-actions">
        <button type="submit" name="save" class="btn">
          <i data-lucide="save"></i> Сохранить
        </button>
        <button type="button" class="btn cancel" onclick="closeModal()">Отмена</button>
      </div>
    </form>
  </div>
</div>

<!-- Модалка: УЧАСТКИ конкретного цеха -->
<div class="modal" id="sectionsModal">
  <div class="modal-content modal-lg">
    <h3 id="sectionsTitle">Участки цеха</h3>

    <!-- Прокручиваемая таблица участков подгружается Ajax-ом в этот контейнер -->
    <div id="sectionsTableWrap" style="max-height:420px; overflow-y:auto; margin-top:12px;"></div>

    <div class="modal-actions">
      <button type="button" class="btn cancel" onclick="closeSectionsModal()">Закрыть</button>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
