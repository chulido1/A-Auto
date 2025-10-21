<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_auth();

$action = $_GET['action'] ?? '';
$labId = (int)($_GET['lab_id'] ?? 0);

if ($action === 'list' && $labId > 0) {
  $stmt = $pdo->prepare("SELECT id, name, type, serial FROM lab_equipment WHERE lab_id=? ORDER BY id ASC");
  $stmt->execute([$labId]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  ob_start(); ?>
  <table class="table-employees">
    <thead>
      <tr>
        <th>ID</th><th>Название</th><th>Тип</th><th>Серийный номер</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr><td colspan="4">Оборудование не найдено.</td></tr>
      <?php else: foreach ($rows as $r): ?>
        <tr>
          <td><?= $r['id'] ?></td>
          <td><?= htmlspecialchars($r['name']) ?></td>
          <td><?= htmlspecialchars($r['type']) ?></td>
          <td><?= htmlspecialchars($r['serial']) ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
  <?php
  echo ob_get_clean();
  exit;
}

http_response_code(400);
echo "Unsupported action";
