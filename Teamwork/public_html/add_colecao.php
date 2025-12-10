<?php
session_start();
require_once 'php/db.php';
require_once 'php/auth.php';
require_once 'php/get_profile_pic.php';

// Verifica se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    
    // 1. Receber e limpar dados básicos
    $title = trim($_POST['collection-name']);
    $date = $_POST['collection-date'];
    $desc = trim($_POST['collection-description']);

    // Validação simples
    if (empty($title) || empty($date) || empty($desc)) {
        $erro = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        // 2. Inserir a Coleção na tabela 'collections'
        // Nota: O campo na BD chama-se 'title' e 'created_date'
        $stmt = $conn->prepare("INSERT INTO collections (user_id, title, description, created_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $title, $desc, $date);

        if ($stmt->execute()) {
            // Recuperar o ID da coleção acabada de criar
            $collection_id = $conn->insert_id;
            $stmt->close();

            // 3. Processar e Inserir as Tags na tabela 'collection_tags'
            // Prepara a query de inserção de tags
            $stmt_tag = $conn->prepare("INSERT INTO collection_tags (collection_id, tag_name) VALUES (?, ?)");

            // Loop pelos 5 campos de tags
            for ($i = 1; $i <= 5; $i++) {
                // Verifica se o campo existe e não está vazio
                if (!empty($_POST["tag-$i"])) {
                    $tag_name = trim($_POST["tag-$i"]);
                    // Insere apenas se tiver texto
                    if ($tag_name !== "") {
                        $stmt_tag->bind_param("is", $collection_id, $tag_name);
                        $stmt_tag->execute();
                    }
                }
            }
            $stmt_tag->close();

            // Sucesso! Redirecionar para a lista
            header("Location: minhas_colecoes.php");
            exit();

        } else {
            $erro = "Erro ao criar coleção: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Coleção</title>
    <link rel="stylesheet" href="css/style.css">
    </head>

<body>

    <header class="top-bar-home">
        <div class="logo">
            <a href="homepage.php">
                <img src="images/logo.png" alt="Logo do Sistema">
            </a>
        </div>
        <div class="search-bar">
            <input type="text" placeholder="Pesquisar...">
        </div>
        <div class="user-icon">
            <a href="perfil.php">
                <img src="<?php echo htmlspecialchars($user_photo ?? 'images/profile.png'); ?>" alt="Perfil" height="90" style="border-radius: 50%; object-fit: cover; width: 90px;">
            </a>
        </div>
    </header>

    <main class="add-collection-content">
        <h1>Adicionar coleção:</h1>

        <?php if(isset($erro)): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>

        <section class="add-collection-container">
            <form method="POST" class="add-collection-form">

                <label for="collection-name"><strong>Nome:</strong></label>
                <input type="text" id="collection-name" name="collection-name" placeholder="Digite o nome da coleção" required>

                <label for="collection-date"><strong>Data de criação:</strong></label>
                <input type="date" id="collection-date" name="collection-date" required>

                <label><strong>Tags (Preencha até 5, sem espaços, acentos ou cedilhas):</strong></label>
                <div class="tags-grid">
                    <input type="text" id="tag-1" name="tag-1" placeholder="Tag 1 (Obrigatório)" required>
                    <input type="text" id="tag-2" name="tag-2" placeholder="Tag 2">
                    <input type="text" id="tag-3" name="tag-3" placeholder="Tag 3">
                    <input type="text" id="tag-4" name="tag-4" placeholder="Tag 4">
                    <input type="text" id="tag-5" name="tag-5" placeholder="Tag 5">
                </div>
                
                <label for="collection-description"><strong>Descrição:</strong></label>
                <textarea id="collection-description" name="collection-description" placeholder="Descreva brevemente a coleção..." rows="5" required></textarea>

                <div class="add-collection-buttons">
                    <button type="submit" class="btn-primary">Confirmar</button>
                    <button type="button" id="cancel-btn" class="btn-primary" onclick="window.location.href='minhas_colecoes.php'">Cancelar</button>
                </div>
            </form>
        </section>
    </main>

    <footer class="bottom-bar">
        <a href="desenvolvedores.php">DESENVOLVEDORES</a>
    </footer>

</body>
</html>