<?php
session_start();
require_once 'php/db.php';
require_once 'php/auth.php';
require_once 'php/get_profile_pic.php';

$user_id = $_SESSION["user_id"];

$id = $_GET['id'] ?? 0;
$id = intval($id);

// Buscar item + nome da coleção COM SEGURANÇA
// Adicionámos "AND collections.user_id = $user_id" para garantir que o item é teu
$sql = "
    SELECT items.*, collections.title AS collection_name
    FROM items
    JOIN collections ON items.collection_id = collections.id
    WHERE items.id = $id AND collections.user_id = $user_id
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
    <script src="js/pesquisa.js" defer></script>
</head>

<body>

<header class="top-bar-home">
    <div class="logo">
        <a href="homepage.php">
            <img src="images/logo.png" alt="Logo">
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
        onclick="window.location.href='php/apagar_item.php?id=<?= $item['id'] ?>'">
        Eliminar item
    </button>
</aside>

</main>

<footer class="bottom-bar">
    <a href="desenvolvedores.php">DESENVOLVEDORES</a>
</footer>

</body>
</html>
