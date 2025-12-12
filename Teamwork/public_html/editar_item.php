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

// Validar ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("ID inválido.");
}

// Buscar item APENAS se pertencer ao utilizador
$stmt = $conn->prepare("
    SELECT items.*
    FROM items
    JOIN collections ON items.collection_id = collections.id
    WHERE items.id = ? AND collections.user_id = ?
");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    die("Item não encontrado ou não tem permissão.");
}

// Buscar coleções APENAS do utilizador
$stmtCols = $conn->prepare("
    SELECT id, title FROM collections WHERE user_id = ?
");
$stmtCols->bind_param("i", $user_id);
$stmtCols->execute();
$collections = $stmtCols->get_result();

// Se o formulário for submetido
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $collection_id = intval($_POST['collection_id']);
    $name = trim($_POST['name']);
    $date = $_POST['date'];
    $importance = intval($_POST['importance']);
    $weight = floatval($_POST['weight']);
    $price = floatval($_POST['price']);

    // ✅ Garantir que a coleção pertence ao utilizador
    $check = $conn->prepare("
        SELECT id FROM collections 
        WHERE id = ? AND user_id = ?
    ");
    $check->bind_param("ii", $collection_id, $user_id);
    $check->execute();
    $checkRes = $check->get_result();

    if ($checkRes->num_rows === 0) {
        die("Tentativa inválida de mover item para outra coleção.");
    }

    // Manter imagem antiga por defeito
    $image_path = $item['image_path'];

    // Se for enviada nova imagem
    if (!empty($_FILES["image"]["name"])) {

        // =========================================================
        // 1. LÓGICA ADICIONADA: APAGAR A IMAGEM ANTIGA (Se existir)
        // =========================================================
        // Verifica se há um caminho guardado na BD e se o ficheiro físico existe
        if (!empty($item['image_path']) && file_exists($item['image_path'])) {
            unlink($item['image_path']); // Apaga o ficheiro antigo do servidor
        }
        // =========================================================

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
            <img src="<?php echo htmlspecialchars($user_photo); ?>" alt="Perfil" height="90" style="border-radius: 50%; object-fit: cover; width: 90px;">
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
    <img src="<?= htmlspecialchars($item['image_path']) ?>" id="item-preview-img" width="180" style="border-radius: 8px;"><br><br>

    <label><strong>Nova imagem (opcional):</strong></label>
    <input type="file" name="image" accept="image/*" id="item-image-input">

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

<script src="js/add_edit_item.js"></script>    
</body>
</html>