<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
include '../includes/header.php';
require_auth();

$user = $_SESSION['user'];
$role = (int)$user['role_id'];

$product_id = (int)($_GET['product_id'] ?? 0);
if ($product_id <= 0) { echo "<p>Не передан product_id.</p>"; include '../includes/footer.php'; exit; }

// сам продукт (для заголовка)
$stmt = $pdo->prepare("SELECT id, model_name, serial_number FROM products WHERE id=?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) { echo "<p>Изделие не найдено.</p>"; include '../includes/footer.php'; exit; }

// маршруты (берём и id, и имена)
$sql = "SELECT
          r.id,
          r.section_id,
          r.brigade_id,
          r.start_datetime,
          r.end_datetime,
          s.name AS section_name,
          b.name AS brigade_name
        FROM product_routes r
        LEFT JOIN sections s  ON s.id  = r.section_id
        LEFT JOIN brigades b  ON b.id  = r.brigade_id
        WHERE r.product_id = ?
        ORDER BY r.start_datetime ASC, r.id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$product_id]);
$routes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// справочники
$sections = $pdo->query("SELECT id,name FROM sections ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$brigades = $pdo->query("SELECT id,name FROM brigades ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
<section>
  <h2>Маршрут изделия</h2>
  <p>
    Изделие: <strong><?= htmlspecialchars($product['model_name']) ?></strong>,
    № <strong><?= htmlspecialchars($product['serial_number']) ?></strong>
  </p>

  <div class="actions-bar">
    <a class="btn" href="products.php">← К списку изделий</a>
    <?php if ($role <= 2): ?>
      <button class="btn add-btn" type="button" onclick="openRouteModal()">
        <i data-lucide="plus"></i> Добавить этап
      </button>
    <?php endif; ?>
  </div>

  <div class="table-wrap">
    <table class="table-employees">
      <thead>
        <tr>
          <th>#</th>
          <th>Участок</th>
          <th>Бригада</th>
          <th>Начало</th>
          <th>Окончание</th>
          <?php if ($role <= 2): ?><th>Действия</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
      <?php if (!$routes): ?>
        <tr><td colspan="<?= $role<=2 ? 6 : 5 ?>">Этапов ещё нет.</td></tr>
      <?php else: foreach ($routes as $i => $r): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= htmlspecialchars($r['section_name'] ?? '') ?></td>
          <td><?= htmlspecialchars($r['brigade_name'] ?? '') ?></td>
          <td><?= htmlspecialchars($r['start_datetime']) ?></td>
          <td><?= htmlspecialchars($r['end_datetime']) ?></td>
          <?php if ($role <= 2): ?>
          <td class="actions">
            <button class="btn edit-btn" onclick='editRoute(
              <?= (int)$r["id"] ?>,
              <?= (int)($r["section_id"] ?? 0) ?>,
              <?= (int)($r["brigade_id"] ?? 0) ?>,
              <?= json_encode($r["start_datetime"] ?? "") ?>,
              <?= json_encode($r["end_datetime"] ?? "") ?>
            )'><i data-lucide="pencil"></i></button>

            <?php if ($role == 1): ?>
              <button class="btn delete-btn" onclick="deleteRoute(<?= (int)$r['id'] ?>)">
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

<!-- Модалка этапа -->
<div class="modal" id="routeModal">
  <div class="modal-content modal-lg">
    <div class="modal-header" style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
      <h3 id="routeTitle" style="margin:0;">Добавить этап</h3>
      <button class="btn cancel" type="button" onclick="closeRouteModal()">Закрыть</button>
    </div>

    <form id="routeForm" method="post" action="../scripts/product-routes-handler.php">
      <input type="hidden" name="id" id="routeId">
      <input type="hidden" name="product_id" value="<?= (int)$product_id ?>">

      <div class="grid2">
        <div>
          <label>Участок</label>
          <select name="section_id" id="routeSection" required>
            <option value="">— не выбран —</option>
            <?php foreach ($sections as $s): ?>
              <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>Бригада</label>
          <select name="brigade_id" id="routeBrigade">
            <option value="">— не выбрана —</option>
            <?php foreach ($brigades as $b): ?>
              <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="grid2">
        <div>
          <label>Начало</label>
          <input type="datetime-local" name="start_datetime" id="routeStart" required>
        </div>
        <div>
          <label>Окончание</label>
          <input type="datetime-local" name="end_datetime" id="routeEnd">
        </div>
      </div>

      <div class="modal-actions">
        <button class="btn add-btn" type="submit" name="save"><i data-lucide="save"></i> Сохранить</button>
        <button class="btn cancel" type="button" onclick="closeRouteModal()">Отмена</button>
      </div>
    </form>
  </div>
</div>

<script>
function openRouteModal(){
  const f = document.getElementById('routeForm');
  f.reset();
  document.getElementById('routeId').value = '';
  document.getElementById('routeTitle').textContent = 'Добавить этап';
  document.getElementById('routeModal').style.display = 'flex';
  if (window.lucide) lucide.createIcons();
}
function closeRouteModal(){
  document.getElementById('routeModal').style.display = 'none';
}
function editRoute(id, sectionId, brigadeId, startDT, endDT){
  document.getElementById('routeId').value = id;
  document.getElementById('routeTitle').textContent = 'Редактировать этап';

  document.getElementById('routeSection').value = sectionId || '';
  document.getElementById('routeBrigade').value = brigadeId || '';

  // для input[type=datetime-local] нужен формат YYYY-MM-DDTHH:MM
  const s = (startDT || '').replace(' ', 'T').slice(0,16);
  const e = (endDT   || '').replace(' ', 'T').slice(0,16);
  document.getElementById('routeStart').value = s;
  document.getElementById('routeEnd').value   = e;

  document.getElementById('routeModal').style.display = 'flex';
  if (window.lucide) lucide.createIcons();
}
function deleteRoute(id){
  if (!confirm('Удалить этап?')) return;
  const pid = <?= (int)$product_id ?>;
  window.location.href = '../scripts/product-routes-handler.php?delete='+id+'&product_id='+pid;
}
if (window.lucide) lucide.createIcons();
</script>

<?php include '../includes/footer.php'; ?>
