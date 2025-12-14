<?php
session_start();
require_once 'php/db.php';
require_once 'php/auth.php';
require_once 'php/get_profile_pic.php';

// 1. Validar ID da coleção
if (!isset($_GET['id'])) {
    die("Erro: Coleção não especificada.");
}

$collection_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// 2. Buscar dados da Coleção (COM PROTEÇÃO: Apenas do user logado)
$stmt = $conn->prepare("SELECT * FROM collections WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $collection_id, $user_id);
$stmt->execute();
$colecao = $stmt->get_result()->fetch_assoc();

if (!$colecao) {
    die("Erro: Coleção não encontrada ou sem permissão de acesso.");
}

// 3. Buscar Tags da coleção
$stmt_tags = $conn->prepare("SELECT tag_name FROM collection_tags WHERE collection_id = ?");
$stmt_tags->bind_param("i", $collection_id);
$stmt_tags->execute();
$res_tags = $stmt_tags->get_result();

$tags_array = [];
while ($t = $res_tags->fetch_assoc()) {
    $tags_array[] = $t['tag_name'];
}
$tags_string = implode(", ", $tags_array);

// 4. Buscar Itens da coleção
$stmt_items = $conn->prepare("SELECT * FROM items WHERE collection_id = ?");
$stmt_items->bind_param("i", $collection_id);
$stmt_items->execute();
$res_items = $stmt_items->get_result();
$num_itens = $res_items->num_rows; // Contagem automática

// 5. Buscar Eventos associados
// A. Eventos Futuros (Data >= Hoje) -> Ordem ASC (Mais próximo primeiro)
$sql_future = "SELECT e.* FROM events e 
               JOIN event_collections ec ON e.id = ec.event_id 
               WHERE ec.collection_id = ? AND e.event_date >= CURDATE() 
               ORDER BY e.event_date ASC";
$stmt_fut = $conn->prepare($sql_future);
$stmt_fut->bind_param("i", $collection_id);
$stmt_fut->execute();
$events_future = $stmt_fut->get_result();

// B. Eventos Passados (Data < Hoje) -> Ordem DESC (Mais recente primeiro)
$sql_past = "SELECT e.* FROM events e 
             JOIN event_collections ec ON e.id = ec.event_id 
             WHERE ec.collection_id = ? AND e.event_date < CURDATE() 
             ORDER BY e.event_date DESC";
$stmt_past = $conn->prepare($sql_past);
$stmt_past->bind_param("i", $collection_id);
$stmt_past->execute();
$events_past = $stmt_past->get_result();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($colecao['title']); ?> - Detalhes</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/colecao.js" defer></script>
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
        <section class="collection-details">
            <h2>Coleção</h2>

            <div class="collection-info" style="background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.05)";>
                <p>
                    <span style="color: #007bff; font-weight: bold; font-size: 1.8em;">
                        <?php echo htmlspecialchars($colecao['title']); ?>
                    </span>
                </p>
                <p><strong>Data de criação: </strong><?php echo date('d/m/Y', strtotime($colecao['created_date'])); ?></p>
                <p><strong>Tags: </strong><?php echo htmlspecialchars($tags_string); ?></p>
                <p><strong>Número de itens: </strong><?php echo $num_itens; ?></p>
                <p><strong>Descrição: </strong><?php echo nl2br(htmlspecialchars($colecao['description'])); ?></p>
                
                <?php if($colecao['is_public']): ?>
                    <p style="margin-top:25px; color: #333; font-size: 0.95em;">🔓 Esta coleção é <strong>Pública</strong>.</p>
                <?php else: ?>
                    <p style="margin-top:25px; color: #333; font-size: 0.95em;">🔒 Esta coleção é <strong>Privada</strong>.</p>
                <?php endif; ?>
            </div>

            <div class="collection-items">
                <h3>Itens:</h3>
                <div class="item-gallery">
                    <?php if ($res_items->num_rows > 0): ?>
                        <?php while($item = $res_items->fetch_assoc()): ?>
                            <?php if (!empty($item['image_path'])): ?>
                                <div style="text-align: center;">
                                    <a href="item.php?id=<?php echo $item['id']; ?>" style="text-decoration: none; color: inherit;">
                                        <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             title="<?php echo htmlspecialchars($item['name']); ?>">
                                        
                                        <p style="margin-top: 5px; font-size: 0.9em; font-weight: bold; color: #333;">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </p>
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="grid-column: 1 / -1; color: #333; margin-bottom: 20px;">Esta coleção ainda não tem itens.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="collection-events">
                
                <div class="events-group">
                    <h3>Eventos Futuros:</h3>
                    
                    <?php if ($events_future->num_rows > 0): ?>
                        <?php while($evt = $events_future->fetch_assoc()): ?>
                            <div class="mini-event-card">
                                <h4><?php echo htmlspecialchars($evt['name']); ?></h4>
                                <p>📅 <?php echo date('d M Y', strtotime($evt['event_date'])); ?> • <?php echo date('H:i', strtotime($evt['start_time'])); ?></p>
                                <p>📍 <?php echo htmlspecialchars($evt['location']); ?></p>
                                <a href="evento.php?id=<?php echo $evt['id']; ?>">Ver detalhes →</a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="margin-bottom: 20px;  color: #333">Não há eventos futuros associados.</p>
                    <?php endif; ?>
                </div>

                <div>
                    <h3>Eventos Passados:</h3>
                    
                    <?php if ($events_past->num_rows > 0): ?>
                        <?php while($evt = $events_past->fetch_assoc()): ?>
                            <div class="mini-event-card past-event">
                                <h4><?php echo htmlspecialchars($evt['name']); ?></h4>
                                <p>📅 <?php echo date('d M Y', strtotime($evt['event_date'])); ?> • <?php echo date('H:i', strtotime($evt['start_time'])); ?></p>
                                <p>📍 <?php echo htmlspecialchars($evt['location']); ?></p>
                                
                                <?php if ($evt['is_present'] === null): ?>
                                    <p class="event-action" data-id="<?php echo $evt['id']; ?>">
                                        Registar presença e classificar evento
                                    </p>       
                                <?php else: ?>
                                    <p class="event-meta-info" data-id="<?php echo $evt['id']; ?>" style="cursor: pointer;">
                                        <strong>Presença:</strong> <?php echo $evt['is_present'] ? 'Sim' : 'Não'; ?> 
                                        <?php if($evt['is_present']): ?>
                                            | <strong>Classificação:</strong> <?php echo $evt['rating'] ? str_repeat('⭐', $evt['rating']) : '---'; ?>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>

                                <a href="evento.php?id=<?php echo $evt['id']; ?>">Ver detalhes →</a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="margin-bottom: 20px;  color: #333">Não há eventos passados.</p>
                    <?php endif; ?>
                </div>
                
            </div>
        </section>

        <aside class="sidebar">
            <button class="btn-primary" onclick="window.location.href='add_item.php?collection_id=<?php echo $collection_id; ?>'">Adicionar item à coleção</button>
            
            <button class="btn-primary" onclick="window.location.href='editar_colecao.php?id=<?php echo $collection_id; ?>'">Editar coleção</button>
            
            <button class="btn-primary" id="delete-item-btn">Eliminar coleção</button>
        </aside>
    </main>

    <footer class="bottom-bar">
        <a href="desenvolvedores.php">DESENVOLVEDORES</a>
    </footer>
    
    <div id="confirm-popup" class="popup-overlay">
        <div class="popup-box">
            <h3>Tem a certeza que deseja eliminar esta coleção?</h3>
            <p style="font-size: 0.9em; margin-bottom: 15px;">(Isto apagará todos os itens desta coleção)</p>
            <div class="popup-buttons">
                <button id="confirm-yes" class="btn-secondary" onclick="window.location.href='php/delete_collection.php?id=<?php echo $collection_id; ?>'">Sim</button>
                <button id="confirm-no" class="btn-secondary">Não</button>
            </div>
        </div>
    </div>
    
    <div id="event-modal" class="modal-bg">
        <div class="modal-box">
            <h3>Registar presença e classificação</h3>
            <input type="hidden" id="modal-event-id" value="">
            <label><strong>Presença:</strong></label>
            <select id="presence-select">
                <option value="">Selecione...</option>
                <option value="1">Sim</option>
                <option value="0">Não</option>
            </select>
            <label><strong>Classificação (1-5):</strong></label>
            <select id="rating-select" disabled>
                <option value="">Selecione...</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>
            <div class="modal-buttons">
                <button id="confirm-modal" class="btn-secondary">Confirmar</button>
                <button id="cancel-modal" class="btn-secondary">Cancelar</button>
            </div>
        </div>
    </div>

</body>
</html>