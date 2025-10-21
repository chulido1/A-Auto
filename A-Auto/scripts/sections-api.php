<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_auth();

header('Content-Type: text/html; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'list') {
  $depId = (int)($_GET['department_id'] ?? 0);

  // Список участков (работает со старым полем supervisor)
  $stmt = $pdo->prepare("SELECT id, name, supervisor, description 
                         FROM sections 
                         WHERE department_id = :dep 
                         ORDER BY id ASC");
  $stmt->execute([':dep' => $depId]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  ob_start(); ?>
  <div class="scroll-table" style="max-height:420px;overflow-y:auto;margin-bottom:15px;">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Название участка</th>
          <th>Начальник участка</th>
          <th>Описание</th>
          <th>Действия</th>
        </tr>
      </thead>
      <tbody>
        <?php if(!$rows): ?>
          <tr><td colspan="5">Участков пока нет.</td></tr>
        <?php else: foreach($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= htmlspecialchars($r['name']) ?></td>
            <td><?= htmlspecialchars($r['supervisor'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['description'] ?? '') ?></td>
            <td>
              <?php if (can('sections.edit')): ?>
              <button class="btn edit-btn"
                onclick='editSection(
                  <?= (int)$r["id"] ?>,
                  <?= json_encode($r["name"]) ?>,
                  <?= json_encode($r["supervisor"] ?? "") ?>,
                  <?= json_encode($r["description"] ?? "") ?>
                )'>
                <i data-lucide="pencil"></i>
              </button>
              <?php endif; ?>

              <?php if (can('sections.delete')): ?>
              <button class="btn delete-btn" onclick="deleteSection(<?= (int)$r['id'] ?>)">
                <i data-lucide="trash-2"></i>
              </button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <?php if (can('sections.edit')): ?>
  <!-- Форма добавления/редактирования -->
  <form id="sectionForm" style="margin-top:12px;">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" id="sectionId">
    <input type="hidden" name="department_id" id="sectionDepartmentId" value="<?= (int)$depId ?>">

    <label>Название участка</label>
    <input type="text" name="name" id="sectionName" required>

    <label>Начальник участка</label>
    <input type="text" name="supervisor" id="sectionChief">

    <label>Описание</label>
    <textarea name="description" id="sectionDesc" rows="3"></textarea>

    <div class="modal-actions">
      <button class="btn add-btn" type="submit">
        <i data-lucide="save"></i> Сохранить участок
      </button>
      <button class="btn cancel" type="button" onclick="closeSectionsModal()">Отмена</button>
    </div>
  </form>
  <?php endif; ?>
  <?php
  echo ob_get_clean(); exit;
}


// === Добавление / редактирование ===
if ($action === 'save') {
  if (!can('sections.edit')) {
    http_response_code(403);
    exit('Forbidden');
  }

  $id   = (int)($_POST['id'] ?? 0);
  $dep  = (int)($_POST['department_id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $sup  = trim($_POST['supervisor'] ?? '');
  $desc = trim($_POST['description'] ?? '');

  if ($id > 0) {
    $stmt = $pdo->prepare("UPDATE sections SET name=:n, supervisor=:s, description=:d WHERE id=:id");
    $stmt->execute([':n'=>$name, ':s'=>$sup, ':d'=>$desc, ':id'=>$id]);
  } else {
    $stmt = $pdo->prepare("INSERT INTO sections (name, department_id, supervisor, description)
                           VALUES (:n, :dep, :s, :d)");
    $stmt->execute([':n'=>$name, ':dep'=>$dep, ':s'=>$sup, ':d'=>$desc]);
  }
  echo "OK"; exit;
}


// === Удаление ===
if ($action === 'delete') {
  if (!can('sections.delete')) {
    http_response_code(403);
    exit('Forbidden');
  }

  $id = (int)($_GET['id'] ?? 0);
  if ($id > 0) {
    $pdo->prepare("DELETE FROM sections WHERE id=:id")->execute([':id'=>$id]);
    echo "OK";
  } else echo "ERR";
  exit;
}

http_response_code(400);
echo "Unsupported action";
