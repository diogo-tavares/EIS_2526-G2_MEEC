<?php
session_start();
require_once 'db.php';
require_once 'auth.php';

$user_id = $_SESSION['user_id'];

if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, "r");

    if ($handle !== FALSE) {
        // Ignorar a primeira linha (cabeçalho)
        fgetcsv($handle, 1000, ",");

        // Preparar Query
        $stmt = $conn->prepare("INSERT INTO collections (user_id, title, description, created_date) VALUES (?, ?, ?, NOW())");

        // Ler linha a linha
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Estrutura esperada do CSV: [0]Titulo, [1]Descricao
            $title = $data[0];
            $desc = $data[1];
            
            // Validação simples
            if (!empty($title)) {
                $stmt->bind_param("iss", $user_id, $title, $desc);
                $stmt->execute();
            }
        }
        fclose($handle);
        
        // Sucesso
        header("Location: ../minhas_colecoes.php?msg=import_success");
        exit();
    }
}

// Erro
header("Location: ../minhas_colecoes.php?msg=import_error");
exit();
?>