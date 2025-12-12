<?php
session_start();
require_once 'db.php';
require_once 'auth.php'; // Garante que o utilizador está logado

// 1. Verificar se o ID foi fornecido na URL
if (isset($_GET['id'])) {
    $col_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // =================================================================
    // NOVO PASSO: ELIMINAR FICHEIROS DE IMAGEM DOS ITENS
    // =================================================================

    // A) Buscar os caminhos das imagens dos itens. Join com 'collections' para verificar user_id por segurança.
    $sql_imgs = "
        SELECT i.image_path
        FROM items i
        JOIN collections c ON i.collection_id = c.id
        WHERE c.id = ? AND c.user_id = ?
    ";
    $stmt_imgs = $conn->prepare($sql_imgs);
    $stmt_imgs->bind_param("ii", $col_id, $user_id);
    $stmt_imgs->execute();
    $result_imgs = $stmt_imgs->get_result();

    // B) Apagar os ficheiros físicos do servidor
    while ($row = $result_imgs->fetch_assoc()) {
        // O script está em 'php/', a pasta de imagens está em '../images/items/'
        $file_path = "../" . $row['image_path'];

        // Verificar se o caminho não está vazio e se o ficheiro existe antes de apagar
        if (!empty($row['image_path']) && file_exists($file_path)) {
            unlink($file_path); // Apaga o ficheiro
        }
    }
    $stmt_imgs->close();

    // =================================================================
    // FIM DA ELIMINAÇÃO DE FICHEIROS
    // =================================================================

    // 2. Preparar a eliminação da coleção na BD
    // A cláusula "AND user_id = ?" é CRUCIAL para a segurança.
    // O ON DELETE CASCADE na BD vai apagar todos os registos dos itens e tags.
    $stmt = $conn->prepare("DELETE FROM collections WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $col_id, $user_id);

    // 3. Executar
    if ($stmt->execute()) {
        // Verifica se alguma linha foi realmente apagada
        if ($stmt->affected_rows > 0) {
            // Sucesso: Redireciona para a lista de coleções
            header("Location: ../minhas_colecoes.php?msg=deleted");
            exit();
        } else {
            // Se chegou aqui, a coleção não existe OU pertence a outro utilizador
            echo "Erro: Coleção não encontrada ou sem permissão para eliminar.";
            echo "<br><a href='../minhas_colecoes.php'>Voltar</a>";
        }
    } else {
        echo "Erro na base de dados: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "ID da coleção em falta.";
}

$conn->close();
?>