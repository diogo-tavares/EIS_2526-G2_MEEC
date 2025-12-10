<?php
session_start();
require_once 'php/db.php';
require_once 'php/auth.php';
require_once 'php/get_profile_pic.php'; // Para a foto de perfil no canto superior

// Obter ID do utilizador logado
$user_id = $_SESSION['user_id'];

// Buscar as coleções deste utilizador
// Ordenado pela data de criação (mais recente primeiro)

$sql = "SELECT * FROM collections WHERE user_id = ? ORDER BY created_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
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

    <?php if ($result->num_rows === 0): ?>
        <p>⛔ Ainda não criaste nenhuma coleção.</p>
    <?php endif; ?>

    <?php while ($row = $result->fetch_assoc()): ?>

        <div class="collection">
            <a href="colecao.php?id=<?= $row['id'] ?>">
                <?= htmlspecialchars($row['title']) ?>
            </a>

            <div class="items">
                <?php
                $id = $row['id'];
                $stmtImg = $conn->prepare("
                    SELECT image_path 
                    FROM items 
                    WHERE collection_id = ? 
                    LIMIT 3
                ");
                $stmtImg->bind_param("i", $id);
                $stmtImg->execute();
                $imgs = $stmtImg->get_result();

                while ($img = $imgs->fetch_assoc()):
                ?>
                    <img src="<?= htmlspecialchars($img['image_path']) ?>" height="80">
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
