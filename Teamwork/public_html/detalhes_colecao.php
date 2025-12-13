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

// 2. Buscar dados da Coleção (LÓGICA: Permite se for Pública OU se for do user)
$stmt = $conn->prepare("
    SELECT c.*, u.name as owner_name 
    FROM collections c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.id = ? AND (c.is_public = 1 OR c.user_id = ?)
");
$stmt->bind_param("ii", $collection_id, $user_id);
$stmt->execute();
$colecao = $stmt->get_result()->fetch_assoc();

if (!$colecao) {
    die("Erro: Coleção não encontrada, privada ou sem permissão.");
}

// 3. Buscar Tags
$stmt_tags = $conn->prepare("SELECT tag_name FROM collection_tags WHERE collection_id = ?");
$stmt_tags->bind_param("i", $collection_id);
$stmt_tags->execute();
$res_tags = $stmt_tags->get_result();
$tags_array = [];
while ($t = $res_tags->fetch_assoc()) { $tags_array[] = $t['tag_name']; }
$tags_string = implode(", ", $tags_array);

// 4. Buscar Itens
$stmt_items = $conn->prepare("SELECT * FROM items WHERE collection_id = ?");
$stmt_items->bind_param("i", $collection_id);
$stmt_items->execute();
$res_items = $stmt_items->get_result();
$num_itens = $res_items->num_rows;
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($colecao['title']); ?> - Social Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/pesquisa.js" defer></script>
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
            <a href="perfil.php"><img src="<?php echo htmlspecialchars($user_photo); ?>" alt="Perfil" height="90" style="border-radius: 50%; object-fit: cover; width: 90px;"></a>
        </div>
    </header>

    <main class="main-content" style="margin-bottom: 200px;">
        <section class="collection-details">
            <a href="social.php" style="text-decoration: none; color: #666;">&larr; Voltar ao Social Hub</a>
            <h2 style="margin-top: 15px;">Detalhes da Coleção Pública</h2>

            <div class="collection-info" style="background-color: #fff3cd; border: 1px solid #ffeeba; border-radius: 8px; padding: 25px;">
                <p>
                    <span style="color: #856404; font-weight: bold; font-size: 1.8em;">
                        <?php echo htmlspecialchars($colecao['title']); ?>
                    </span>
                    <span style="float: right; font-size: 0.9em; background: white; padding: 5px 10px; border-radius: 15px; border: 1px solid #ddd;">
                        Criado por: <strong><?php echo htmlspecialchars($colecao['owner_name']); ?></strong>
                    </span>
                </p>
                <p><strong>Data de criação: </strong><?php echo date('d/m/Y', strtotime($colecao['created_date'])); ?></p>
                <p><strong>Tags: </strong><?php echo htmlspecialchars($tags_string); ?></p>
                <p><strong>Número de itens: </strong><?php echo $num_itens; ?></p>
                <p><strong>Descrição: </strong><?php echo nl2br(htmlspecialchars($colecao['description'])); ?></p>
            </div>

            <div class="collection-items">
                <h3>Itens nesta coleção:</h3>
                <div class="item-gallery">
                    <?php if ($res_items->num_rows > 0): ?>
                        <?php while($item = $res_items->fetch_assoc()): ?>
                            <div style="text-align: center;">
                                <?php if (!empty($item['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         title="<?php echo htmlspecialchars($item['name']); ?>"
                                         style="cursor: default;">
                                <?php endif; ?>
                                <p style="font-size: 0.9em; margin-top: 5px; font-weight: bold;"><?php echo htmlspecialchars($item['name']); ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>Esta coleção não tem itens visíveis.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <aside class="sidebar">
            <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
                <h3>Gostaste?</h3>
                <p style="margin-bottom: 15px; font-size: 0.9em; color: #555;">Podes importar esta coleção para o teu perfil e geri-la como quiseres.</p>
                
                <a href="php/copy_collection.php?id=<?php echo $collection_id; ?>" class="btn-primary" style="display: block; text-decoration: none; color: white;">
                    📥 Importar Coleção
                </a>
            </div>
        </aside>
    </main>

    <footer class="bottom-bar">
        <a href="desenvolvedores.php">DESENVOLVEDORES</a>
    </footer>
</body>
</html>