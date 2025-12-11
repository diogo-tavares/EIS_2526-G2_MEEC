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

// Se vier diretamente de uma coleção
$preSelected = isset($_GET['collection_id']) ? intval($_GET['collection_id']) : 0;

// Buscar APENAS as coleções do utilizador
$stmtCols = $conn->prepare("
    SELECT id, title 
    FROM collections 
    WHERE user_id = ?
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
        die("Tentativa inválida de inserir item noutra coleção.");
    }

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
    <script src="js/pesquisa.js" defer></script>
</head>

<body>

<header class="top-bar-home">
    <div class="logo">
        <a href="homepage.php">
            <img src="images/logo.png" alt="Logo do Sistema">
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
    <input type="file" name="image" accept="image/*" id="item-image-input">

    <img id="item-preview-img" src="#" alt="Pré-visualização" style="display:none; max-width: 200px; margin-top: 10px; border-radius: 8px;">

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
    
    
<script src="js/add_edit_item.js"></script>

</body>
</html>
