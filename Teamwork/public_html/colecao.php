<?php
session_start();
require_once 'php/db.php';
require_once 'php/auth.php';
require_once 'php/get_profile_pic.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Validar ID da coleção
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("ID inválido.");
}

// Buscar dados da coleção (APENAS SE FOR DO UTILIZADOR)
$stmt = $conn->prepare("
    SELECT id, title, description, created_date
    FROM collections
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$colecao = $result->fetch_assoc();

if (!$colecao) {
    die("Coleção não encontrada ou não te pertence.");
}

// Buscar itens da coleção
$stmtItens = $conn->prepare("
    SELECT id, name, image_path
    FROM items
    WHERE collection_id = ?
");
$stmtItens->bind_param("i", $id);
$stmtItens->execute();
$itens = $stmtItens->get_result();
$total_itens = $itens->num_rows;

// Buscar tags da coleção (APENAS LISTAR)
$stmtTags = $conn->prepare("
    SELECT tag_name 
    FROM collection_tags 
    WHERE collection_id = ?
");
$stmtTags->bind_param("i", $id);
$stmtTags->execute();
$tags = $stmtTags->get_result();

// Buscar eventos associados à coleção
$stmtEventos = $conn->prepare("
    SELECT e.*
    FROM events e
    JOIN event_collections ec ON e.id = ec.event_id
    WHERE ec.collection_id = ?
    ORDER BY e.event_date ASC
");
$stmtEventos->bind_param("i", $id);
$stmtEventos->execute();
$eventos = $stmtEventos->get_result();

// Remover a hora da data
$data_formatada = date("Y-m-d", strtotime($colecao['created_date']));
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($colecao['title']) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<header class="top-bar-home">
    <div class="logo">
        <a href="homepage.php">
            <img src="images/logo.png" alt="Logo">
        </a>
    </div>

    <div class="search-bar">
        <input type="text" placeholder="Pesquisar por coleções, eventos ou tags">
        <button>🔍</button>
    </div>

    <div class="user-icon">
    <a href="perfil.php">
        <img src="<?php echo htmlspecialchars($user_photo ?? 'images/profile.png'); ?>" height="90" style="border-radius: 50%; object-fit: cover; width: 90px;">
    </a>
</div>
</header>

<main class="main-content">

    <!-- DETALHES DA COLEÇÃO -->
    <section class="collection-details">
        <h2>Coleção</h2>

        <div class="collection-info">
            <p><strong>Nome:</strong> <?= htmlspecialchars($colecao['title']) ?></p>
            <p><strong>Data de criação:</strong> <?= $data_formatada ?></p>
            <p><strong>Número de itens:</strong> <?= $total_itens ?></p>
            <p><strong>Descrição:</strong> <?= htmlspecialchars($colecao['description']) ?></p>

            <!-- ✅ TAGS (APENAS LISTAR) -->
            <p><strong>Tags:</strong>
                <?php if ($tags->num_rows > 0): ?>
                    <?php while ($tag = $tags->fetch_assoc()): ?>
                        <span class="tag"><?= htmlspecialchars($tag['tag_name']) ?></span>
                    <?php endwhile; ?>
                <?php else: ?>
                    <span>Sem tags</span>
                <?php endif; ?>
            </p>
        </div>

        <!-- ITENS DA COLEÇÃO -->
        <div class="collection-items">
            <h3>Itens:</h3>

            <div class="item-gallery">
    <?php while ($item = $itens->fetch_assoc()): ?>

        <a href="item.php?id=<?= (int)$item['id'] ?>">
            <img 
                src="<?= htmlspecialchars($item['image_path']) ?>" 
                alt="<?= htmlspecialchars($item['name']) ?>" 
                height="120"
            >
        </a>

    <?php endwhile; ?>
</div>
        </div>

        <!-- ✅ EVENTOS VOLTARAM A APARECER -->
        <div class="collection-events">
            <h3>Eventos da Coleção:</h3>

            <?php if ($eventos->num_rows > 0): ?>
                <?php while ($evento = $eventos->fetch_assoc()): ?>
                    <div class="mini-event-card">
                        <h4><?= htmlspecialchars($evento['name']) ?></h4>
                        <p>📅 <?= $evento['event_date'] ?> • <?= $evento['start_time'] ?></p>
                        <p>📍 <?= htmlspecialchars($evento['location']) ?></p>
                        <a href="evento.php?id=<?= $evento['id'] ?>">Ver detalhes →</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Sem eventos associados a esta coleção.</p>
            <?php endif; ?>
        </div>

    </section>

    <!-- BOTÕES LATERAIS -->
    <aside class="sidebar">
        <button class="btn-primary"
                onclick="window.location.href='add_item.php?collection_id=<?= $id ?>'">
            Adicionar item à coleção
        </button>

        <button class="btn-primary"
                onclick="window.location.href='editar_colecao.php?id=<?= $id ?>'">
            Editar coleção
        </button>

        <button class="btn-primary"
                onclick="window.location.href='php/delete_collection.php?id=<?= $id ?>'">
            Eliminar coleção
        </button>
    </aside>

</main>

<footer class="bottom-bar">
    <a href="desenvolvedores.php">DESENVOLVEDORES</a>
</footer>

</body>
</html>