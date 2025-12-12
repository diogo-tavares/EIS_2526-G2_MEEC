<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $user_id   = (int)($_SESSION['user_id'] ?? 0);
    $new_email = trim($_POST['new_email'] ?? '');
    $curr_pass = (string)($_POST['current_password'] ?? '');

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) { header('Location: mudar_mail.php?err=email'); exit(); }

    // e-mail já usado por outro utilizador?
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
    $stmt->bind_param('si', $new_email, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) { $stmt->close(); header('Location: mudar_mail.php?err=exists'); exit(); }
    $stmt->close();

    // confirmar password atual (coluna users.password)
    $stmt = $conn->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($secret);
    if (!$stmt->fetch()) { $stmt->close(); header('Location: mudar_mail.php?err=user'); exit(); }
    $stmt->close();

    if (!hash_equals((string)$secret, $curr_pass)) { header('Location: mudar_mail.php?err=pass'); exit(); }

    // atualizar e-mail
    $stmt = $conn->prepare('UPDATE users SET email = ? WHERE id = ?');
    $stmt->bind_param('si', $new_email, $user_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['user_email'] = $new_email;
    header('Location: perfil.php?changed=email');
    exit();
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
