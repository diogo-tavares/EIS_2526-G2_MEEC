<?php
require_once __DIR__ . "/php/db.php";

$id = $_GET['id'] ?? 0;
$id = intval($id);

// Buscar item + nome da coleção
$sql = "
    SELECT items.*, collections.title AS collection_name
    FROM items
    JOIN collections ON items.collection_id = collections.id
    WHERE items.id = $id
";

$result = $conn->query($sql);
$item = $result->fetch_assoc();

if (!$item) {
    die("Item não encontrado.");
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($item['name']) ?></title>
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
        <input type="text" placeholder="Pesquisar">
        <button>🔍</button>
    </div>

    <div class="user-icon">
        <a href="perfil.php">
            <img src="images/profile.png" height="90">
        </a>
    </div>
</header>

<main class="item-page colecao-page">

<section class="item-details">
    <h2>Item</h2>

    <div class="item-info">
        <p><strong>Nome:</strong> <?= htmlspecialchars($item['name']) ?></p>

        <p><strong>Coleção:</strong>
            <a href="colecao.php?id=<?= $item['collection_id'] ?>">
                <?= htmlspecialchars($item['collection_name']) ?>
            </a>
        </p>

        <?php if (!empty($item['description'])): ?>
            <p><strong>Descrição:</strong> <?= htmlspecialchars($item['description']) ?></p>
        <?php endif; ?>

        <?php if (!empty($item['acquisition_date'])): ?>
            <p><strong>Data de aquisição:</strong> <?= $item['acquisition_date'] ?></p>
        <?php endif; ?>

        <?php if (!empty($item['importance'])): ?>
            <p><strong>Importância:</strong> <?= $item['importance'] ?></p>
        <?php endif; ?>

        <?php if (!empty($item['weight'])): ?>
            <p><strong>Peso (g):</strong> <?= $item['weight'] ?></p>
        <?php endif; ?>

        <?php if (!empty($item['price'])): ?>
            <p><strong>Preço (€):</strong> <?= $item['price'] ?></p>
        <?php endif; ?>

        <!-- ✅ IMAGEM CORRIGIDA -->
        <?php if (!empty($item['image_path'])): ?>
            <p>
                <strong>Imagem:</strong><br>
                <img src="<?= $item['image_path'] ?>" class="item-image">
            </p>
        <?php endif; ?>
    </div>
</section>

<aside class="item-sidebar">
    <button class="btn-primary"
        onclick="window.location.href='editar_item.php?id=<?= $item['id'] ?>'">
        Editar item
    </button>

    <button class="btn-primary"
        onclick="window.location.href='apagar_item.php?id=<?= $item['id'] ?>'">
        Eliminar item
    </button>
</aside>

</main>

<footer class="bottom-bar">
    <a href="desenvolvedores.php">DESENVOLVEDORES</a>
</footer>

</body>
</html>
