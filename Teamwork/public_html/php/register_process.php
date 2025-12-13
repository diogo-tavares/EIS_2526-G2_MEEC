<?php
// php/register_process.php
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

require_once 'db.php';

$name      = trim($_POST['name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = (string)($_POST['password'] ?? '');
$confirm   = (string)($_POST['confirm'] ?? ($_POST['confirm-password'] ?? ''));
$birthdate_input = trim($_POST['birthdate'] ?? '');

// 1. Validação de campos de texto obrigatórios
if ($name === '' || $email === '' || $password === '' || $confirm === '') {
    header('Location: ../registar.php?error=empty');
    exit();
}

// 2. Validação ESPECÍFICA da Data de Nascimento (Obrigatória)
if ($birthdate_input === '') {
    header('Location: ../registar.php?error=missing_date');
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

// 3. Validação da Data (Formato e Futuro)
// Como já garantimos que não é vazia acima, podemos validar diretamente
$d = date_create_from_format('Y-m-d', $birthdate_input);

if (!$d || $d->format('Y-m-d') !== $birthdate_input) {
    header('Location: ../registar.php?error=date');
    exit();
}

// Verificar se é data futura
$hoje = new DateTime();
$hoje->setTime(0, 0, 0);
$d->setTime(0, 0, 0);

if ($d > $hoje) {
    header('Location: ../registar.php?error=future_date');
    exit();
}

// Definir a data final para inserção
$final_birthdate = $birthdate_input;

// 4. Validar Email Único
$stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($exists) {
    header('Location: ../registar.php?error=exists');
    exit();
}

// 5. Inserção na Base de Dados
$res = $conn->query("SHOW COLUMNS FROM users LIKE 'password_hash'");
$has_password_hash = $res && $res->num_rows > 0;
$res = $conn->query("SHOW COLUMNS FROM users LIKE 'birthdate'");
$has_birthdate = $res && $res->num_rows > 0;

if ($has_password_hash && $has_birthdate) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO users (name, email, password_hash, birthdate) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $name, $email, $hash, $final_birthdate);

} elseif ($has_password_hash && !$has_birthdate) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $name, $email, $hash);

} elseif (!$has_password_hash && $has_birthdate) {
    $stmt = $conn->prepare('INSERT INTO users (name, email, password, birthdate) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $name, $email, $password, $final_birthdate);

} else {
    $stmt = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $name, $email, $password);
}

$stmt->execute();
$uid = $stmt->insert_id;
$stmt->close();

// 6. Sessão e Redirecionamento
session_regenerate_id(true);
$_SESSION['user_id']    = (int)$uid;
$_SESSION['user_name']  = $name;
$_SESSION['user_email'] = $email;

header('Location: ../homepage.php');
exit();
?>