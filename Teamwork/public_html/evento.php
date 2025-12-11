<?php
session_start();
require_once 'php/db.php';
require_once 'php/auth.php';
require_once 'php/get_profile_pic.php';

// 1. Verificar ID
if (!isset($_GET['id'])) {
    die("Erro: Evento não especificado.");
}

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// 2. Buscar dados (COM FILTRO DE DONO)
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ? AND creator_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$evento = $result->fetch_assoc();

if (!$evento) {
    // Se não encontrar, ou o evento não existe ou pertence a outro user
    die("Erro: Evento não encontrado ou sem permissão de acesso.");
}

// 3. Buscar nomes das Coleções
$sql_col = "SELECT c.title FROM collections c 
            JOIN event_collections ec ON c.id = ec.collection_id 
            WHERE ec.event_id = ?";
$stmt_col = $conn->prepare($sql_col);
$stmt_col->bind_param("i", $id);
$stmt_col->execute();
$result_col = $stmt_col->get_result();

$nomes_colecoes = [];
while ($row = $result_col->fetch_assoc()) {
    $nomes_colecoes[] = $row['title'];
}

$string_colecoes = empty($nomes_colecoes) ? "Nenhuma coleção associada" : implode(", ", $nomes_colecoes);


// 4. CÁLCULO DE DATAS
$data_evento = new DateTime($evento['event_date']);
$hoje = new DateTime();
$hoje->setTime(0, 0, 0); // Zerar horas para comparar apenas dias
$data_evento->setTime(0, 0, 0);

// Verifica se a data do evento é maior (futuro) que a de hoje
$is_futuro = ($data_evento > $hoje);
$dias_restantes = 0;

if ($is_futuro) {
    $intervalo = $hoje->diff($data_evento);
    $dias_restantes = $intervalo->days;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($evento['name']); ?> - Detalhes</title>
    <link rel="stylesheet" href="css/style.css">
    <script>const currentEventId = <?php echo $id; ?>;</script>
    <script src="js/evento.js" defer></script>
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
            <input type="text" id="live-search-input" placeholder="🔍 Pesquisar..." autocomplete="off">
            <div id="search-results" class="search-results-list"></div>
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
            <h2>Evento</h2>

            <div class="collection-info"style="background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <p>
                    <span style="color: #007bff; font-weight: bold; font-size: 1.8em;">
                        <?php echo htmlspecialchars($evento['name']); ?>
                    </span>
                </p>
                <p><strong>Coleções: </strong><?php echo htmlspecialchars($string_colecoes); ?></p>
                <p><strong>Localização: </strong><?php echo htmlspecialchars($evento['location']); ?></p>
                <p><strong>Data: </strong><?php echo date('d/m/Y', strtotime($evento['event_date'])); ?></p>
                <p><strong>Hora de início: </strong><?php echo date('H:i', strtotime($evento['start_time'])); ?></p>
                <p><strong>Preço do bilhete: </strong>
                    <?php echo ($evento['price'] == 0) ? 'Grátis' : number_format($evento['price'], 2, ',', ' ') . ' €'; ?>
                </p>
                <p><strong>Descrição: </strong><?php echo nl2br(htmlspecialchars($evento['description'])); ?></p>
                
                <div style="margin-top: 25px; padding-top: 15px;">
                    
                    <?php if ($is_futuro): ?>
                        <p style="color: #333;">
                            ⏳ Faltam <strong><?php echo $dias_restantes; ?> dias</strong> para o evento começar.
                        </p>
                        <p style="margin-top: 5px; font-size: 0.95em; color: #333;">
                            <?php if ($evento['is_public'] == 1): ?>
                                🔓 Este evento é <strong>Público</strong>.
                            <?php else: ?>
                                🔒 Este evento é <strong>Privado</strong>.
                            <?php endif; ?>
                        </p>
                    <?php else: ?> 
                        <?php if ($evento['is_present'] === null): ?>
                            <p class="event-action" data-id="<?php echo $evento['id']; ?>" style="cursor: pointer; font-weight: bold;">
                                Registar presença e classificar evento
                            </p>       
                        <?php else: ?>
                            <p class="event-meta-info" data-id="<?php echo $evento['id']; ?>" style="cursor: pointer;" title="Clique para alterar">
                                <strong>Presença:</strong> <?php echo $evento['is_present'] ? 'Sim' : 'Não'; ?> 
                                <?php if($evento['is_present']): ?>
                                    | <strong>Classificação:</strong> <?php echo $evento['rating'] ? str_repeat('⭐', $evento['rating']) : '---'; ?>
                                <?php endif; ?>
                                <span style="font-size: 0.8em; color: #888; margin-left: 10px;">(Clique para alterar)</span>
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            </div>
        </section>

        <aside class="sidebar">
            <button class="btn-primary" onclick="window.location.href='editar_evento.php?id=<?php echo $id; ?>'">Editar evento</button>
            <button class="btn-primary" id="delete-item-btn">Eliminar evento</button>
        </aside>
    </main>

    <footer class="bottom-bar">
        <a href="desenvolvedores.php">DESENVOLVEDORES</a>
    </footer>
    
    <div id="confirm-popup" class="popup-overlay">
        <div class="popup-box">
            <h3>Tem a certeza que deseja eliminar este evento?</h3>
            <div class="popup-buttons">
                <button id="confirm-yes" class="btn-secondary">Sim</button>
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