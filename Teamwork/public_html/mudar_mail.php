<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
$errors = [];
$success = '';

function has_column(mysqli $conn, string $table, string $col): bool {
    $stmt = $conn->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $stmt->bind_param('s', $col);
    $stmt->execute();
    $res = $stmt->get_result();
    $ok = $res && $res->num_rows > 0;
    $stmt->close();
    return $ok;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_email = trim($_POST['new_email'] ?? '');
    $curr_pass = (string)($_POST['current_password'] ?? '');

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-mail inválido.';
    }

    // Verificar se novo e-mail já existe noutro utilizador
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
        $stmt->bind_param('si', $new_email, $user_id);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($exists) {
            $errors[] = 'Este e-mail já está a ser utilizado.';
        }
    }

    // Buscar secret para validar password atual
    if (empty($errors)) {
        $use_hash = has_column($conn, 'users', 'password_hash');
        $col = $use_hash ? 'password_hash' : 'password';
        $stmt = $conn->prepare("SELECT $col AS secret FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            $errors[] = 'Utilizador não encontrado.';
        } else {
            $secret = (string)$row['secret'];
            $ok = $use_hash ? password_verify($curr_pass, $secret) : hash_equals($secret, $curr_pass);
            if (!$ok) {
                $errors[] = 'Palavra-passe atual incorreta.';
            }
        }
    }

    // Update email
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->bind_param('si', $new_email, $user_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['user_email'] = $new_email;
        $success = 'E-mail atualizado com sucesso.';
        header('Location: perfil.php?changed=email');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mudar e-mail</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <main class="perfil-content" style="max-width:600px;margin:40px auto;">
    <h1>Alterar e-mail</h1>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <?php foreach ($errors as $e): ?><p><?php echo htmlspecialchars($e); ?></p><?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="post" action="mudar_mail.php" class="auth-form">
      <label for="new_email">Novo e-mail</label>
      <input type="email" id="new_email" name="new_email" required>

      <label for="current_password">Palavra-passe atual</label>
      <input type="password" id="current_password" name="current_password" required>

      <div style="margin-top:12px;">
        <button type="submit" class="btn-primary">Guardar</button>
        <a class="btn-secondary" href="perfil.php">Cancelar</a>
      </div>
    </form>
  </main>
</body>
</html>
