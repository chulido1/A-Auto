<?php
require_once '../config/db.php';
require_once '../includes/auth.php';   // функции require_auth(), can()
require_auth();                        // пускаем только авторизованных

include '../includes/header.php';

// ---- Поиск + выборка
$q = trim($_GET['q'] ?? '');
$params = [];

$sql = "SELECT e.id,
               CONCAT(e.last_name, ' ', e.first_name, ' ', COALESCE(e.patronymic,'')) AS fio,
               e.position, e.category, e.profession, e.grade, e.experience_years,
               e.phone, e.email, e.hire_date, e.status,
               d.name AS dep_name,
               s.name AS sec_name,
               b.name AS brig_name
        FROM employees e
        LEFT JOIN departments d ON d.id = e.department_id
        LEFT JOIN sections    s ON s.id = e.section_id
        LEFT JOIN brigades    b ON b.id = e.brigade_id";

if ($q !== '') {
  $sql .= " WHERE e.last_name LIKE :q1 OR e.first_name LIKE :q2
                     OR e.position LIKE :q3 OR e.profession LIKE :q4
                     OR d.name LIKE :q5 OR s.name LIKE :q6 OR b.name LIKE :q7";
  $params = [
    ':q1' => "%$q%", ':q2' => "%$q%", ':q3' => "%$q%",
    ':q4' => "%$q%", ':q5' => "%$q%", ':q6' => "%$q%", ':q7' => "%$q%"
  ];
}

$sql .= " ORDER BY e.id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---- Справочники для селектов
$deps = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$secs = $pdo->query("SELECT s.id, CONCAT(d.name,' → ',s.name) AS name
                     FROM sections s JOIN departments d ON d.id=s.department_id
                     ORDER BY d.name, s.name")->fetchAll(PDO::FETCH_ASSOC);
$brigs = $pdo->query("SELECT b.id, CONCAT(s.name,' → ',b.name) AS name
                      FROM brigades b JOIN sections s ON s.id=b.section_id
                      ORDER BY s.name, b.name")->fetchAll(PDO::FETCH_ASSOC);
?>

<section>
  <h2>Кадры и бригады</h2>
  <p>Учёт сотрудников и их привязка к цехам, участкам и бригадам. Доступные действия зависят от роли.</p>

  <div class="actions-bar">
    <form class="search-form" method="get">
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Поиск: ФИО / должность / цех / участок / бригада">
      <button class="btn search-btn" type="submit">
        <i data-lucide="search"></i> Найти
      </button>
    </form>

    <div style="display:flex; gap:10px;">
      <?php if (can('employees.create')): ?>
        <button type="button" class="btn add-btn" id="btnAddEmp">
          <i data-lucide="plus"></i> Добавить сотрудника
        </button>
      <?php endif; ?>

      <?php if (can('brigades.view')): ?>
        <button type="button" class="btn small-btn" id="btnOpenBrigades">
          <i data-lucide="users"></i> Бригады
        </button>
      <?php endif; ?>
    </div>
  </div>

  <div class="table-wrap">
    <table class="table-employees">
      <thead>
        <tr>
          <th>ID</th>
          <th>ФИО</th>
          <th>Должность</th>
          <th>Категория</th>
          <th>Профессия/разряд</th>
          <th>Стаж (лет)</th>
          <th>Цех</th>
          <th>Участок</th>
          <th>Бригада</th>
          <th>Статус</th>
          <th>Действия</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$rows): ?>
        <tr><td colspan="11">Сотрудников не найдено.</td></tr>
      <?php else: foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?= htmlspecialchars($r['fio']) ?></td>
          <td><?= htmlspecialchars($r['position']) ?></td>
          <td><?= htmlspecialchars($r['category']) ?></td>
          <td><?= htmlspecialchars(trim(($r['profession']??'').' '.($r['grade']??''))) ?></td>
          <td><?= htmlspecialchars($r['experience_years'] ?? '') ?></td>
          <td><?= htmlspecialchars($r['dep_name'] ?? '') ?></td>
          <td><?= htmlspecialchars($r['sec_name'] ?? '') ?></td>
          <td><?= htmlspecialchars($r['brig_name'] ?? '') ?></td>
          <td><?= htmlspecialchars($r['status']) ?></td>
          <td class="actions">
            <?php if (can('employees.edit')): ?>
              <button type="button" class="btn edit-btn" onclick='editEmp(
                <?= (int)$r["id"] ?>,
                <?= json_encode($r["fio"]) ?>,
                <?= json_encode($r["position"]) ?>,
                <?= json_encode($r["category"]) ?>,
                <?= json_encode($r["profession"]) ?>,
                <?= json_encode($r["grade"]) ?>,
                <?= json_encode($r["experience_years"]) ?>,
                <?= json_encode($r["phone"]) ?>,
                <?= json_encode($r["email"]) ?>,
                <?= json_encode($r["dep_name"]) ?>,
                <?= json_encode($r["sec_name"]) ?>,
                <?= json_encode($r["brig_name"]) ?>,
                <?= json_encode($r["hire_date"]) ?>,
                <?= json_encode($r["status"]) ?>
              )'>
                <i data-lucide="pencil"></i>
              </button>
            <?php endif; ?>

            <?php if (can('employees.delete')): ?>
              <button type="button" class="btn delete-btn" onclick="deleteEmp(<?= (int)$r['id'] ?>)">
                <i data-lucide="trash-2"></i>
              </button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- Модалка сотрудника -->
<div class="modal" id="empModal">
  <div class="modal-content modal-lg">
    <h3 id="empTitle">Добавить сотрудника</h3>
    <form id="empForm" method="post" action="../scripts/employees-handler.php">
      <input type="hidden" name="id" id="empId">

      <div class="grid2">
        <div>
          <label>Фамилия</label>
          <input type="text" name="last_name" id="empLast" required>
        </div>
        <div>
          <label>Имя</label>
          <input type="text" name="first_name" id="empFirst" required>
        </div>
      </div>

      <label>Отчество</label>
      <input type="text" name="patronymic" id="empPatr">

      <div class="grid3">
        <div>
          <label>Должность</label>
          <input type="text" name="position" id="empPosition">
        </div>
        <div>
          <label>Категория</label>
          <select name="category" id="empCategory">
            <option value="ИТП">ИТП</option>
            <option value="Рабочий">Рабочий</option>
          </select>
        </div>
        <div>
          <label>Профессия</label>
          <input type="text" name="profession" id="empProfession">
        </div>
      </div>

      <div class="grid3">
        <div>
          <label>Разряд</label>
          <input type="text" name="grade" id="empGrade">
        </div>
        <div>
          <label>Стаж (лет)</label>
          <input type="number" name="experience_years" id="empExp" min="0">
        </div>
        <div>
          <label>Дата приёма</label>
          <input type="date" name="hire_date" id="empHire">
        </div>
      </div>

      <div class="grid2">
        <div>
          <label>Телефон</label>
          <input type="text" name="phone" id="empPhone">
        </div>
        <div>
          <label>Email</label>
          <input type="email" name="email" id="empEmail">
        </div>
      </div>

      <div class="grid3">
        <div>
          <label>Цех</label>
          <select name="department_id" id="empDep">
            <option value="">— не выбран —</option>
            <?php foreach ($deps as $d): ?>
              <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>Участок</label>
          <select name="section_id" id="empSec">
            <option value="">— не выбран —</option>
            <?php foreach ($secs as $s): ?>
              <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>Бригада</label>
          <select name="brigade_id" id="empBrig">
            <option value="">— не выбрана —</option>
            <?php foreach ($brigs as $b): ?>
              <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <label>Статус</label>
      <select name="status" id="empStatus">
        <option value="работает">работает</option>
        <option value="уволен">уволен</option>
      </select>

      <div class="modal-actions">
        <button class="btn add-btn" type="submit" name="save"><i data-lucide="save"></i> Сохранить</button>
        <button class="btn cancel" type="button" onclick="closeEmpModal()">Отмена</button>
      </div>
    </form>
  </div>
</div>

<!-- Модалка бригад -->
<?php if (can('brigades.view')): ?>
<div class="modal" id="brigadesModal">
  <div class="modal-content modal-lg">
    <h3>Бригады</h3>
    <div id="brigadesWrap" style="max-height:420px; overflow-y:auto; margin-top:12px;"></div>
    <div class="modal-actions">
      <button class="btn cancel" type="button" onclick="closeBrigadesModal()">Закрыть</button>
    </div>
  </div>
</div>
<?php endif; ?>

<style>
  .grid2{display:grid; grid-template-columns:1fr 1fr; gap:12px;}
  .grid3{display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;}
  @media (max-width:900px){ .grid2,.grid3{grid-template-columns:1fr;} }
  .table-wrap{ width:100%; overflow-x:auto; border-radius:10px; }
  .table-employees{ min-width:1080px; }
  .actions { white-space:nowrap; display:flex; gap:8px; }
</style>

<?php include '../includes/footer.php'; ?>
departments-handler.php