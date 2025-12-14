<?php
session_start();
require_once 'db.php';
require_once 'auth.php';

$user_id = $_SESSION['user_id'];

if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, "r");

    if ($handle !== FALSE) {
        // Ignorar cabeçalho
        fgetcsv($handle, 1000, ",");

        // Preparar Queries
        $stmt = $conn->prepare("INSERT INTO collections (user_id, title, description, created_date) VALUES (?, ?, ?, NOW())");
        $stmt_tag = $conn->prepare("INSERT INTO collection_tags (collection_id, tag_name) VALUES (?, ?)");

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // CSV Esperado: [0]Titulo, [1]Descricao, [2]Data(ignorado), [3]Tags
            $title = $data[0];
            $desc = $data[1];
            // Verifica se existe a coluna das tags
            $tags_str = isset($data[3]) ? $data[3] : ''; 

            if (!empty($title)) {
                // 1. Criar Coleção
                $stmt->bind_param("iss", $user_id, $title, $desc);
                
                if ($stmt->execute()) {
                    $new_coll_id = $conn->insert_id;

                    // 2. Inserir Tags (se existirem)
                    if (!empty($tags_str)) {
                        // Separa por vírgula
                        $tags_array = explode(",", $tags_str);
                        
                        foreach ($tags_array as $tag) {
                            $tag = trim($tag); // Remove espaços extra
                            if (!empty($tag)) {
                                $stmt_tag->bind_param("is", $new_coll_id, $tag);
                                $stmt_tag->execute();
                            }
                        }
                    }
                }
            }
        }
        fclose($handle);
        
        header("Location: ../minhas_colecoes.php?msg=import_success");
        exit();
    }
}

header("Location: ../minhas_colecoes.php?msg=import_error");
exit();
?>