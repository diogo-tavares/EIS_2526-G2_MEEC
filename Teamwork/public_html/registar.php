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
        <h1>Registo</h1>

        <form id="register-form" method="POST" action="php/register_process.php">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" placeholder="Escreva o seu e-mail" required>

        <label for="name">Nome</label>
        <input type="text" id="name" name="name" placeholder="Escreva o primeiro e último nome" required>

        <label for="birthdate">Data de Nascimento</label>
        <input type="date" id="birthdate" name="birthdate">

        <label for="password">Palavra-passe</label>
        <input type="password" id="password" name="password" placeholder="Escreva a sua palavra-passe" required>

        <label for="confirm">Repetir palavra-passe</label>
        <input type="password" id="confirm" name="confirm" placeholder="Repita a sua palavra-passe" required>

        <button type="submit" class="btn-primary">REGISTAR</button>
        </form>

    </main>

    <!-- Barra inferior -->
    <footer class="bottom-bar">
        <a href="desenvolvedores_anon.php">DESENVOLVEDORES</a>
    </footer>

    <!-- Ligação ao JavaScript -->
    <!--    <script src="js/validation.js"></script>    -->
</body>
</html>
