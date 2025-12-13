<?php
// php/register_process.php — guarda birthdate e usa password_hash quando disponível
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/db.php';

$name      = trim($_POST['name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = (string)($_POST['password'] ?? '');
$confirm   = (string)($_POST['confirm'] ?? ($_POST['confirm-password'] ?? ''));
$birthdate = trim($_POST['birthdate'] ?? '');

// validação básica
if ($name === '' || $email === '' || $password === '' || $confirm === '') {
    header('Location: ../registar.php?error=empty');
    exit();
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../registar.php?error=email');
    exit();
}
if ($password !== $confirm) {
    header('Location: ../registar.php?error=match');
    exit();
}
// validar data (aceita vazio)
if ($birthdate !== '') {
    $d = date_create_from_format('Y-m-d', $birthdate);
    if (!$d || $d->format('Y-m-d') !== $birthdate) {
        header('Location: ../registar.php?error=date');
        exit();
    }
}

// email único
$stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();
if ($exists) {
    header('Location: ../registar.php?error=exists');
    exit();
}

// descobrir colunas existentes
$has_password_hash = false;
$has_birthdate     = false;

$res = $conn->query("SHOW COLUMNS FROM users LIKE 'password_hash'");
$has_password_hash = $res && $res->num_rows > 0;
$res = $conn->query("SHOW COLUMNS FROM users LIKE 'birthdate'");
$has_birthdate = $res && $res->num_rows > 0;

// construir INSERT consoante as colunas
if ($has_password_hash && $has_birthdate) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO users (name, email, password_hash, birthdate) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $name, $email, $hash, $birthdate);
} elseif ($has_password_hash && !$has_birthdate) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $name, $email, $hash);
} elseif (!$has_password_hash && $has_birthdate) {
    // legado sem hashing (não recomendado)
    $stmt = $conn->prepare('INSERT INTO users (name, email, password, birthdate) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $name, $email, $password, $birthdate);
} else {
    $stmt = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $name, $email, $password);
}

$stmt->execute();
$uid = $stmt->insert_id;
$stmt->close();

// abrir sessão e redirecionar
session_regenerate_id(true);
$_SESSION['user_id']    = (int)$uid;
$_SESSION['user_name']  = $name;
$_SESSION['user_email'] = $email;

header('Location: ../homepage.php');
exit();