<?php
session_start();
require_once 'php/db.php';
require_once 'php/auth.php';
require_once 'php/get_profile_pic.php';

// 1. Check if ID exists
if (!isset($_GET['id'])) {
    die("Erro: Coleção não especificada.");
}

$col_id = intval($_GET['id']); // Force integer for safety
$user_id = $_SESSION['user_id'];

// 2. PROCESS FORM (ON SUBMIT)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Receive data
    $title = trim($_POST['collection-name']);
    $desc = trim($_POST['collection-description']);
    // Checkbox for visibility (0 or 1)
    $is_public = isset($_POST['is_public']) ? intval($_POST['is_public']) : 0;

    // Validation
    if (empty($title) || empty($desc)) {
        $erro = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        // A. Update Collection Data
        $stmt = $conn->prepare("UPDATE collections SET title=?, description=?, is_public=? WHERE id=? AND user_id=?");
        $stmt->bind_param("ssiii", $title, $desc, $is_public, $col_id, $user_id);

        if ($stmt->execute()) {
            
            // B. Update Tags (Strategy: Delete old, Insert new)
            
            // 1. Delete ALL old tags for this collection
            $stmt_del = $conn->prepare("DELETE FROM collection_tags WHERE collection_id = ?");
            $stmt_del->bind_param("i", $col_id);
            $stmt_del->execute();
            $stmt_del->close();

            // 2. Insert NEW tags
            $stmt_tag = $conn->prepare("INSERT INTO collection_tags (collection_id, tag_name) VALUES (?, ?)");

            // Loop through the 5 tag inputs
            for ($i = 1; $i <= 5; $i++) {
                if (!empty($_POST["tag-$i"])) {
                    $tag_name = trim($_POST["tag-$i"]);
                    if ($tag_name !== "") {
                        $stmt_tag->bind_param("is", $col_id, $tag_name);
                        $stmt_tag->execute();
                    }
                }
            }
            $stmt_tag->close();

            // Success: Redirect to the collection page
            header("Location: colecao.php?id=" . $col_id);
            exit();

        } else {
            $erro = "Erro ao atualizar coleção: " . $conn->error;
        }
    }
}

// 3. FETCH CURRENT DATA (To fill the form)
// A. Collection Data
$stmt = $conn->prepare("SELECT * FROM collections WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $col_id, $user_id);
$stmt->execute();
$colecao = $stmt->get_result()->fetch_assoc();

if (!$colecao) {
    die("Coleção não encontrada ou sem permissão.");
}

// B. Current Tags
$stmt_tags = $conn->prepare("SELECT tag_name FROM collection_tags WHERE collection_id = ?");
$stmt_tags->bind_param("i", $col_id);
$stmt_tags->execute();
$res_tags = $stmt_tags->get_result();

$tags_atuais = [];
while ($row = $res_tags->fetch_assoc()) {
    $tags_atuais[] = $row['tag_name'];
}
// Pad array to ensure we have 5 elements (avoids undefined index errors)
while (count($tags_atuais) < 5) {
    $tags_atuais[] = "";
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Coleção</title>
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
                <img src="<?php echo htmlspecialchars($user_photo ?? 'images/profile.png'); ?>" height="90" style="border-radius: 50%; object-fit: cover; width: 90px;">
            </a>
        </div>
    </header>

    <main class="add-collection-content">
        <h1>Editar coleção: <?php echo htmlspecialchars($colecao['title']); ?></h1>

        <?php if(isset($erro)): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>

        <section class="add-collection-container">
            <form method="POST" class="add-collection-form">

                <label for="collection-name"><strong>Nome:</strong></label>
                <input type="text" id="collection-name" name="collection-name" 
                       value="<?php echo htmlspecialchars($colecao['title']); ?>" required>

                <label><strong>Tags (Preencha até 5, sem espaços, acentos ou cedilhas):</strong></label>
                <div class="tags-grid">
                    <input type="text" name="tag-1" placeholder="Tag 1 (Obrigatório)" value="<?php echo htmlspecialchars($tags_atuais[0]); ?>" required>
                    <input type="text" name="tag-2" placeholder="Tag 2" value="<?php echo htmlspecialchars($tags_atuais[1]); ?>">
                    <input type="text" name="tag-3" placeholder="Tag 3" value="<?php echo htmlspecialchars($tags_atuais[2]); ?>">
                    <input type="text" name="tag-4" placeholder="Tag 4" value="<?php echo htmlspecialchars($tags_atuais[3]); ?>">
                    <input type="text" name="tag-5" placeholder="Tag 5" value="<?php echo htmlspecialchars($tags_atuais[4]); ?>">
                </div>

                <label for="collection-description"><strong>Descrição:</strong></label>
                <textarea id="collection-description" name="collection-description" rows="5" required><?php echo htmlspecialchars($colecao['description']); ?></textarea>
                
                <label><strong>Visibilidade:</strong></label>
                <div class="checkbox-group" style="display: flex; flex-direction: column; gap: 5px; margin-bottom: 15px;">  
                    <label>
                        <input type="radio" name="is_public" value="0" 
                        <?php echo ($colecao['is_public'] == 0) ? 'checked' : ''; ?>> 
                        Privado (Apenas eu vejo)
                    </label>
    
                    <label>
                        <input type="radio" name="is_public" value="1"
                        <?php echo ($colecao['is_public'] == 1) ? 'checked' : ''; ?>> 
                        Público (Visível no Social Hub)
                    </label>
                </div>
                                
                <div class="add-collection-buttons">
                    <button type="submit" class="btn-primary">Guardar Alterações</button>
                    <button type="button" class="btn-primary" onclick="window.location.href='colecao.php?id=<?php echo $col_id; ?>'">Cancelar</button>
                </div>
            </form>
        </section>
    </main>

    <footer class="bottom-bar">
        <a href="desenvolvedores.php">DESENVOLVEDORES</a>
    </footer>

</body>
</html>