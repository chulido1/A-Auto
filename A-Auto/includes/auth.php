<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Константы ролей
const ROLE_ADMIN    = 1;
const ROLE_ENGINEER = 2;
const ROLE_WORKER   = 3;

function current_user() {
  return $_SESSION['user'] ?? null;
}

function role_id() {
  return $_SESSION['user']['role_id'] ?? null;
}

function is_admin()    { return role_id() === ROLE_ADMIN; }
function is_engineer() { return role_id() === ROLE_ENGINEER; }
function is_worker()   { return role_id() === ROLE_WORKER; }

// Разрешения в виде «возможностей»
function can($ability) {
  $role = role_id();
  $map = [
    'departments.view' => [ROLE_ADMIN, ROLE_ENGINEER, ROLE_WORKER],
    'departments.create' => [ROLE_ADMIN, ROLE_ENGINEER],
    'departments.edit'   => [ROLE_ADMIN, ROLE_ENGINEER],
    'departments.delete' => [ROLE_ADMIN],

    'sections.view'  => [ROLE_ADMIN, ROLE_ENGINEER, ROLE_WORKER],
    'sections.create'=> [ROLE_ADMIN, ROLE_ENGINEER],
    'sections.edit'  => [ROLE_ADMIN, ROLE_ENGINEER],
    'sections.delete'=> [ROLE_ADMIN],

    'brigades.view'  => [ROLE_ADMIN, ROLE_ENGINEER, ROLE_WORKER],
    'brigades.create'=> [ROLE_ADMIN, ROLE_ENGINEER],
    'brigades.edit'  => [ROLE_ADMIN, ROLE_ENGINEER],
    'brigades.delete'=> [ROLE_ADMIN],

    'employees.view' => [ROLE_ADMIN, ROLE_ENGINEER, ROLE_WORKER],
    'employees.create'=>[ROLE_ADMIN],
    'employees.edit'  =>[ROLE_ADMIN],
    'employees.delete'=>[ROLE_ADMIN],

    'products.view'  => [ROLE_ADMIN, ROLE_ENGINEER, ROLE_WORKER],
    'products.create'=>[ROLE_ADMIN, ROLE_ENGINEER],
    'products.edit'  =>[ROLE_ADMIN, ROLE_ENGINEER],
    'products.delete'=>[ROLE_ADMIN],

    'labs.view'   => [ROLE_ADMIN, ROLE_ENGINEER, ROLE_WORKER],
    'labs.create' => [ROLE_ADMIN, ROLE_ENGINEER],
    'labs.edit'   => [ROLE_ADMIN, ROLE_ENGINEER],
    'labs.delete' => [ROLE_ADMIN],

    'users.manage' => [ROLE_ADMIN],
  ];

  return in_array($role, $map[$ability] ?? [], true);
}

// Жёсткая защита страницы
function require_auth() {
  if (empty($_SESSION['user'])) {
    header('Location: /A-Auto/public/login.php');
    exit;
  }
}

function require_ability($ability) {
  require_auth();
  if (!can($ability)) {
    http_response_code(403);
    echo "<h2>Доступ запрещён</h2>";
    exit;
  }
}
