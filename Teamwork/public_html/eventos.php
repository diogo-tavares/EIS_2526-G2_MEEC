<?php
session_start();
require_once 'php/db.php';
require_once 'php/auth.php';
require_once 'php/get_profile_pic.php';

// 2. Obter ID do utilizador logado
$user_id = $_SESSION['user_id'];

// 3. Buscar APENAS os eventos deste utilizador
$sql = "SELECT * FROM events WHERE creator_id = ? ORDER BY event_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$eventos_futuros = [];
$eventos_passados = [];
$data_hoje = date('Y-m-d');

if ($result) {
    while ($row = $result->fetch_assoc()) {
        if ($row['event_date'] >= $data_hoje) {
            $eventos_futuros[] = $row;
        } else {
            $eventos_passados[] = $row;
        }
    }
}

// Inverter ordem dos passados
$eventos_passados = array_reverse($eventos_passados);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Eventos</title> <link rel="stylesheet" href="css/style.css">
    <script src="js/eventos.js" defer></script>
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
        </div>
        <div class="user-icon">
            <a href="perfil.php">
                <img src="<?php echo htmlspecialchars($user_photo); ?>" alt="Perfil" height="90" style="border-radius: 50%; object-fit: cover; width: 90px;">
            </a>
        </div>
    </header>

    <main class="main-content">
        <section class="latest-collections">
            
            <h2>Meus Eventos Futuros:</h2>
            
            <?php if (empty($eventos_futuros)): ?>
                <p>Não tens eventos futuros agendados.</p>
            <?php else: ?>
                <?php foreach ($eventos_futuros as $evento): ?>
                    <div class="mini-event-card">
                        <h4><?php echo htmlspecialchars($evento['name']); ?></h4>
                        <p><?php echo htmlspecialchars($evento['description']); ?></p>
                        <p>📅 <?php echo date('d M Y', strtotime($evento['event_date'])); ?> • <?php echo date('H:i', strtotime($evento['start_time'])); ?></p>
                        <p>📍 <?php echo htmlspecialchars($evento['location']); ?></p>
                        <a href="evento.php?id=<?php echo $evento['id']; ?>">Ver detalhes →</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <h2 class="event-section-title">Meus Eventos Passados:</h2>

            <?php if (empty($eventos_passados)): ?>
                <p>Não tens eventos passados.</p>
            <?php else: ?>
                <?php foreach ($eventos_passados as $evento): ?>
                    <div class="mini-event-card past-event">
                        <h4><?php echo htmlspecialchars($evento['name']); ?></h4>
                        <p><?php echo htmlspecialchars($evento['description']); ?></p>
                        <p>📅 <?php echo date('d M Y', strtotime($evento['event_date'])); ?> • <?php echo date('H:i', strtotime($evento['start_time'])); ?></p>
                        <p>📍 <?php echo htmlspecialchars($evento['location']); ?></p>
                        
                        <?php if ($evento['is_present'] === null): ?>
                            <p class="event-action" data-id="<?php echo $evento['id']; ?>">
                                Registar presença e classificar evento
                            </p>       
                        <?php else: ?>
                            <p class="event-meta-info" data-id="<?php echo $evento['id']; ?>">
                                <strong>Presença:</strong> <?php echo $evento['is_present'] ? 'Sim' : 'Não'; ?> | 
                                <strong>Classificação:</strong> <?php echo $evento['rating'] ? str_repeat('⭐', $evento['rating']) : '---'; ?>
                                <span style="font-size: 0.8em; color: #888; margin-left: 10px;">(Clique para alterar)</span>
                            </p>
                        <?php endif; ?>

                        <a href="evento.php?id=<?php echo $evento['id']; ?>">Ver detalhes →</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
       
        </section>

        <aside class="sidebar">
            <button class="btn-primary" onclick="window.location.href='add_evento.php'">Adicionar evento</button>
            <div style="margin-top: 5px;">

                <button class="btn-secondary" onclick="window.location.href='php/export_events.php'" style="width:100%; margin-bottom: 5px;">
                    Exportar CSV
                </button>

                <form action="php/import_events.php" method="POST" enctype="multipart/form-data" id="import-evt-form">
                    <input type="file" name="csv_file" id="csv-evt-input" accept=".csv" style="display: none;" onchange="document.getElementById('import-evt-form').submit()">
        
                    <button type="button" class="btn-secondary" onclick="document.getElementById('csv-evt-input').click()" style="width:100%;">
                        Importar CSV
                    </button>
                </form>
            </div>
        </aside>
    </main>

    <footer class="bottom-bar">
        <a href="desenvolvedores.php">DESENVOLVEDORES</a>
    </footer>
    
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