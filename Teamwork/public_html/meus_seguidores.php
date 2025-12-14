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
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Quem estou a seguir</title>
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
            justify-content: center;
        }
        .stat-item {
            min-width: 150px;
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
                        <a href="perfil_publico.php?id=<?php echo $user['id']; ?>" 
                           class="btn-primary" 
                           style="flex: 1; 
                                  height: 40px; 
                                  box-sizing: border-box; 
                                  border: 1px solid #007bff; 
                                  margin: 0; 
                                  padding: 0; 
                                  font-size: 0.9em; 
                                  text-decoration: none; 
                                  display: flex; 
                                  justify-content: center; 
                                  align-items: center; 
                                  border-radius: 6px;">
                           Ver Perfil
                        </a>
                        
                        <button class="btn-secondary btn-unfollow" 
                                style="flex: 1; 
                                       height: 40px; 
                                       box-sizing: border-box; 
                                       border: 1px solid #dc3545;
                                       margin: 0; 
                                       padding: 0; 
                                       font-size: 0.9em; 
                                       display: flex; 
                                       justify-content: center; 
                                       align-items: center; 
                                       border-radius: 6px; 
                                       cursor: pointer;" 
                                onclick="toggleFollow(<?php echo $user['id']; ?>)">
                            Deixar
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p style="margin-bottom: 40px; color: #666;">Ainda não segues ninguém. Explora o Social Hub para encontrar pessoas.</p>
    <?php endif; ?>

</main>

<script>
function toggleFollow(userId) {
    if (!confirm("Tens a certeza que queres deixar de seguir este utilizador?")) {
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
            const card = document.getElementById('card-user-' + userId);
            if (card) {
                card.style.opacity = '0.5';
                card.innerHTML = '<p style="padding: 20px; color: #dc3545;">Deixaste de seguir.</p>';
                setTimeout(() => card.remove(), 1000);
                
                const stat = document.querySelector('.stat-number');
                if(stat) stat.innerText = parseInt(stat.innerText) - 1;
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