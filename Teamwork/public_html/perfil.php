<?php
session_start();
require_once 'php/db.php';
require_once 'php/auth.php';
require_once 'php/get_profile_pic.php';

// Garante que só entra quem está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* -------------------------------------------------------
   1. OBTER INFO DO UTILIZADOR
---------------------------------------------------------*/
$stmt = $conn->prepare("
    SELECT name, email, birthdate, created_at 
    FROM users 
    WHERE id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Formatar datas (opcional)
$birthdate_formatted = '';
$register_date_formatted = '';

if (!empty($user_data['birthdate'])) {
    $birthdate_formatted = (new DateTime($user_data['birthdate']))->format('d/m/Y');
}

if (!empty($user_data['created_at'])) {
    $register_date_formatted = (new DateTime($user_data['created_at']))->format('d/m/Y');
}

/* -------------------------------------------------------
   2. CONTAR COLEÇÕES DO UTILIZADOR (PARA O BADGE)
---------------------------------------------------------*/
$stmt2 = $conn->prepare("SELECT COUNT(*) AS total FROM collections WHERE user_id = ?");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$rowCount = $stmt2->get_result()->fetch_assoc();
$stmt2->close();

$total_collections = (int) $rowCount['total'];

// Lógica do badge
$badge_class = null;
$badge_label = null;

if ($total_collections >= 1 && $total_collections <= 2) {
    $badge_class = 'badge-silver';
    $badge_label = 'Silver';
} elseif ($total_collections >= 3 && $total_collections <= 4) {
    $badge_class = 'badge-gold';
    $badge_label = 'Gold';
} elseif ($total_collections >= 5) {
    $badge_class = 'badge-diamond';
    $badge_label = 'Diamond';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hub de Coleções - Perfil</title>

    <link rel="stylesheet" href="css/style.css?v=3">
    <script src="js/profile.js" defer></script>
    <script src="js/pesquisa.js" defer></script>
</head>
<body>

    <!-- Barra superior -->
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

    <!-- Conteúdo principal -->
    <main class="perfil-content">
        <h1>Perfil:</h1>

        <div class="main-content">
            <div class="perfil-container">
                
                <!-- Imagem do usuário + badge -->
                <div class="perfil-img">
                    <img 
                        id="profile-img" 
                        src="<?= htmlspecialchars($user_photo ?? 'images/profile.png') ?>" 
                        alt="Foto do usuário" 
                        width="150" 
                        height="150"
                        style="border-radius:50%; object-fit:cover;"
                    >

                    <?php if ($badge_class && $badge_label): ?>
                        <div class="user-badge <?= $badge_class ?>">
                            <?= htmlspecialchars($badge_label) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Informações do usuário -->
                <div class="perfil-info">
                    <p><strong>Nome: </strong><?= htmlspecialchars($user_data['name']) ?></p>
                    <p><strong>E-mail: </strong><?= htmlspecialchars($user_data['email']) ?></p>
                    <p><strong>Data de nascimento: </strong><?= htmlspecialchars($birthdate_formatted) ?></p>
                    <p><strong>Data de registo: </strong><?= htmlspecialchars($register_date_formatted) ?></p>
                    <p><strong>Número de coleções: </strong><?= $total_collections ?></p>

                    <div class="perfil-buttons">
                        <button onclick="window.location.href='editar_perfil.php'" class="btn-primary">Editar perfil</button>
                        <button onclick="window.location.href='mudar_mail.php'" class="btn-primary">Alterar e-mail</button>
                        <button onclick="window.location.href='mudar_pass.php'" class="btn-primary">Alterar palavra-passe</button>
                    </div>
                    
                    <!-- Se tiveres um logout.php, usa-o aqui. Se não, deixa login.php como antes -->
                    <button class="btn-danger" onclick="window.location.href='login.php'">
                        Terminar Sessão
                    </button>
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