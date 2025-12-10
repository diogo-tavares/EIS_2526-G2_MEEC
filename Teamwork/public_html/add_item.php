<?php
session_start();
require_once 'php/db.php';
require_once 'php/auth.php';

// Se vier diretamente de uma coleção
$preSelected = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Buscar todas as coleções
$collections = $conn->query("SELECT id, title FROM collections");

// Se o formulário for submetido
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $collection_id = intval($_POST['collection_id']);
    $name = $_POST['name'];
    $date = $_POST['date'];
    $importance = intval($_POST['importance']);
    $weight = floatval($_POST['weight']);
    $price = floatval($_POST['price']);

    // Upload da imagem
    $image_path = null;

    if (!empty($_FILES["image"]["name"])) {

        $target_dir = "images/items/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $image_path = $target_dir . time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
    }

    // Inserir com prepared statement
    $stmt = $conn->prepare("
        INSERT INTO items 
        (collection_id, name, acquisition_date, importance, weight, price, image_path)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "issidds",
        $collection_id,
        $name,
        $date,
        $importance,
        $weight,
        $price,
        $image_path
    );

    $stmt->execute();

    // Voltar para a coleção
    header("Location: colecao.php?id=$collection_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Item</title>
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
        <input type="text" placeholder="Pesquisar">
        <button>🔍</button>
    </div>

    <div class="user-icon">
        <a href="perfil.php">
            <img src="images/profile.png" height="90">
        </a>
    </div>
</header>

<main class="add-item-content">
<h1>Adicionar item:</h1>

<section class="add-item-container">

<form method="POST" enctype="multipart/form-data" class="add-item-form">

    <label><strong>Coleção:</strong></label>
    <select name="collection_id" required>
        <option value="">Selecione uma coleção</option>
        <?php while ($c = $collections->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>"
                <?= ($c['id'] == $preSelected) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['title']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label><strong>Nome:</strong></label>
    <input type="text" name="name" required>

    <label><strong>Data de aquisição:</strong></label>
    <input type="date" name="date" required>

    <label><strong>Importância (1–10):</strong></label>
    <input type="number" name="importance" min="1" max="10" required>

    <label><strong>Peso (g):</strong></label>
    <input type="number" name="weight" required>

    <label><strong>Preço (€):</strong></label>
    <input type="number" name="price" step="0.01" required>

    <label><strong>Imagem:</strong></label>
    <input type="file" name="image" accept="image/*">

    <div class="add-item-buttons">
        <button type="submit" class="btn-primary">Confirmar</button>
        <button type="button" class="btn-primary"
            onclick="window.history.back()">Cancelar</button>
    </div>

</form>
</section>
</main>

<footer class="bottom-bar">
    <a href="desenvolvedores.php">DESENVOLVEDORES</a>
</footer>

</body>
</html>
