<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth.php';

// foto por defeito (podes ter get_profile_pic.php a definir $user_photo)
$user_photo = 'images/profile.png';
if (file_exists(__DIR__ . '/php/get_profile_pic.php')) {
    require_once __DIR__ . '/php/get_profile_pic.php';
    // se $user_photo vier definido lá e existir na filesystem, fica esse
}

$user_id = (int)($_SESSION['user_id'] ?? 0);

// ---- Utilizador ----
$user = [
    'name'       => '',
    'email'      => '',
    'birthdate'  => null,
    'created_at' => null,
    'photo_path' => null,
];

if ($stmt = $conn->prepare("SELECT name, email, birthdate, created_at, photo_path FROM users WHERE id = ? LIMIT 1")) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $user = $row;
        if (!empty($row['photo_path'])) {
            $user_photo = $row['photo_path'];
        }
    }
    $stmt->close();
}

// ---- Nº de coleções do utilizador ----
$collections_count = 0;
if ($stmt = $conn->prepare("SELECT COUNT(*) FROM collections WHERE user_id = ?")) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($collections_count);
    $stmt->fetch();
    $stmt->close();
}

// helpers de data
$birth_str  = !empty($user['birthdate'])  ? date('d/m/Y', strtotime((string)$user['birthdate'])) : '';
$reg_str    = !empty($user['created_at']) ? date('d/m/Y', strtotime((string)$user['created_at'])) : '';
$name_str   = (string)($user['name'] ?? '');
$email_str  = (string)($user['email'] ?? '');
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hub de Coleções</title>
    <link rel="stylesheet" href="css/style.css?v=2">
    <script src="js/profile.js" defer></script>
</head>
<body>

    <!-- Barra superior -->
    <header class="top-bar-home">
        <div class="logo">
            <a href="homepage.php">
                <img src="images/logo.png" alt="Logo do Sistema">
            </a>
        </div>
        <div class="search-bar">
            <input type="text" placeholder="Pesquisar por coleções, eventos ou tags">
            <button>🔍</button>
        </div>
        <div class="user-icon">
            <a href="perfil.php">
                <img src="<?php echo htmlspecialchars($user_photo); ?>" alt="Perfil" height="90">
            </a>
        </div>
    </header>

    <!-- Conteúdo principal -->
    <main class="perfil-content">
        <!-- Título do perfil -->
        <h1>Perfil:</h1>

        <!-- Container da imagem + informações -->
        <div class="main-content">
            <div class="perfil-container">
                <!-- Imagem do usuário -->
                <div class="perfil-img">
                    <img id="profile-img" src="<?php echo htmlspecialchars($user_photo); ?>" alt="Foto do usuário" height="150">
                </div>

                <!-- Informações do usuário -->
                <div class="perfil-info">
                    <p><strong>Nome: </strong><?php echo htmlspecialchars($name_str ?: 'Primeiro Ultimo'); ?></p>
                    <p><strong>E-mail: </strong><span id="email-display"><?php echo htmlspecialchars($email_str ?: 'email@email.com'); ?></span></p>
                    <p><strong>Data de nascimento: </strong><span id="birthdate-display"><?php echo htmlspecialchars($birth_str ?: ''); ?></span></p>
                    <p><strong>Data de registo: </strong><?php echo htmlspecialchars($reg_str ?: ''); ?></p>
                    <p><strong>Número de coleções: </strong><?php echo (int)$collections_count; ?></p>

                    <div class="perfil-buttons">
                        <button id="edit-profile-btn" class="btn-primary">Editar perfil</button>
                        <button id="change-email-btn" class="btn-primary"> Alterar e-mail</button>
                        <button id="change-pass-btn" class="btn-primary">Alterar palavra-passe</button>
                    </div>
                    
                    <button class="btn-danger" onclick="window.location.href='php/logout.php'">Terminar Sessão</button>
                </div>
            </div>
        </div>
    </main>

    <!-- Barra inferior -->
    <footer class="bottom-bar">
        <a href="desenvolvedores.php">DESENVOLVEDORES</a>
    </footer>

</body>
</html>
