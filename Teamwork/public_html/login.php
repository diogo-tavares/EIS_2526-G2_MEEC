<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hub de Coleções</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Barra superior -->
    <header class="top-bar">
    <div class="logo">
        <a href="login.php"> <!-- será alterado -->
            <img src="images/logo.png" alt="Logo do Sistema">
        </a>
    </div>
</header>

    <!-- Conteúdo principal -->
    <main class="login-container">
        <h1>Iniciar Sessão</h1>

        <form method="POST" action="php/login_process.php" class="auth-form">
    <input type="hidden" name="redirect" value="<?php echo isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : '/eventos.php'; ?>">
    <label for="email">E-mail</label>
    <input id="email" name="email" type="email" required>

    <label for="password">Password</label>
    <input id="password" name="password" type="password" required>

    <label class="remember">
        <input type="checkbox" name="remember" value="1">
        Manter sessão iniciada
    </label>

    <?php if (isset($_GET['error'])): ?>
      <div class="error">
        <?php
          $errors = [
            'empty' => 'Preencha todos os campos.',
            'notfound' => 'Conta não encontrada.',
            'invalid' => 'Credenciais inválidas.',
            'server' => 'Erro do servidor.',
            'expired' => 'Sessão expirada, volte a iniciar.',
          ];
          $key = $_GET['error'];
          echo isset($errors[$key]) ? $errors[$key] : 'Ocorreu um erro.';
        ?>
      </div>
    <?php endif; ?>

    <button type="submit" class="btn-primary">ENTRAR</button>
</form>

        <p class="register-text">
            Caso ainda não tenha uma conta clique em registar para criar uma.
        </p>
        <a href="registar.php" class="btn-secondary">REGISTAR</a>
    </main>

    <!-- Barra inferior -->
    <footer class="bottom-bar">
        <a href="desenvolvedores_anon.php">DESENVOLVEDORES</a>
    </footer>

    <!-- Ligação ao JavaScript -->
    <script src="js/validation.js"></script>
</body>
</html>