<?php
session_start();
require_once 'php/db.php';
require_once 'php/auth.php';
require_once 'php/get_profile_pic.php';

$user_id = $_SESSION['user_id'];

// 1. Buscar COLEÇÕES Públicas (de outros users)
$sql_cols = "SELECT c.*, u.name as owner_name 
             FROM collections c
             JOIN users u ON c.user_id = u.id
             WHERE c.is_public = 1  AND c.user_id != ?
             ORDER BY c.created_date DESC";


$stmt_cols = $conn->prepare($sql_cols);
$stmt_cols->bind_param("i", $user_id);
$stmt_cols->execute();
$res_cols = $stmt_cols->get_result();

// 2. Buscar EVENTOS Públicos (de outros users)
$sql_evts = "SELECT e.*, u.name as owner_name 
             FROM events e
             JOIN users u ON e.creator_id = u.id
             WHERE e.is_public = 1 AND e.event_date >= CURDATE() AND e.creator_id != ?
             ORDER BY e.event_date DESC";


$stmt_evts = $conn->prepare($sql_evts);
$stmt_evts->bind_param("i", $user_id);
$stmt_evts->execute();
$res_evts = $stmt_evts->get_result();

// 3. Buscar OUTROS USERS (Para a barra lateral)
$sql_users = "SELECT name, photo_path FROM users WHERE id != ? ORDER BY RAND()";
$stmt_users = $conn->prepare($sql_users);
$stmt_users->bind_param("i", $user_id);
$stmt_users->execute();
$res_users = $stmt_users->get_result();
?>


<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Hub</title>
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

    <main class="main-content" style="display: flex; gap: 30px; align-items: flex-start;">
        
        <div class="feed-container" style="flex: 3;">
            <h1>🌎 Social Hub</h1>
            <p style="margin-bottom: 30px; margin-top: 10px;">Descobre o que a comunidade anda a partilhar.</p>

            <h2 style="border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px;">📚 Coleções da Comunidade</h2>
            
            <section class="latest-collections" style="margin-bottom: 50px;">
                <?php if ($res_cols->num_rows > 0): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                        
                        <?php while($col = $res_cols->fetch_assoc()): ?>
                            <div class="mini-event-card" style="position: relative; border-left: 5px solid #ffc107; display: flex; flex-direction: column; justify-content: space-between;">
                                <div>
                                    <span style="font-size: 0.8em; background: #e9ecef; padding: 2px 8px; border-radius: 4px; float: right;">
                                        Por: <?php echo htmlspecialchars($col['owner_name']); ?>
                                    </span>
                                    
                                    <h4>
                                        <a href="detalhes_colecao.php?id=<?php echo $col['id']; ?>" style="text-decoration: none; color: #333;">
                                            <?php echo htmlspecialchars($col['title']); ?>
                                        </a>
                                    </h4>
                                    
                                    <p><em><?php echo htmlspecialchars($col['description']); ?></em></p>
                                </div>
                                
                                <div style="margin-top: 15px; display: flex; gap: 10px;">
                                    <a href="detalhes_colecao.php?id=<?php echo $col['id']; ?>" 
                                       class="btn-secondary" 
                                       style="flex: 1; text-align: center; text-decoration: none; color: white; background-color: #17a2b8;">
                                        👁️ Ver
                                    </a>

                                    <a href="php/copy_collection.php?id=<?php echo $col['id']; ?>" 
                                       class="btn-secondary" 
                                       style="flex: 1; text-align: center; text-decoration: none; color: white;">
                                        📥 Importar
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>

                    </div>
                <?php else: ?>
                    <p>Nenhuma coleção pública encontrada.</p>
                <?php endif; ?>
            </section>

            <h2 style="border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px;">📅 Próximos Eventos da Comunidade</h2>

            <section class="latest-collections">
                <?php if ($res_evts->num_rows > 0): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                        <?php while($evt = $res_evts->fetch_assoc()): ?>
                            <div class="mini-event-card" style="position: relative; border-left: 5px solid #007bff;">
                                <span style="font-size: 0.8em; background: #e9ecef; padding: 2px 8px; border-radius: 4px; float: right;">
                                    Por: <?php echo htmlspecialchars($evt['owner_name']); ?>
                                </span>
                                <h4><?php echo htmlspecialchars($evt['name']); ?></h4>
                                <p>📍 <?php echo htmlspecialchars($evt['location']); ?></p>
                                <p>📅 <?php echo date('d/m/Y', strtotime($evt['event_date'])); ?></p>
                                <p><em><?php echo htmlspecialchars($evt['description']); ?></em></p>
                                
                                <a href="php/copy_event.php?id=<?php echo $evt['id']; ?>" 
                                   class="btn-secondary" 
                                   style="display: block; text-align: center; margin-top: 15px; text-decoration: none; color: white;">
                                    📥 Importar Evento
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>Nenhum evento futuro público encontrado.</p>
                <?php endif; ?>
            </section>
        </div>

        <aside class="sidebar" style="flex: 1; min-width: 250px; background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
            <h3 style="margin-bottom: 15px; border-bottom: 2px solid #007bff; padding-bottom: 5px;">👥 Membros</h3>
            
            <?php if ($res_users->num_rows > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php while($u = $res_users->fetch_assoc()): ?>
                        <?php 
                            $u_img = !empty($u['photo_path']) ? $u['photo_path'] : 'images/profile.png';
                        ?>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <img src="<?php echo htmlspecialchars($u_img); ?>" 
                                 alt="Foto" 
                                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #ccc;">
                            <span style="font-weight: bold; color: #333;">
                                <?php echo htmlspecialchars($u['name']); ?>
                            </span>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>És o único membro por agora!</p>
            <?php endif; ?>
        </aside>

    </main>

    <footer class="bottom-bar">
        <a href="desenvolvedores.php">DESENVOLVEDORES</a>
    </footer>
</body>
</html>