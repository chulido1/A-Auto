<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
include '../includes/header.php';
require_auth();

$user = $_SESSION['user'];
$role = (int)$user['role_id'];

$product_id = (int)($_GET['product_id'] ?? 0);
if ($product_id <= 0) { echo "<p>Не передан product_id.</p>"; include '../includes/footer.php'; exit; }

$stmt = $pdo->prepare("SELECT id, model_name, serial_number FROM products WHERE id=?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) { echo "<p>Изделие не найдено.</p>"; include '../includes/footer.php'; exit; }

$sql = "SELECT
    t.id,
    t.product_id,
    t.lab_id,
    t.performed_by_employee_id,
    t.test_date,
    t.result,
    t.protocol_path,
    t.comments,
    l.name AS lab_name,
    CONCAT(e.last_name,' ',e.first_name) AS emp_name
  FROM tests t
  LEFT JOIN labs l      ON l.id = t.lab_id
  LEFT JOIN employees e ON e.id = t.performed_by_employee_id
  WHERE t.product_id = ?
  ORDER BY t.test_date DESC, t.id DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$product_id]);
$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// справочники
$labs = $pdo->query("SELECT id,name FROM labs ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$emps = $pdo->query("SELECT id, CONCAT(last_name,' ',first_name) AS name FROM employees ORDER BY last_name, first_name")->fetchAll(PDO::FETCH_ASSOC);

// ярлыки результата
$RESULT_LABELS = [
  'pending' => 'ожидается',
  'passed'  => 'успешно',
  'failed'  => 'неуспешно'
];
?>
<section>
  <h2>Испытания изделия</h2>
  <p>
    Изделие: <strong><?= htmlspecialchars($product['model_name']) ?></strong>,
    № <strong><?= htmlspecialchars($product['serial_number']) ?></strong>
  </p>

  <div class="actions-bar">
    <a class="btn" href="products.php">← К списку изделий</a>
    <?php if ($role <= 2): ?>
      <button class="btn add-btn" type="button" onclick="openTestModal()">
        <i data-lucide="plus"></i> Добавить испытание
      </button>
    <?php endif; ?>
  </div>

  <div class="table-wrap">
    <table class="table-employees tests-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Лаборатория</th>
          <th>Дата</th>
          <th>Исполнитель</th>
          <th>Результат</th>
          <th>Протокол</th>
          <th>Комментарий</th>
          <?php if ($role <= 2): ?><th>Действия</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
      <?php if (!$tests): ?>
        <tr><td colspan="<?= $role<=2 ? 8 : 7 ?>">Записей нет.</td></tr>
      <?php else: foreach ($tests as $i => $t): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= htmlspecialchars($t['lab_name'] ?? '') ?></td>
          <td><?= htmlspecialchars($t['test_date'] ?? '') ?></td>
          <td><?= htmlspecialchars($t['emp_name'] ?? '') ?></td>
          <td>
            <?php
              $raw = trim((string)($t['result'] ?? ''));
              echo htmlspecialchars($RESULT_LABELS[$raw] ?? $raw);
            ?>
          </td>
          <td>
            <?php if (!empty($t['protocol_path'])): ?>
              <a href="<?= htmlspecialchars($t['protocol_path']) ?>" target="_blank">открыть</a>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($t['comments'] ?? '') ?></td>

          <?php if ($role <= 2): ?>
          <td class="actions">
            <button class="btn edit-btn" onclick='editTest(
              <?= (int)$t["id"] ?>,
              <?= (int)$t["lab_id"] ?>,
              <?= json_encode($t["test_date"] ?? "") ?>,
              <?= (int)($t["performed_by_employee_id"] ?? 0) ?>,
              <?= json_encode($t["result"] ?? "") ?>,
              <?= json_encode($t["protocol_path"] ?? "") ?>,
              <?= json_encode($t["comments"] ?? "") ?>
            )'><i data-lucide="pencil"></i></button>

            <?php if ($role == 1): ?>
              <button class="btn delete-btn" onclick="deleteTest(<?= (int)$t['id'] ?>)">
                <i data-lucide="trash-2"></i>
              </button>
            <?php endif; ?>
          </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- Модалка испытания -->
<div class="modal" id="testModal">
  <div class="modal-content modal-lg">
    <div class="modal-header" style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
      <h3 id="testTitle" style="margin:0;">Добавить испытание</h3>
      <button class="btn cancel" type="button" onclick="closeTestModal()">Закрыть</button>
    </div>

    <form id="testForm" method="post" action="../scripts/product-tests-handler.php">
      <input type="hidden" name="id" id="testId">
      <input type="hidden" name="product_id" value="<?= (int)$product_id ?>">

      <div class="grid3">
        <div>
          <label>Лаборатория</label>
          <select name="lab_id" id="testLab" required>
            <option value="">— не выбрана —</option>
            <?php foreach ($labs as $l): ?>
              <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>Дата</label>
          <input type="date" name="test_date" id="testDate" required>
        </div>
        <div>
          <label>Исполнитель</label>
          <select name="performed_by_employee_id" id="testEmp">
            <option value="">— не выбран —</option>
            <?php foreach ($emps as $e): ?>
              <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="grid3">
        <div>
          <label>Результат</label>
          <select name="result" id="testResult">
            <option value="pending">ожидается</option>
            <option value="passed">успешно</option>
            <option value="failed">неуспешно</option>
          </select>
        </div>
        <div>
          <label>Протокол (путь/№)</label>
          <input type="text" name="protocol_path" id="testProtocol">
        </div>
        <div>
          <label>Комментарий</label>
          <input type="text" name="comments" id="testComment">
        </div>
      </div>

      <div class="modal-actions">
        <button class="btn add-btn" type="submit" name="save"><i data-lucide="save"></i> Сохранить</button>
        <button class="btn cancel" type="button" onclick="closeTestModal()">Отмена</button>
      </div>
    </form>
  </div>
</div>

<script>
function openTestModal(){
  const f = document.getElementById('testForm');
  f.reset();
  document.getElementById('testId').value = '';
  document.getElementById('testTitle').textContent = 'Добавить испытание';
  document.getElementById('testModal').style.display = 'flex';
  if (window.lucide) lucide.createIcons();
}
function closeTestModal(){ document.getElementById('testModal').style.display = 'none'; }

function editTest(id, labId, date, empId, result, proto, comm){
  document.getElementById('testId').value = id;
  document.getElementById('testTitle').textContent = 'Редактировать испытание';

  document.getElementById('testLab').value = labId || '';
  document.getElementById('testEmp').value = empId || '';
  document.getElementById('testDate').value = date || '';
  document.getElementById('testResult').value = result || 'pending';
  document.getElementById('testProtocol').value = proto || '';
  document.getElementById('testComment').value = comm || '';

  document.getElementById('testModal').style.display = 'flex';
  if (window.lucide) lucide.createIcons();
}

function deleteTest(id){
  if (!confirm('Удалить запись испытания?')) return;
  const pid = <?= (int)$product_id ?>;
  window.location.href = '../scripts/product-tests-handler.php?delete='+id+'&product_id='+pid;
}
if (window.lucide) lucide.createIcons();
</script>

<?php include '../includes/footer.php'; ?>
