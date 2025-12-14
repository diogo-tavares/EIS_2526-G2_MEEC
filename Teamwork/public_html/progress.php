<?php
session_start();
require_once 'php/db.php';
require_once 'php/auth.php';
require_once 'php/get_profile_pic.php';

// Só entrar logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* -------------------------------
   Buscar número de coleções
--------------------------------*/
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM collections WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

/* -------------------------------
   Determinar label atual e próxima
--------------------------------*/
if ($total == 0) {
    $current_label = "Sem badge";
    $next_label = "Silver";
    $needed = 1;
} elseif ($total <= 2) {
    $current_label = "Silver";
    $next_label = "Gold";
    $needed = 3 - $total;
} elseif ($total <= 4) {
    $current_label = "Gold";
    $next_label = "Diamond";
    $needed = 5 - $total;
} else {
    $current_label = "Diamond";
    $next_label = "— já atingiu o nível máximo!";
    $needed = 0;
}

/* -------------------------------
   Mapear labels para classes CSS
--------------------------------*/
function badge_class_from_label(string $label): ?string {
    switch ($label) {
        case 'Silver':  return 'badge-silver';
        case 'Gold':    return 'badge-gold';
        case 'Diamond': return 'badge-diamond';
        default:        return null; // "Sem badge" ou texto normal
    }
}

$current_badge_class = badge_class_from_label($current_label);
$next_badge_class    = badge_class_from_label($next_label);

/* -------------------------------
   Calcular percentagem para barra
--------------------------------*/
$max = 5;
$progress = min(100, ($total / $max) * 100);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Progresso do Utilizador</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/pesquisa.js" defer></script>

    <style>
        .progress-wrapper {
            max-width: 700px;
            margin: 50px auto 80px auto;
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 6px 14px rgba(0,0,0,0.15);
            text-align: center;
        }

        .progress-bar-bg {
            width: 100%;
            height: 25px;
            background: #e0e0e0;
            border-radius: 20px;
            margin-top: 20px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            width: <?= $progress ?>%;
            background: linear-gradient(135deg, #35d0e0, #63e9f7, #a7f7ff);
            border-radius: 20px;
            transition: width 0.5s;
        }

        .progress-wrapper h1 {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

<header class="top-bar-home">
    <div class="logo">
        <a href="homepage.php">
            <img src="images/logo.png" alt="Logo">
        </a>
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

<div class="progress-wrapper">
    <h1>Progresso de Coleções</h1>

    <h2>
        Nível atual:
        <?php if ($current_badge_class): ?>
            <span class="user-badge <?= $current_badge_class ?>">
                <?= htmlspecialchars($current_label) ?>
            </span>
        <?php else: ?>
            <strong><?= htmlspecialchars($current_label) ?></strong>
        <?php endif; ?>
    </h2>

    <p style="margin-top:15px;">
        Coleções: <strong><?= $total ?></strong>
    </p>

    <?php if ($needed > 0): ?>
        <p style="margin-top:8px;">
            Faltam <strong><?= $needed ?></strong> coleções para atingir
            <?php if ($next_badge_class): ?>
                <span class="user-badge <?= $next_badge_class ?>">
                    <?= htmlspecialchars($next_label) ?>
                </span>
            <?php else: ?>
                <strong><?= htmlspecialchars($next_label) ?></strong>
            <?php endif; ?>
        </p>
    <?php else: ?>
        <p style="margin-top:8px;">
            🎉 Atingiste o nível máximo!
            <span class="user-badge badge-diamond">Diamond</span> 💎
        </p>
    <?php endif; ?>

    <div class="progress-bar-bg">
        <div class="progress-bar-fill"></div>
    </div>

    <button onclick="window.location.href='perfil.php'" class="btn-primary" style="margin-top:25px;">
        Voltar ao Perfil
    </button>
</div>

<footer class="bottom-bar">
    <a href="desenvolvedores.php">DESENVOLVEDORES</a>
</footer>

</body>
</html>