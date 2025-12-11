<?php
session_start();
require_once 'php/db.php';
require_once 'php/auth.php';
require_once 'php/get_profile_pic.php';

// 1. Verificar se temos ID
if (!isset($_GET['id'])) {
    die("Evento não especificado.");
}

$event_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// 2. PROCESSAR O FORMULÁRIO (QUANDO CLICAS EM CONFIRMAR)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['event-name'];
    $location = $_POST['event-location'];
    $date = $_POST['event-date'];
    $time = $_POST['event-time'];
    $price = $_POST['event-price'];
    $desc = $_POST['event-description'];

    // A. Atualizar dados principais do evento
    $is_public = isset($_POST['is_public']) ? intval($_POST['is_public']) : 0;

    $stmt = $conn->prepare("UPDATE events SET name=?, location=?, event_date=?, start_time=?, price=?, description=?, is_public=? WHERE id=? AND creator_id=?");
    $stmt->bind_param("ssssdsiii", $name, $location, $date, $time, $price, $desc, $is_public, $event_id, $user_id);

    if ($stmt->execute()) {
        // B. Atualizar Coleções Associadas
        // Estratégia: Apagar todas as associações antigas e inserir as novas
        $conn->query("DELETE FROM event_collections WHERE event_id = $event_id");

        if (isset($_POST['collections'])) {
            $stmt_col = $conn->prepare("INSERT INTO event_collections (event_id, collection_id) VALUES (?, ?)");
            foreach ($_POST['collections'] as $col_id) {
                $stmt_col->bind_param("ii", $event_id, $col_id);
                $stmt_col->execute();
            }
        }

        // Redirecionar para a página do evento atualizado
        header("Location: evento.php?id=" . $event_id);
        exit();
    } else {
        $erro = "Erro ao atualizar: " . $conn->error;
    }
}

// 3. BUSCAR DADOS PARA PREENCHER O FORMULÁRIO
// A. Dados do Evento
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ? AND creator_id = ?");
$stmt->bind_param("ii", $event_id, $user_id);
$stmt->execute();
$evento = $stmt->get_result()->fetch_assoc();

if (!$evento) {
    die("Evento não encontrado ou sem permissão para editar.");
}

// B. Buscar TODAS as coleções do utilizador (para as opções das checkboxes)
$sql_all_cols = "SELECT id, title FROM collections WHERE user_id = ?";
$stmt_cols = $conn->prepare($sql_all_cols);
$stmt_cols->bind_param("i", $user_id);
$stmt_cols->execute();
$todas_colecoes = $stmt_cols->get_result();

// C. Buscar as coleções JÁ associadas a este evento (para marcar como checked)
$sql_linked = "SELECT collection_id FROM event_collections WHERE event_id = ?";
$stmt_linked = $conn->prepare($sql_linked);
$stmt_linked->bind_param("i", $event_id);
$stmt_linked->execute();
$res_linked = $stmt_linked->get_result();

$colecoes_selecionadas = [];
while ($row = $res_linked->fetch_assoc()) {
    $colecoes_selecionadas[] = $row['collection_id'];
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Evento</title>
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

    <main class="add-collection-content">
        <h1>Editar evento: <?php echo htmlspecialchars($evento['name']); ?></h1>
        
        <?php if(isset($erro)) echo "<p style='color:red'>$erro</p>"; ?>

        <section class="add-collection-container">
            <form method="POST" class="add-collection-form">

                <label for="event-name"><strong>Nome:</strong></label>
                <input type="text" id="event-name" name="event-name" 
                       value="<?php echo htmlspecialchars($evento['name']); ?>" required>
                
                <label><strong>Coleções (selecione as que se aplicam):</strong></label>
                <div class="checkbox-group" style="display: flex; flex-direction: column; gap: 5px; margin-bottom: 15px;">
                    <?php 
                    if ($todas_colecoes->num_rows > 0) {
                        while($col = $todas_colecoes->fetch_assoc()) {
                            // Verifica se esta coleção está na lista das selecionadas
                            $checked = in_array($col['id'], $colecoes_selecionadas) ? "checked" : "";
                            
                            echo '<label>';
                            echo '<input type="checkbox" name="collections[]" value="' . $col['id'] . '" ' . $checked . '> ';
                            echo htmlspecialchars($col['title']);
                            echo '</label>';
                        }
                    } else {
                        echo "<p>Não tens coleções criadas.</p>";
                    }
                    ?>
                </div>

                <label for="event-location"><strong>Localização:</strong></label>
                <input type="text" id="event-location" name="event-location" 
                       value="<?php echo htmlspecialchars($evento['location']); ?>" required>

                <label for="event-date"><strong>Data:</strong></label>
                <input type="date" id="event-date" name="event-date" 
                       value="<?php echo $evento['event_date']; ?>" required>
                
                <label for="event-time"><strong>Hora de início:</strong></label>
                <input type="time" id="event-time" name="event-time" 
                       value="<?php echo date('H:i', strtotime($evento['start_time'])); ?>" required>

                <label for="event-price"><strong>Preço do bilhete (€):</strong></label>
                <input type="number" id="event-price" name="event-price" step="0.01" 
                       value="<?php echo $evento['price']; ?>" required>

                <label for="event-description"><strong>Descrição:</strong></label>
                <textarea id="event-description" name="event-description" rows="5" required><?php echo htmlspecialchars($evento['description']); ?></textarea>
                
                <label><strong>Visibilidade para outros users:</strong></label>
                <div class="checkbox-group" style="display: flex; flex-direction: column; gap: 5px; margin-bottom: 15px;">  
                    <label>
                        <input type="radio" name="is_public" value="0" 
                        <?php echo ($evento['is_public'] == 0) ? 'checked' : ''; ?>> 
                        Privado (Apenas eu vejo)
                    </label>
    
                    <label>
                        <input type="radio" name="is_public" value="1"
                        <?php echo ($evento['is_public'] == 1) ? 'checked' : ''; ?>> 
                        Público (Visível no Social Hub)
                    </label>

                </div>
                
                <div class="add-collection-buttons">
                    <button type="submit" class="btn-primary">Guardar Alterações</button>
                    <button type="button" class="btn-primary" onclick="window.location.href='evento.php?id=<?php echo $event_id; ?>'">Cancelar</button>
                </div>
            </form>
        </section>
    </main>

    <footer class="bottom-bar">
        <a href="desenvolvedores.php">DESENVOLVEDORES</a>
    </footer>

</body>
</html>