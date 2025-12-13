<?php
session_start();
require_once 'php/db.php';
require_once 'php/auth.php';
require_once 'php/get_profile_pic.php';

$my_id = $_SESSION['user_id'];

// --- QUERY 1: Quem eu sigo (Following) ---
// Inclui subquery para contar quantas coleções cada pessoa tem
$sql_following = "
    SELECT u.id, u.name, u.photo_path, 
           (SELECT COUNT(*) FROM collections WHERE user_id = u.id) as num_cols
    FROM user_follows uf
    JOIN users u ON uf.followed_id = u.id
    WHERE uf.follower_id = ?
    ORDER BY uf.created_at DESC";

$stmt = $conn->prepare($sql_following);
$stmt->bind_param("i", $my_id);
$stmt->execute();
$res_following = $stmt->get_result();
$count_following = $res_following->num_rows;

// --- QUERY 2: Quem me segue (Followers) ---
// Inclui verificação se eu já sigo de volta (do_i_follow)
$sql_followers = "
    SELECT u.id, u.name, u.photo_path,
           (SELECT COUNT(*) FROM collections WHERE user_id = u.id) as num_cols,
           (SELECT COUNT(*) FROM user_follows WHERE follower_id = ? AND followed_id = u.id) as do_i_follow
    FROM user_follows uf
    JOIN users u ON uf.follower_id = u.id
    WHERE uf.followed_id = ?
    ORDER BY uf.created_at DESC";

$stmt2 = $conn->prepare($sql_followers);
$stmt2->bind_param("ii", $my_id, $my_id);
$stmt2->execute();
$res_followers = $stmt2->get_result();
$count_followers = $res_followers->num_rows;
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>A Minha Rede</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/pesquisa.js" defer></script>
    <style>
        /* Estilos específicos para esta página */
        .stats-bar {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .stat-item {
            flex: 1;
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #007bff;
            display: block;
        }
        .stat-label {
            color: #666;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 1px;
        }
        .network-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 50px;
        }
        .user-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: transform 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .user-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            border: 3px solid #f8f9fa;
        }
        .user-card h3 {
            margin: 5px 0;
            font-size: 1.1em;
            color: #333;
        }
        .user-meta {
            font-size: 0.9em;
            color: #777;
            margin-bottom: 15px;
        }
        .btn-unfollow {
            background-color: #ffebee;
            color: #dc3545;
            border: 1px solid #dc3545;
        }
        .btn-unfollow:hover {
            background-color: #dc3545;
            color: white;
        }
        .section-title {
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #444;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>

<header class="top-bar-home">
    <div class="logo"><a href="homepage.php"><img src="images/logo.png" alt="Logo"></a></div>
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

<main class="main-content" style="display: block; max-width: 1000px;">
    
    <h1>A Minha Rede</h1>
    
    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-number"><?php echo $count_following; ?></span>
            <span class="stat-label">A Seguir</span>
        </div>
        <div class="stat-item" style="border-left: 1px solid #eee;">
            <span class="stat-number"><?php echo $count_followers; ?></span>
            <span class="stat-label">Seguidores</span>
        </div>
    </div>

    <h2 class="section-title">📤 Pessoas que segues</h2>
    
    <?php if ($count_following > 0): ?>
        <div class="network-grid">
            <?php while($user = $res_following->fetch_assoc()): ?>
                <div class="user-card" id="card-user-<?php echo $user['id']; ?>">
                    <img src="<?php echo htmlspecialchars(!empty($user['photo_path']) ? $user['photo_path'] : 'images/profile.png'); ?>">
                    
                    <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                    <p class="user-meta">📚 <?php echo $user['num_cols']; ?> Coleções</p>
                    
                    <div style="display: flex; gap: 10px; width: 100%;">
                        <a href="perfil_publico.php?id=<?php echo $user['id']; ?>" class="btn-primary" style="flex: 1; font-size: 0.9em; padding: 8px;">Ver Perfil</a>
                        
                        <button class="btn-secondary btn-unfollow" style="flex: 1; font-size: 0.9em; padding: 8px;" 
                                onclick="toggleFollow(<?php echo $user['id']; ?>, 'unfollow')">
                            Deixar
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p style="margin-bottom: 40px; color: #666;">Ainda não segues ninguém. <a href="social.php">Explora o Social Hub</a> para encontrar pessoas.</p>
    <?php endif; ?>


    <h2 class="section-title">📥 Pessoas que te seguem</h2>

    <?php if ($count_followers > 0): ?>
        <div class="network-grid">
            <?php while($follower = $res_followers->fetch_assoc()): ?>
                <div class="user-card">
                    <img src="<?php echo htmlspecialchars(!empty($follower['photo_path']) ? $follower['photo_path'] : 'images/profile.png'); ?>">
                    
                    <h3><?php echo htmlspecialchars($follower['name']); ?></h3>
                    <p class="user-meta">📚 <?php echo $follower['num_cols']; ?> Coleções</p>
                    
                    <div style="display: flex; gap: 10px; width: 100%;">
                        <a href="perfil_publico.php?id=<?php echo $follower['id']; ?>" class="btn-primary" style="flex: 1; font-size: 0.9em; padding: 8px;">Ver Perfil</a>
                        
                        <?php if ($follower['do_i_follow']): ?>
                            <button class="btn-secondary" disabled style="flex: 1; font-size: 0.9em; padding: 8px; background-color: #e9ecef; color: #666; cursor: default;">
                                Segues ✔
                            </button>
                        <?php else: ?>
                            <button id="btn-follow-back-<?php echo $follower['id']; ?>" 
                                    class="btn-primary" style="flex: 1; font-size: 0.9em; padding: 8px; background-color: #28a745;"
                                    onclick="toggleFollow(<?php echo $follower['id']; ?>, 'follow_back')">
                                Seguir de volta
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p style="color: #666;">Ainda não tens seguidores.</p>
    <?php endif; ?>

</main>

<script>
function toggleFollow(userId, type) {
    // Confirmar se for para deixar de seguir
    if (type === 'unfollow' && !confirm("Tens a certeza que queres deixar de seguir este utilizador?")) {
        return;
    }

    fetch('php/follow_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ followed_id: userId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (type === 'unfollow') {
                // Remover o cartão visualmente
                const card = document.getElementById('card-user-' + userId);
                if (card) {
                    card.style.opacity = '0.5';
                    card.innerHTML = '<p style="padding: 20px; color: #dc3545;">Deixaste de seguir.</p>';
                    setTimeout(() => card.remove(), 1000);
                    
                    // Atualizar contador
                    const stat = document.querySelector('.stat-number');
                    if(stat) stat.innerText = parseInt(stat.innerText) - 1;
                }
            } else if (type === 'follow_back') {
                // Alterar botão para "Segues ✔"
                const btn = document.getElementById('btn-follow-back-' + userId);
                if (btn) {
                    btn.innerText = "Segues ✔";
                    btn.disabled = true;
                    btn.style.backgroundColor = "#e9ecef";
                    btn.style.color = "#666";
                    btn.style.cursor = "default";
                    
                    // Atualizar contador de "A Seguir"
                    const stat = document.querySelector('.stat-number');
                    if(stat) stat.innerText = parseInt(stat.innerText) + 1;
                }
            }
        } else {
            alert("Erro: " + data.message);
        }
    })
    .catch(err => console.error("Erro:", err));
}
</script>

</body>
</html>