<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'php/db.php';
require_once 'php/auth.php';
require_once 'php/get_profile_pic.php';

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
    
    
  <main class="perfil-content" style="max-width:700px;">
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
      <input type="email" id="new_email" name="new_email" placeholder="Escreva o novo e-mail" required>

      <label for="current_password">Palavra-passe</label>
      <input type="password" id="current_password" name="current_password" placeholder="Escreva a sua palavra-passe" required>

      <div class="edit-profile-buttons" style="margin-top: 15px; justify-content: center;">
        <button type="submit" class="btn-primary">Confirmar</button>
        <button type="button" class="btn-primary" onclick="window.location.href='perfil.php'">Desfazer alterações e voltar atrás</button>
      </div>
    </form>
  </main>
    
    <footer class="bottom-bar">
        <a href="desenvolvedores.php">DESENVOLVEDORES</a>
    </footer>
</body>
</html>
