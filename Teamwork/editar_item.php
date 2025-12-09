<?php
require_once __DIR__ . "/php/db.php";

// Validar ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("ID inválido.");
}

// Buscar item
$stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    die("Item não encontrado.");
}

// Buscar coleções
$collections = $conn->query("SELECT id, title FROM collections");

// Se o formulário for submetido
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $collection_id = intval($_POST['collection_id']);
    $name = $_POST['name'];
    $date = $_POST['date'];
    $importance = intval($_POST['importance']);
    $weight = floatval($_POST['weight']);
    $price = floatval($_POST['price']);

    // Manter imagem antiga por defeito
    $image_path = $item['image_path'];

    // Se for enviada nova imagem
    if (!empty($_FILES["image"]["name"])) {

        $target_dir = "images/items/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $image_path = $target_dir . time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
    }

    // Update com prepared statement
    $stmtUpdate = $conn->prepare("
        UPDATE items SET
            collection_id = ?,
            name = ?,
            acquisition_date = ?,
            importance = ?,
            weight = ?,
            price = ?,
            image_path = ?
        WHERE id = ?
    ");

    $stmtUpdate->bind_param(
        "issiddsi",
        $collection_id,
        $name,
        $date,
        $importance,
        $weight,
        $price,
        $image_path,
        $id
    );

    $stmtUpdate->execute();

    // Voltar para o item
    header("Location: item.php?id=$id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Editar Item</title>
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

<main class="add-item-content">
<h1>Editar item:</h1>

<section class="add-item-container">

<form method="POST" enctype="multipart/form-data" class="add-item-form">

    <label><strong>Coleção:</strong></label>
    <select name="collection_id" required>
        <?php while ($c = $collections->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>"
                <?= ($c['id'] == $item['collection_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['title']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label><strong>Nome:</strong></label>
    <input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>

    <label><strong>Data de aquisição:</strong></label>
    <input type="date" name="date" value="<?= $item['acquisition_date'] ?>" required>

    <label><strong>Importância (1–10):</strong></label>
    <input type="number" name="importance" min="1" max="10"
           value="<?= $item['importance'] ?>" required>

    <label><strong>Peso (g):</strong></label>
    <input type="number" name="weight" value="<?= $item['weight'] ?>" required>

    <label><strong>Preço (€):</strong></label>
    <input type="number" step="0.01" name="price" value="<?= $item['price'] ?>" required>

    <label><strong>Imagem atual:</strong></label><br>
    <img src="<?= $item['image_path'] ?>" width="180"><br><br>

    <label><strong>Nova imagem (opcional):</strong></label>
    <input type="file" name="image" accept="image/*">

    <div class="add-item-buttons">
        <button type="submit" class="btn-primary">Guardar alterações</button>
        <button type="button" class="btn-primary"
            onclick="window.location.href='item.php?id=<?= $item['id'] ?>'">
            Cancelar
        </button>
    </div>

</form>
</section>
</main>

<footer class="bottom-bar">
    <a href="desenvolvedores.php">DESENVOLVEDORES</a>
</footer>

</body>
</html>
