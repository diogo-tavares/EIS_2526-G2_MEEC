<?php
require_once __DIR__ . "/php/db.php";

// Buscar últimas 5 coleções
$sql = "SELECT id, title, description FROM collections ORDER BY id DESC LIMIT 5";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Hub de Coleções</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="top-bar-home">
    <div class="logo">
        <a href="homepage.php">
            <img src="images/logo.png">
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

<main class="main-content">

<section class="latest-collections">
    <h2>Últimas coleções adicionadas:</h2>

    <?php while ($row = $result->fetch_assoc()): ?>

        <div class="collection">
            <a href="colecao.php?id=<?= $row['id'] ?>">
                <?= htmlspecialchars($row['title']) ?>
            </a>

            <div class="items">
                <?php
                $id = $row['id'];
                $imgSql = "SELECT image_path FROM items WHERE collection_id=$id LIMIT 3";
                $imgs = $conn->query($imgSql);

                while ($img = $imgs->fetch_assoc()):
                ?>
                    <img src="<?= $img['image_path'] ?>" height="80">
                <?php endwhile; ?>
            </div>
        </div>

    <?php endwhile; ?>

</section>

<aside class="sidebar">
    <button onclick="location.href='minhas_colecoes.php'">Minhas coleções</button>
    <button onclick="location.href='add_colecao.php'">Adicionar coleção</button>
</aside>

</main>

<footer class="bottom-bar">
    <a href="desenvolvedores.php">DESENVOLVEDORES</a>
</footer>

</body>
</html>
