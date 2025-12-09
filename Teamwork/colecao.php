<?php
require_once __DIR__ . "/php/db.php";

// Validar ID da coleção
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("ID inválido.");
}

// Buscar dados da coleção
$stmt = $conn->prepare("
    SELECT id, title, description, created_date
    FROM collections
    WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$colecao = $result->fetch_assoc();

if (!$colecao) {
    die("Coleção não encontrada.");
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

// Número de itens
$total_itens = $itens->num_rows;
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
            <img src="images/profile.png" height="90" alt="Perfil">
        </a>
    </div>
</header>

<main class="main-content">

    <!-- DETALHES DA COLEÇÃO -->
    <section class="collection-details">
        <h2>Coleção</h2>

        <div class="collection-info">
            <p><strong>Nome:</strong> <?= htmlspecialchars($colecao['title']) ?></p>
            <p><strong>Data de criação:</strong> <?= $colecao['created_date'] ?></p>
            <p><strong>Número de itens:</strong> <?= $total_itens ?></p>
            <p><strong>Descrição:</strong> <?= htmlspecialchars($colecao['description']) ?></p>
        </div>

        <!-- ITENS DA COLEÇÃO -->
        <div class="collection-items">
            <h3>Itens:</h3>

            <div class="item-gallery">
                <?php while ($item = $itens->fetch_assoc()): ?>
                    <a href="item.php?id=<?= $item['id'] ?>">
                        <img src="<?= htmlspecialchars($item['image_path']) ?>"
                             alt="<?= htmlspecialchars($item['name']) ?>">
                    </a>
                <?php endwhile; ?>
            </div>
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
                onclick="window.location.href='apagar_colecao.php?id=<?= $id ?>'">
            Eliminar coleção
        </button>
    </aside>

</main>

<footer class="bottom-bar">
    <a href="desenvolvedores.php">DESENVOLVEDORES</a>
</footer>

</body>
</html>
