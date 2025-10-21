<?php
require_once '../config/db.php';

if (isset($_POST['save'])) {
  $id  = (int)($_POST['id'] ?? 0);

  $data = [
    ':last'  => trim($_POST['last_name'] ?? ''),
    ':first' => trim($_POST['first_name'] ?? ''),
    ':patr'  => trim($_POST['patronymic'] ?? ''),
    ':pos'   => trim($_POST['position'] ?? ''),
    ':cat'   => $_POST['category'] ?? 'Рабочий',
    ':prof'  => trim($_POST['profession'] ?? ''),
    ':grade' => trim($_POST['grade'] ?? ''),
    ':exp'   => ($_POST['experience_years'] === '' ? null : (int)$_POST['experience_years']),
    ':phone' => trim($_POST['phone'] ?? ''),
    ':email' => trim($_POST['email'] ?? ''),
    ':dep'   => ($_POST['department_id'] === '' ? null : (int)$_POST['department_id']),
    ':sec'   => ($_POST['section_id'] === '' ? null : (int)$_POST['section_id']),
    ':brig'  => ($_POST['brigade_id'] === '' ? null : (int)$_POST['brigade_id']),
    ':hire'  => ($_POST['hire_date'] === '' ? null : $_POST['hire_date']),
    ':st'    => $_POST['status'] ?? 'работает',
  ];

  if ($id > 0) {
    $sql = "UPDATE employees SET
              last_name=:last, first_name=:first, patronymic=:patr,
              position=:pos, category=:cat, profession=:prof, grade=:grade,
              experience_years=:exp, phone=:phone, email=:email,
              department_id=:dep, section_id=:sec, brigade_id=:brig,
              hire_date=:hire, status=:st
            WHERE id=:id";
    $data[':id'] = $id;
  } else {
    $sql = "INSERT INTO employees
            (last_name, first_name, patronymic, position, category, profession, grade,
             experience_years, phone, email, department_id, section_id, brigade_id, hire_date, status)
            VALUES (:last,:first,:patr,:pos,:cat,:prof,:grade,:exp,:phone,:email,:dep,:sec,:brig,:hire,:st)";
  }
  $stmt = $pdo->prepare($sql);
  $stmt->execute($data);
  header('Location: ../public/employees.php'); exit;
}

if (isset($_POST['delete'])) {
  $id = (int)($_POST['id'] ?? 0);
  if ($id>0) {
    $pdo->prepare("DELETE FROM employees WHERE id=:id")->execute([':id'=>$id]);
  }
  header('Location: ../public/employees.php'); exit;
}

http_response_code(400);
echo "Bad request";
