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
    $current = (string)($_POST['current_password'] ?? '');
    $new1    = (string)($_POST['new_password'] ?? '');
    $new2    = (string)($_POST['confirm_password'] ?? '');

    if ($new1 === '' || strlen($new1) < 8) {
        $errors[] = 'A nova palavra-passe deve ter pelo menos 8 caracteres.';
    }
    if ($new1 !== $new2) {
        $errors[] = 'A confirmação não coincide.';
    }

    // Validar password atual
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
            $ok = $use_hash ? password_verify($current, $secret) : hash_equals($secret, $current);
            if (!$ok) {
                $errors[] = 'Palavra-passe atual incorreta.';
            }
        }
    }

    // Atualizar password
    if (empty($errors)) {
        $use_hash = has_column($conn, 'users', 'password_hash');
        if ($use_hash) {
            $hash = password_hash($new1, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->bind_param('si', $hash, $user_id);
            $stmt->execute();
            $stmt->close();
        } else {
            // Legado (texto simples): não recomendado, mas compatível
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param('si', $new1, $user_id);
            $stmt->execute();
            $stmt->close();
        }
        $success = 'Palavra-passe atualizada com sucesso.';
        header('Location: perfil.php?changed=password');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mudar palavra-passe</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <main class="perfil-content" style="max-width:600px;margin:40px auto;">
    <h1>Alterar palavra-passe</h1>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <?php foreach ($errors as $e): ?><p><?php echo htmlspecialchars($e); ?></p><?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="post" action="mudar_pass.php" class="auth-form">
      <label for="current_password">Palavra-passe atual</label>
      <input type="password" id="current_password" name="current_password" required>

      <label for="new_password">Nova palavra-passe</label>
      <input type="password" id="new_password" name="new_password" minlength="8" required>

      <label for="confirm_password">Confirmar nova palavra-passe</label>
      <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>

      <div style="margin-top:12px;">
        <button type="submit" class="btn-primary">Guardar</button>
        <a class="btn-secondary" href="perfil.php">Cancelar</a>
      </div>
    </form>
  </main>
</body>
</html>
