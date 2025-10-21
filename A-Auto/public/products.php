<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
include '../includes/header.php';

require_auth();
$user = $_SESSION['user'];
$role = (int)$user['role_id'];

// ===== Параметры фильтрации/поиска/пагинации =====
$q        = trim($_GET['q'] ?? '');
$cat_f    = (int)($_GET['category_id'] ?? 0);
$dep_f    = (int)($_GET['department_id'] ?? 0);
$status_f = trim($_GET['status'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 12;
$offset   = ($page - 1) * $perPage;

$params = [];
$where  = [];

// Поиск по тексту
if ($q !== '') {
  $where[] = "(p.model_name LIKE :q OR p.serial_number LIKE :q2 OR d.name LIKE :q3 OR c.name LIKE :q4)";
  $params[':q']  = "%$q%";
  $params[':q2'] = "%$q%";
  $params[':q3'] = "%$q%";
  $params[':q4'] = "%$q%";
}
// Фильтр категория
if ($cat_f > 0) {
  $where[] = "p.category_id = :cat";
  $params[':cat'] = $cat_f;
}
// Фильтр цех
if ($dep_f > 0) {
  $where[] = "p.department_id = :dep";
  $params[':dep'] = $dep_f;
}
// Фильтр статус
if ($status_f !== '') {
  $where[] = "p.status = :st";
  $params[':st'] = $status_f;
}

$whereSql = $where ? (" WHERE " . implode(" AND ", $where)) : "";

// ===== Получение общего числа строк для пагинации =====
$countSql = "SELECT COUNT(*) 
             FROM products p
             LEFT JOIN departments d ON d.id = p.department_id
             LEFT JOIN sections s   ON s.id = p.current_section_id
             LEFT JOIN categories c ON c.id = p.category_id
             $whereSql";

$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

// ===== Основной запрос списка =====
$listSql = "SELECT 
              p.id, p.model_name, p.serial_number, p.start_date, p.status, p.quantity, p.notes,
              p.category_id, p.department_id, p.current_section_id,
              d.name AS dep_name, s.name AS sec_name, c.name AS cat_name
            FROM products p
            LEFT JOIN departments d ON d.id = p.department_id
            LEFT JOIN sections s   ON s.id = p.current_section_id
            LEFT JOIN categories c ON c.id = p.category_id
            $whereSql
            ORDER BY p.id ASC
            LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($listSql);
foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Справочники
$deps = $pdo->query("SELECT id,name FROM departments ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$secs = $pdo->query("SELECT id,name FROM sections ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$cats = $pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// статусы
$STATUS = [
  'в производстве',
  'ожидает проверки',
  'готово к отправке',
  'передано на упаковку',
  'на испытаниях',
];

// хелпер для построения ссылок пагинации с сохранением фильтров
function buildQuery(array $extra = []) {
  $base = $_GET;
  foreach ($extra as $k=>$v) $base[$k]=$v;
  return '?' . http_build_query($base);
}
?>
<section>
  <h2>Изделия</h2>
  <p>Учёт производимых изделий, с указанием текущего цеха и участка.</p>

  <div class="actions-bar">
    <form class="search-form" method="get" style="display:flex; gap:10px; flex-wrap:wrap;">
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Поиск по модели, номеру, цеху, категории...">

      <select name="category_id" title="Категория">
        <option value="0">Все категории</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $cat_f==$c['id']?'selected':'' ?>>
            <?= htmlspecialchars($c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select name="department_id" title="Цех">
        <option value="0">Все цеха</option>
        <?php foreach ($deps as $d): ?>
          <option value="<?= $d['id'] ?>" <?= $dep_f==$d['id']?'selected':'' ?>>
            <?= htmlspecialchars($d['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select name="status" title="Статус">
        <option value="">Все статусы</option>
        <?php foreach ($STATUS as $st): ?>
          <option value="<?= htmlspecialchars($st) ?>" <?= $status_f===$st?'selected':'' ?>>
            <?= htmlspecialchars($st) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button class="btn search-btn" type="submit"><i data-lucide="search"></i> Найти</button>
    </form>

    <?php if ($role <= 2): ?>
      <button type="button" class="btn add-btn" onclick="openProductModal()">
        <i data-lucide="plus"></i> Добавить изделие
      </button>
    <?php endif; ?>
  </div>

  <div class="table-wrap">
    <table class="table-employees">
      <thead>
        <tr>
          <th>ID</th>
          <th>Модель</th>
          <th>Серийный номер</th>
          <th>Категория</th>
          <th>Цех</th>
          <th>Участок</th>
          <th>Дата начала</th>
          <th>Кол-во</th>
          <th>Статус</th>
          <th>Примечания</th>
          <th>Действия</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="11">Ничего не найдено.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= htmlspecialchars($r['model_name']) ?></td>
            <td><?= htmlspecialchars($r['serial_number']) ?></td>
            <td><?= htmlspecialchars($r['cat_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['dep_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['sec_name'] ?? '') ?></td>
            <td style="text-align:center;"><?= htmlspecialchars($r['start_date']) ?></td>
            <td style="text-align:center;"><?= htmlspecialchars($r['quantity']) ?></td>
            <td style="text-align:center;"><?= htmlspecialchars($r['status']) ?></td>
            <td><?= htmlspecialchars($r['notes']) ?></td>
            <td class="actions">
              <a class="btn small-btn" href="product-routes.php?product_id=<?= (int)$r['id'] ?>" title="Маршрут">
                <i data-lucide="route"></i>
              </a>
              <a class="btn small-btn" href="product-tests.php?product_id=<?= (int)$r['id'] ?>" title="Испытания">
                <i data-lucide="beaker"></i>
              </a>

              <?php if ($role != 3): ?>
                <button class="btn edit-btn"
                  onclick='editProduct(
                    <?= (int)$r["id"] ?>,
                    <?= json_encode($r["model_name"]) ?>,
                    <?= json_encode($r["serial_number"]) ?>,
                    <?= json_encode($r["category_id"] ?? "") ?>,
                    <?= json_encode($r["department_id"] ?? "") ?>,
                    <?= json_encode($r["current_section_id"] ?? "") ?>,
                    <?= json_encode($r["start_date"]) ?>,
                    <?= json_encode($r["quantity"]) ?>,
                    <?= json_encode($r["status"]) ?>,
                    <?= json_encode($r["notes"]) ?>
                  )' title="Редактировать">
                  <i data-lucide="pencil"></i>
                </button>
              <?php endif; ?>

              <?php if ($role == 1): ?>
                <button class="btn delete-btn" onclick="deleteProduct(<?= (int)$r['id'] ?>)" title="Удалить">
                  <i data-lucide="trash-2"></i>
                </button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($totalPages > 1): ?>
    <div style="display:flex; gap:8px; justify-content:center; margin-top:16px;">
      <a class="btn" href="<?= buildQuery(['page'=>1]) ?>" <?= $page==1?'style="pointer-events:none;opacity:.6"':'' ?>>« Первая</a>
      <a class="btn" href="<?= buildQuery(['page'=>max(1,$page-1)]) ?>" <?= $page==1?'style="pointer-events:none;opacity:.6"':'' ?>>‹ Назад</a>
      <span style="display:flex;align-items:center;">Стр. <?= $page ?> / <?= $totalPages ?></span>
      <a class="btn" href="<?= buildQuery(['page'=>min($totalPages,$page+1)]) ?>" <?= $page==$totalPages?'style="pointer-events:none;opacity:.6"':'' ?>>Вперёд ›</a>
      <a class="btn" href="<?= buildQuery(['page'=>$totalPages]) ?>" <?= $page==$totalPages?'style="pointer-events:none;opacity:.6"':'' ?>>Последняя »</a>
    </div>
  <?php endif; ?>
</section>

<!-- Модалка -->
<div class="modal" id="productModal">
  <div class="modal-content modal-lg">
    <h3 id="prodTitle">Добавить изделие</h3>
    <form id="productForm" method="post" action="../scripts/products-handler.php">
      <input type="hidden" name="id" id="prodId">

      <label>Модель изделия</label>
      <input type="text" name="model_name" id="prodModel" required>

      <label>Серийный номер</label>
      <input type="text" name="serial_number" id="prodSerial">

      <div class="grid3">
        <div>
          <label>Категория</label>
          <select name="category_id" id="prodCat" required>
            <option value="">— не выбрана —</option>
            <?php foreach ($cats as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>Цех</label>
          <select name="department_id" id="prodDep">
            <option value="">— не выбран —</option>
            <?php foreach ($deps as $d): ?>
              <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>Участок</label>
          <select name="current_section_id" id="prodSec">
            <option value="">— не выбран —</option>
            <?php foreach ($secs as $s): ?>
              <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="grid3">
        <div><label>Дата начала</label><input type="date" name="start_date" id="prodStart"></div>
        <div><label>Количество</label><input type="number" name="quantity" id="prodQty" min="1"></div>
        <div>
          <label>Статус</label>
          <select name="status" id="prodStatus">
            <?php foreach ($STATUS as $st): ?>
              <option value="<?= htmlspecialchars($st) ?>"><?= htmlspecialchars($st) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <label>Примечания</label>
      <textarea name="notes" id="prodNotes" rows="3"></textarea>

      <div class="modal-actions">
        <button type="submit" name="save" class="btn add-btn"><i data-lucide="save"></i> Сохранить</button>
        <button type="button" class="btn cancel" onclick="closeProductModal()">Отмена</button>
      </div>
    </form>
  </div>
</div>

<script src="../assets/js/script.js"></script>
<script> if (window.lucide) lucide.createIcons(); </script>
<?php include '../includes/footer.php'; ?>
