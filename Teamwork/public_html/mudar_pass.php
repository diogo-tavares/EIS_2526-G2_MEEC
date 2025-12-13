<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'php/db.php';
require_once 'php/auth.php';
require_once 'php/get_profile_pic.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $user_id = (int)($_SESSION['user_id'] ?? 0);
    $current = (string)($_POST['current_password'] ?? '');
    $new1    = (string)($_POST['new_password'] ?? '');
    $new2    = (string)($_POST['confirm_password'] ?? '');

    if ($new1 === '' || strlen($new1) < 8) { header('Location: mudar_pass.php?err=weak'); exit(); }
    if ($new1 !== $new2) { header('Location: mudar_pass.php?err=nomatch'); exit(); }

    // Buscar password atual (coluna users.password)
    $stmt = $conn->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($secret);
    if (!$stmt->fetch()) { $stmt->close(); header('Location: mudar_pass.php?err=user'); exit(); }
    $stmt->close();

    if (!hash_equals((string)$secret, $current)) { header('Location: mudar_pass.php?err=pass'); exit(); }

    // Atualizar password (texto simples, compatível com a tua BD atual)
    $stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
    $stmt->bind_param('si', $new1, $user_id);
    $stmt->execute();
    $stmt->close();

    header('Location: perfil.php?changed=password');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mudar palavra-passe</title>
  <link rel="stylesheet" href="css/style.css">
  <script src="js/pesquisa.js" defer></script>
</head>
<body>
    
    <header class="top-bar-home">
        <div class="logo">
            <a href="homepage.php"><img src="images/logo.png" alt="Logo"></a>
        </div>
        
        <div class="search-bar">
    
    <div class="search-input-wrapper">
        <input type="text" id="live-search-input" placeholder="🔍 Pesquisar..." autocomplete="off">
        <div id="search-results" class="search-results-list"></div>
    </div>

    <a href="social.php" class="social-hub-btn">
        <span class="social-hub-icon">🌍</span>
        <span class="social-hub-text">Social Hub</span>
    </a>

</div>

        <div class="user-icon">
            <a href="perfil.php">
                <img src="<?php echo htmlspecialchars($user_photo); ?>" alt="Perfil" height="90" style="border-radius: 50%; object-fit: cover; width: 90px;">
            </a>
        </div>
    </header>
    
    
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
