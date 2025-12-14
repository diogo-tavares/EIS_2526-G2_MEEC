<?php
session_start();
require_once 'db.php';
require_once 'auth.php';

if (!isset($_GET['id'])) {
    die("ID inválido");
}

$original_id = intval($_GET['id']);
$my_id = $_SESSION['user_id'];

// 1. Buscar dados da coleção original (Pública)
$sql = "SELECT title, description FROM collections WHERE id = ? AND is_public = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $original_id);
$stmt->execute();
$res = $stmt->get_result();
$col_data = $res->fetch_assoc();

if (!$col_data) {
    die("Coleção não encontrada ou privada.");
}

// Iniciar Transação
$conn->begin_transaction();

try {
    // 2. Criar a NOVA coleção
    $new_title = $col_data['title'] . " (Importada)";
    $desc = $col_data['description'];

    $stmt_insert = $conn->prepare("INSERT INTO collections (user_id, title, description, created_date, is_public) VALUES (?, ?, ?, NOW(), 0)");
    $stmt_insert->bind_param("iss", $my_id, $new_title, $desc);
    $stmt_insert->execute();
    $new_collection_id = $conn->insert_id;

    // --- NOVO: COPIAR TAGS ---
    $stmt_tags = $conn->prepare("SELECT tag_name FROM collection_tags WHERE collection_id = ?");
    $stmt_tags->bind_param("i", $original_id);
    $stmt_tags->execute();
    $res_tags = $stmt_tags->get_result();

    $stmt_add_tag = $conn->prepare("INSERT INTO collection_tags (collection_id, tag_name) VALUES (?, ?)");
    while ($t = $res_tags->fetch_assoc()) {
        $stmt_add_tag->bind_param("is", $new_collection_id, $t['tag_name']);
        $stmt_add_tag->execute();
    }
    // -------------------------

    // 3. Buscar ITENS originais
    $stmt_items = $conn->prepare("SELECT name, acquisition_date, importance, price, weight, image_path FROM items WHERE collection_id = ?");
    $stmt_items->bind_param("i", $original_id);
    $stmt_items->execute();
    $res_items = $stmt_items->get_result();

    // Preparar INSERT dos itens
    $stmt_copy_item = $conn->prepare("INSERT INTO items (collection_id, name, acquisition_date, importance, price, weight, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");

    while ($item = $res_items->fetch_assoc()) {
        
        // --- LÓGICA DE CÓPIA DE IMAGEM ---
        $final_image_path = $item['image_path']; 

        if (!empty($item['image_path'])) {
            $source_file = "../" . $item['image_path'];
            if (file_exists($source_file)) {
                $extension = pathinfo($source_file, PATHINFO_EXTENSION);
                $new_filename = "images/items/" . uniqid("copy_") . "_" . time() . "." . $extension;
                $dest_file = "../" . $new_filename;

                if (copy($source_file, $dest_file)) {
                    $final_image_path = $new_filename;
                }
            }
        }

        $stmt_copy_item->bind_param("issidds", 
            $new_collection_id, 
            $item['name'], 
            $item['acquisition_date'], 
            $item['importance'], 
            $item['price'], 
            $item['weight'], 
            $final_image_path
        );
        $stmt_copy_item->execute();
    }

    $conn->commit();
    header("Location: ../colecao.php?id=" . $new_collection_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "Erro ao copiar coleção: " . $e->getMessage();
}
?>