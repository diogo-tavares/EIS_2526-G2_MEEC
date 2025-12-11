<?php
session_start();
require_once 'php/db.php';
require_once 'php/auth.php';
require_once 'php/get_profile_pic.php';

$user_id = $_SESSION['user_id'];

// 2. PROCESSAR O FORMULÁRIO (QUANDO CLICAS EM CONFIRMAR)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['event-name'];
    $location = $_POST['event-location'];
    $date = $_POST['event-date'];
    $time = $_POST['event-time'];
    $price = $_POST['event-price'];
    $desc = $_POST['event-description'];

    // A. Inserir evento na tabela events
    $is_public = isset($_POST['is_public']) ? intval($_POST['is_public']) : 0;

    $stmt = $conn->prepare("INSERT INTO events (creator_id, name, location, event_date, start_time, price, description, is_public) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssdsi", $user_id, $name, $location, $date, $time, $price, $desc, $is_public);
    if ($stmt->execute()) {
        // Obter o ID do evento que acabou de ser criado
        $new_event_id = $conn->insert_id;

        // B. Inserir Coleções Associadas (se houver alguma selecionada)
        if (isset($_POST['collections'])) {
            $stmt_col = $conn->prepare("INSERT INTO event_collections (event_id, collection_id) VALUES (?, ?)");
            foreach ($_POST['collections'] as $col_id) {
                $stmt_col->bind_param("ii", $new_event_id, $col_id);
                $stmt_col->execute();
            }
        }

        // Redirecionar para a página do novo evento
        header("Location: evento.php?id=" . $new_event_id);
        exit();
    } else {
        $erro = "Erro ao criar evento: " . $conn->error;
    }
}

// 3. BUSCAR AS COLEÇÕES DO UTILIZADOR (Para as checkboxes)
$sql_all_cols = "SELECT id, title FROM collections WHERE user_id = ?";
$stmt_cols = $conn->prepare($sql_all_cols);
$stmt_cols->bind_param("i", $user_id);
$stmt_cols->execute();
$todas_colecoes = $stmt_cols->get_result();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Evento</title>
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

    <main class="add-collection-content">
        <h1>Adicionar novo evento</h1>
        
        <?php if(isset($erro)) echo "<p style='color:red'>$erro</p>"; ?>

        <section class="add-collection-container">
            <form method="POST" class="add-collection-form">

                <label for="event-name"><strong>Nome:</strong></label>
                <input type="text" id="event-name" name="event-name" placeholder="Digite o nome do evento" required>
                
                <label><strong>Coleções (selecione as que se aplicam):</strong></label>
                <div class="checkbox-group" style="display: flex; flex-direction: column; gap: 5px; margin-bottom: 15px;">
                    <?php 
                    if ($todas_colecoes->num_rows > 0) {
                        while($col = $todas_colecoes->fetch_assoc()) {
                            echo '<label>';
                            echo '<input type="checkbox" name="collections[]" value="' . $col['id'] . '"> ';
                            echo htmlspecialchars($col['title']);
                            echo '</label>';
                        }
                    } else {
                        echo "<p>Não tens coleções criadas. Cria uma coleção primeiro!</p>";
                    }
                    ?>
                </div>

                <label for="event-location"><strong>Localização:</strong></label>
                <input type="text" id="event-location" name="event-location" placeholder="Digite a localização do evento" required>

                <label for="event-date"><strong>Data:</strong></label>
                <input type="date" id="event-date" name="event-date" required>
                
                <label for="event-time"><strong>Hora de início:</strong></label>
                <input type="time" id="event-time" name="event-time" required>

                <label for="event-price"><strong>Preço do bilhete (€):</strong></label>
                <input type="number" id="event-price" name="event-price" placeholder="0.00" step="0.01" required>

                <label for="event-description"><strong>Descrição:</strong></label>
                <textarea id="event-description" name="event-description" placeholder="Descreva brevemente o evento..." rows="5" required></textarea>
                
                <label><strong>Visibilidade para outros users:</strong></label>
                <div class="checkbox-group" style="display: flex; flex-direction: column; gap: 5px; margin-bottom: 15px;">
                    <label style="align-items: center;">
                        <input type="radio" name="is_public" value="0" checked> 
                        Privado (Apenas eu vejo)
                    </label>    
                    <label>
                        <input type="radio" name="is_public" value="1"> 
                        Público (Visível no Social Hub)
                    </label>

                </div>              
                
                <div class="add-collection-buttons">
                    <button type="submit" class="btn-primary">Criar Evento</button>
                    <button type="button" class="btn-primary" onclick="window.location.href='eventos.php'">Cancelar</button>
                </div>
            </form>
        </section>
    </main>

    <footer class="bottom-bar">
        <a href="desenvolvedores.php">DESENVOLVEDORES</a>
    </footer>

</body>
</html>