<?php
// php/login_process.php (compat with legacy 'password' column or modern 'password_hash')
declare(strict_types=1);

// Remember-me cookie params
$remember = isset($_POST['remember']) && $_POST['remember'] === '1';
$lifetime = $remember ? (60 * 60 * 24 * 30) : 0;

session_set_cookie_params([
    'lifetime' => $lifetime,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();
require_once __DIR__ . '/db.php';

$email = trim($_POST['email'] ?? '');
$password_input = $_POST['password'] ?? '';

if ($email === '' || $password_input === '') {
    header('Location: ../login.php?error=empty');
    exit();
}

// Detect column name (password_hash vs password)
$col = 'password_hash';
$col_check = $conn->query("SHOW COLUMNS FROM users LIKE 'password_hash'");
if (!$col_check || $col_check->num_rows === 0) {
    $col = 'password'; // legacy column
}

$sql = "SELECT id, name, email, " . $col . " AS secret, photo_path FROM users WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    header('Location: ../login.php?error=server');
    exit();
}
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: ../login.php?error=notfound');
    exit();
}

$ok = false;
if ($col === 'password_hash') {
    $ok = password_verify($password_input, (string)$user['secret']);
} else {
    // Legacy fallback (não recomendado). Ajusta se usavas MD5/SHA1.
    $ok = hash_equals((string)$user['secret'], (string)$password_input);
}

if (!$ok) {
    header('Location: ../login.php?error=invalid');
    exit();
}

// Success
session_regenerate_id(true);
$_SESSION['user_id']   = (int)$user['id'];
$_SESSION['user_name'] = $user['name'] ?? '';
$_SESSION['user_email']= $user['email'] ?? '';
if (!empty($user['photo_path'])) {
    $_SESSION['photo_path'] = $user['photo_path'];
}

// Redirect to eventos.php next to public_html
header('Location: ../eventos.php');
exit();
