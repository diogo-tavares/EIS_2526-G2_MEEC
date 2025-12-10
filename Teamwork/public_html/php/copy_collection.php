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
    $new_title = $col_data['title'] . " (Cópia)";
    $desc = $col_data['description'];

    $stmt_insert = $conn->prepare("INSERT INTO collections (user_id, title, description, created_date, is_public) VALUES (?, ?, ?, NOW(), 0)");
    $stmt_insert->bind_param("iss", $my_id, $new_title, $desc);
    $stmt_insert->execute();
    $new_collection_id = $conn->insert_id;

    // 3. Buscar ITENS originais
    $stmt_items = $conn->prepare("SELECT name, acquisition_date, importance, price, weight, image_path FROM items WHERE collection_id = ?");
    $stmt_items->bind_param("i", $original_id);
    $stmt_items->execute();
    $res_items = $stmt_items->get_result();

    // Preparar INSERT dos itens
    $stmt_copy_item = $conn->prepare("INSERT INTO items (collection_id, name, acquisition_date, importance, price, weight, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");

    while ($item = $res_items->fetch_assoc()) {
        
        // --- LÓGICA DE CÓPIA DE IMAGEM ---
        $final_image_path = $item['image_path']; // Por defeito, mantém o antigo (backup)

        // Se existir uma imagem definida na BD
        if (!empty($item['image_path'])) {
            // Caminho físico atual (como estamos na pasta 'php/', temos de subir um nível com '../')
            $source_file = "../" . $item['image_path'];
            
            if (file_exists($source_file)) {
                // Gerar novo nome único para a cópia
                $extension = pathinfo($source_file, PATHINFO_EXTENSION);
                $new_filename = "images/items/" . uniqid("copy_") . "_" . time() . "." . $extension;
                $dest_file = "../" . $new_filename;

                // Tentar copiar o ficheiro físico
                if (copy($source_file, $dest_file)) {
                    // Se a cópia funcionar, guardamos o NOVO caminho na BD
                    $final_image_path = $new_filename;
                }
            }
        }
        // ----------------------------------

        $stmt_copy_item->bind_param("issidds", 
            $new_collection_id, 
            $item['name'], 
            $item['acquisition_date'], 
            $item['importance'], 
            $item['price'], 
            $item['weight'], 
            $final_image_path // Usa o caminho da imagem NOVA
        );
        $stmt_copy_item->execute();
    }

    // Confirmar tudo
    $conn->commit();
    
    header("Location: ../colecao.php?id=" . $new_collection_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "Erro ao copiar coleção: " . $e->getMessage();
}
?>