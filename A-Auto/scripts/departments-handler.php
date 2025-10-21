<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_auth(); // пускаем только авторизованных

// === УДАЛЕНИЕ ===
if (isset($_GET['delete'])) {
    if (!can('departments.delete')) {
        http_response_code(403);
        exit('Недостаточно прав для удаления цехов.');
    }

    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM departments WHERE id=?");
    $stmt->execute([$id]);

    header("Location: ../public/departments.php");
    exit;
}

// === ДОБАВЛЕНИЕ / РЕДАКТИРОВАНИЕ ===
if (isset($_POST['save'])) {
    $id    = $_POST['id'] ?? null;
    $name  = trim($_POST['name'] ?? '');
    $chief = trim($_POST['chief'] ?? '');
    $desc  = trim($_POST['description'] ?? '');

    // Проверяем права в зависимости от действия
    if ($id) {
        if (!can('departments.edit')) {
            http_response_code(403);
            exit('Недостаточно прав для редактирования цехов.');
        }
        $stmt = $pdo->prepare("UPDATE departments SET name=?, chief=?, description=? WHERE id=?");
        $stmt->execute([$name, $chief, $desc, $id]);
    } else {
        if (!can('departments.create')) {
            http_response_code(403);
            exit('Недостаточно прав для добавления цехов.');
        }
        $stmt = $pdo->prepare("INSERT INTO departments (name, chief, description) VALUES (?, ?, ?)");
        $stmt->execute([$name, $chief, $desc]);
    }

    header("Location: ../public/departments.php");
    exit;
}

http_response_code(400);
echo "Некорректный запрос.";


