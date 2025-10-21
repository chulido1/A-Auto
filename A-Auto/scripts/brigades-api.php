<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_auth();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'list') {
  $rows = $pdo->query("SELECT b.id, b.name,
                              s.name AS section_name,
                              CONCAT(e.last_name,' ',e.first_name,' ',COALESCE(e.patronymic,'')) AS foreman_fio
                        FROM brigades b
                        JOIN sections s ON s.id=b.section_id
                        LEFT JOIN employees e ON e.id=b.foreman_employee_id
                        ORDER BY b.id ASC")->fetchAll(PDO::FETCH_ASSOC);

  $allSections = $pdo->query("SELECT s.id, CONCAT(d.name,' → ',s.name) AS name
                              FROM sections s JOIN departments d ON d.id=s.department_id
                              ORDER BY s.id ASC")->fetchAll(PDO::FETCH_ASSOC);

  $emps = $pdo->query("SELECT id, CONCAT(last_name,' ',first_name,' ',COALESCE(patronymic,'')) AS fio
                       FROM employees ORDER BY last_name, first_name")->fetchAll(PDO::FETCH_ASSOC);

  ob_start(); ?>
  <div class="scroll-table" style="max-height:420px; overflow-y:auto;">
    <table>
      <thead>
        <tr><th>ID</th><th>Бригада</th><th>Участок</th><th>Бригадир</th><th>Действия</th></tr>
      </thead>
      <tbody>
        <?php if(!$rows): ?>
          <tr><td colspan="5">Бригад пока нет.</td></tr>
        <?php else: foreach($rows as $r): ?>
          <tr>
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['name']) ?></td>
            <td><?= htmlspecialchars($r['section_name']) ?></td>
            <td><?= htmlspecialchars($r['foreman_fio'] ?? '') ?></td>
            <td>
              <?php if (can('brigades.edit')): ?>
              <button class="btn edit-btn"
                onclick='editBrig(<?= $r['id'] ?>, <?= json_encode($r['name']) ?>)'>
                <i data-lucide="pencil"></i>
              </button>
              <?php endif; ?>

              <?php if (can('brigades.delete')): ?>
              <button class="btn delete-btn" onclick="deleteBrig(<?= $r['id'] ?>)">
                <i data-lucide="trash-2"></i>
              </button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <?php if (can('brigades.edit')): ?>
  <form id="brigForm" style="margin-top:14px;">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" id="brigId">

    <label>Название бригады</label>
    <input type="text" name="name" id="brigName" required>

    <div class="grid3">
      <div>
        <label>Участок</label>
        <select name="section_id" id="brigSection" required>
          <option value="">— выбрать участок —</option>
          <?php foreach($allSections as $s): ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>Бригадир (сотрудник)</label>
        <select name="foreman_employee_id" id="brigForeman">
          <option value="">— не назначен —</option>
          <?php foreach($emps as $e): ?>
            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['fio']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="modal-actions">
      <button class="btn add-btn" type="submit"><i data-lucide="save"></i> Сохранить</button>
      <button class="btn cancel" type="button" onclick="closeBrigadesModal()">Отмена</button>
    </div>
  </form>
  <?php endif; ?>
  <?php
  echo ob_get_clean(); exit;
}

if ($action === 'save') {
  if (!can('brigades.edit')) {
    http_response_code(403);
    exit('Forbidden');
  }

  $id   = (int)($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $sec  = (int)($_POST['section_id'] ?? 0);
  $frm  = ($_POST['foreman_employee_id'] === '' ? null : (int)$_POST['foreman_employee_id']);

  if ($id>0) {
    $pdo->prepare("UPDATE brigades SET name=:n, section_id=:s, foreman_employee_id=:f WHERE id=:id")
        ->execute([':n'=>$name, ':s'=>$sec, ':f'=>$frm, ':id'=>$id]);
  } else {
    $pdo->prepare("INSERT INTO brigades (name, section_id, foreman_employee_id) VALUES (:n,:s,:f)")
        ->execute([':n'=>$name, ':s'=>$sec, ':f'=>$frm]);
  }
  echo "OK"; exit;
}

if ($action === 'delete') {
  if (!can('brigades.delete')) {
    http_response_code(403);
    exit('Forbidden');
  }

  $id = (int)($_GET['id'] ?? 0);
  if ($id>0) {
    $pdo->prepare("DELETE FROM brigades WHERE id=:id")->execute([':id'=>$id]);
    echo "OK";
  } else echo "ERR";
  exit;
}

http_response_code(400);
echo "Unsupported action";
