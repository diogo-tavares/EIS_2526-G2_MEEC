<?php
session_start();
require_once 'php/db.php';
require_once 'php/auth.php';
require_once 'php/get_profile_pic.php'; // Necessário para a foto do utilizador logado no header

if (!isset($_GET['id'])) {
    die("Utilizador não especificado.");
}

$target_user_id = intval($_GET['id']);
$my_id = $_SESSION['user_id'];

// 1. Buscar info do utilizador alvo
$stmt = $conn->prepare("SELECT name, email, photo_path, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $target_user_id);
$stmt->execute();
$target_user = $stmt->get_result()->fetch_assoc();

if (!$target_user) die("Utilizador não encontrado.");

// 2. Buscar coleções PÚBLICAS deste utilizador
$stmt_cols = $conn->prepare("SELECT * FROM collections WHERE user_id = ? AND is_public = 1 ORDER BY created_date DESC");
$stmt_cols->bind_param("i", $target_user_id);
$stmt_cols->execute();
$res_cols = $stmt_cols->get_result();

// 3. Verificar se eu já sigo este user
$is_following = false;
if ($my_id !== $target_user_id) {
    $stmt_follow = $conn->prepare("SELECT 1 FROM user_follows WHERE follower_id = ? AND followed_id = ?");
    $stmt_follow->bind_param("ii", $my_id, $target_user_id);
    $stmt_follow->execute();
    if ($stmt_follow->get_result()->num_rows > 0) {
        $is_following = true;
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Perfil de <?php echo htmlspecialchars($target_user['name']); ?></title>
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
            <img src="<?php echo htmlspecialchars($user_photo); ?>" height="90" style="border-radius: 50%; object-fit: cover; width: 90px;">
        </a>
    </div>
</header>

<main class="perfil-content">
    <div class="perfil-container" style="margin-bottom: 40px;">
        <div class="perfil-img">
            <img src="<?php echo htmlspecialchars(!empty($target_user['photo_path']) ? $target_user['photo_path'] : 'images/profile.png'); ?>" height="150" style="border-radius:50%; object-fit:cover;">
        </div>
        
        <div class="perfil-info">
            <h1><?php echo htmlspecialchars($target_user['name']); ?></h1>
            <p>Membro desde: <?php echo date('d/m/Y', strtotime($target_user['created_at'])); ?></p>
            
            <?php if ($my_id !== $target_user_id): ?>
                <button id="follow-btn" class="btn-primary" onclick="toggleFollow(<?php echo $target_user_id; ?>)">
                    <?php echo $is_following ? 'Deixar de Seguir' : 'Seguir'; ?>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <hr>
    <h2 style="margin-top: 30px;">Coleções Públicas:</h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
        <?php if ($res_cols->num_rows > 0): ?>
            <?php while($col = $res_cols->fetch_assoc()): ?>
                <div class="mini-event-card" style="border-left: 5px solid #ffc107;">
                    <h4><?php echo htmlspecialchars($col['title']); ?></h4>
                    <p><em><?php echo htmlspecialchars($col['description']); ?></em></p>
                    <a href="detalhes_colecao.php?id=<?php echo $col['id']; ?>" class="btn-secondary" style="display:block; text-align:center; margin-top:10px; color:white; text-decoration:none;">Ver Coleção</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Este utilizador ainda não partilhou coleções públicas.</p>
        <?php endif; ?>
    </div>
</main>

<script>
function toggleFollow(userId) {
    const btn = document.getElementById('follow-btn');
    // Faz o pedido AJAX ao PHP que criámos anteriormente
    fetch('php/follow_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ followed_id: userId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (data.action === 'followed') {
                btn.innerText = "Deixar de Seguir";
                btn.style.backgroundColor = "#dc3545"; // Vermelho
            } else {
                btn.innerText = "Seguir";
                btn.style.backgroundColor = "#007bff"; // Azul original
            }
        } else {
            alert(data.message);
        }
    })
    .catch(err => console.error("Erro:", err));
}
</script>

</body>
</html>