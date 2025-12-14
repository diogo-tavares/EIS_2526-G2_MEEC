<?php
session_start();
require_once 'php/db.php';
require_once 'php/auth.php'; // Garante que o user está logado
require_once 'php/get_profile_pic.php'; // Para a foto de perfil no header

$user_id = $_SESSION['user_id'];

// --- QUERY 1: Últimas 5 Coleções do User (MANTIDA) ---
$sql_cols = "SELECT id, title FROM collections 
             WHERE user_id = ? 
             ORDER BY created_date DESC 
             LIMIT 5";
$stmt_cols = $conn->prepare($sql_cols);
$stmt_cols->bind_param("i", $user_id);
$stmt_cols->execute();
$res_cols = $stmt_cols->get_result();

// --- QUERY 2: Eventos nos próximos 15 dias (MANTIDA) ---
$sql_events = "SELECT id, name, event_date, start_time, location 
               FROM events 
               WHERE creator_id = ? 
               AND event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)
               ORDER BY event_date ASC, start_time ASC";
$stmt_events = $conn->prepare($sql_events);
$stmt_events->bind_param("i", $user_id);
$stmt_events->execute();
$res_events = $stmt_events->get_result();

// --- QUERY 3 (NOVA): Coleções de quem eu sigo (Últimos 15 dias) ---
$sql_feed = "SELECT c.id, c.title, c.description, c.created_date, u.name as author_name
             FROM collections c
             JOIN user_follows uf ON c.user_id = uf.followed_id
             JOIN users u ON c.user_id = u.id
             WHERE uf.follower_id = ? 
             AND c.is_public = 1
             AND c.created_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)
             ORDER BY c.created_date DESC
             LIMIT 5";

$stmt_feed = $conn->prepare($sql_feed);
$stmt_feed->bind_param("i", $user_id);
$stmt_feed->execute();
$res_feed = $stmt_feed->get_result();
?>


<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hub de Coleções</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/pesquisa.js" defer></script>
</head>
<body>

    <header class="top-bar-home">
        <div class="logo">
            <a href="homepage.php">
                <img src="images/logo.png" alt="Logo do Sistema">
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


    <main class="main-content">
        
        <section class="latest-collections">
            <h2>Últimas coleções adicionadas:</h2>

            <?php if ($res_cols->num_rows > 0): ?>
                <?php while($col = $res_cols->fetch_assoc()): ?>
                    
                    <div class="collection">
                        <a href="colecao.php?id=<?php echo $col['id']; ?>">
                            <?php echo htmlspecialchars($col['title']); ?>
                        </a>

                        <div class="items">
                            <?php
                            // --- QUERY 3: 3 Itens mais recentes desta coleção (MANTIDA) ---
                            $col_id = $col['id'];
                            $sql_items = "SELECT id, image_path, name FROM items 
                                          WHERE collection_id = ? 
                                          ORDER BY id DESC LIMIT 3";
                            
                            $stmt_items = $conn->prepare($sql_items);
                            $stmt_items->bind_param("i", $col_id);
                            $stmt_items->execute();
                            $res_items = $stmt_items->get_result();

                            if ($res_items->num_rows > 0) {
                                while($item = $res_items->fetch_assoc()) {
                                    if (!empty($item['image_path'])) {
                                        echo '<a href="item.php?id=' . $item['id'] . '">';
                                        echo '<img src="' . htmlspecialchars($item['image_path']) . '" alt="' . htmlspecialchars($item['name']) . '" style="object-fit: cover; width: 80px; height: 80px; border-radius: 6px;">';
                                        echo '</a>';
                                    }
                                }
                            } else {
                                echo "<p style='font-size: 0.9em; color: #666;'>Sem itens ainda.</p>";
                            }
                            ?>
                        </div>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #666">Ainda não tens coleções.</p>
            <?php endif; ?>

        </section>
        
        <aside class="sidebar">
            <button class="btn-primary" onclick="window.location.href='minhas_colecoes.php'">Minhas coleções</button>
            <button class="btn-primary" onclick="window.location.href='add_colecao.php'">Adicionar coleção</button>
            
            <div class="upcoming-events" style="margin-top: 25px;">
                <h3>Eventos nos próximos 15 dias</h3>

                <?php if ($res_events->num_rows > 0): ?>
                    <?php while($event = $res_events->fetch_assoc()): ?>
                        
                        <div class="mini-event-card">
                            <h4><?php echo htmlspecialchars($event['name']); ?></h4>
                            <p>
                                📅 <?php echo date('d M Y', strtotime($event['event_date'])); ?> 
                                • <?php echo date('H:i', strtotime($event['start_time'])); ?>
                            </p>
                            <p>📍 <?php echo htmlspecialchars($event['location']); ?></p>
                            <a href="evento.php?id=<?php echo $event['id']; ?>">Ver detalhes →</a>
                        </div>

                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #666; font-size: 0.9em; margin-bottom: 15px;">
                        Não tens eventos agendados para os próximos 15 dias.
                    </p>
                <?php endif; ?>

                <button class="btn-secondary" onclick="window.location.href='eventos.php'">Ver todos os eventos</button>
            </div>

            <div class="upcoming-events" style="margin-top: 25px;">
                <h3 style="border-bottom: 2px solid #28a745;">👥 Novidades (Quem segues)</h3>
                
                <?php if ($res_feed->num_rows > 0): ?>
                    <?php while($feed = $res_feed->fetch_assoc()): ?>
                        <div class="mini-event-card" style="border-left: 5px solid #28a745;">
                            <span style="font-size: 0.8em; color: #666; float:right;">
                                Há <?php echo (new DateTime())->diff(new DateTime($feed['created_date']))->days; ?> dias
                            </span>
                            
                            <h4><?php echo htmlspecialchars($feed['title']); ?></h4>
                            <p style="font-size: 0.9em; margin-bottom: 5px;">
                                Por: <strong><?php echo htmlspecialchars($feed['author_name']); ?></strong>
                            </p>
                            
                            <p style="font-style: italic; font-size: 0.85em; color: #555;">
                                <?php echo substr(htmlspecialchars($feed['description']), 0, 50) . '...'; ?>
                            </p>

                            <a href="detalhes_colecao.php?id=<?php echo $feed['id']; ?>" style="color: #28a745;">
                                Ver Coleção →
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #666; font-size: 0.9em; margin-bottom: 15px;">
                        Nenhuma coleção nova das pessoas que segues nos últimos 15 dias.
                    </p>
                <?php endif; ?>

                <button class="btn-secondary" onclick="window.location.href='meus_seguidores.php'" style="background-color: #28a745; color: white;">
                    Ver quem sigo
                </button>
            </div>

        </aside>
    </main>

    <footer class="bottom-bar">
        <a href="desenvolvedores.php">DESENVOLVEDORES</a>
    </footer>

</body>
</html>