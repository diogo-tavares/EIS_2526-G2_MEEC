<?php
session_start();
// Estes caminhos estão corretos, pois apagar_item.php está dentro da pasta 'php'
require_once 'db.php';
require_once 'auth.php'; 

// 1. Verificar se o ID foi fornecido na URL
if (isset($_GET['id'])) {
    $item_id = $_GET['id'];
    $user_id = $_SESSION['user_id']; 
    
    // 2. Verificar Permissão, Obter ID da Coleção e CAMINHO DA IMAGEM
    // Alterámos o SELECT para trazer também o 'i.image_path'
    $stmt = $conn->prepare("
        SELECT i.collection_id, i.image_path
        FROM items i
        JOIN collections c ON i.collection_id = c.id
        WHERE i.id = ? AND c.user_id = ?
    ");
    $stmt->bind_param("ii", $item_id, $user_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $item = $resultado->fetch_assoc();
    $stmt->close();

    if ($item) {
        // O item existe e pertence a uma coleção deste utilizador.
        $collection_id = $item['collection_id'];

        // =================================================================
        // NOVO PASSO: APAGAR O FICHEIRO DE IMAGEM
        // =================================================================
        
        // O script está em 'php/', a imagem está em '../images/items/...'
        $file_path = "../" . $item['image_path'];

        // Verifica se existe caminho na BD e se o ficheiro existe no disco
        if (!empty($item['image_path']) && file_exists($file_path)) {
            unlink($file_path); // Apaga o ficheiro físico
        }

        // =================================================================

        // 3. Executar a Eliminação do registo na BD
        $stmt_del = $conn->prepare("DELETE FROM items WHERE id = ?");
        $stmt_del->bind_param("i", $item_id);
        
        if ($stmt_del->execute()) {
            // Sucesso: Redireciona de volta para a página da coleção
            header("Location: ../colecao.php?id=" . $collection_id);
            exit();
        } else {
            // Em caso de erro na query DELETE
            echo "<h1>Erro</h1>";
            echo "<p>Erro na base de dados ao tentar apagar: " . htmlspecialchars($conn->error) . "</p>";
            echo "<a href='../minhas_colecoes.php'>Voltar às minhas coleções</a>";
        }
        $stmt_del->close();

    } else {
        // Item não existe OU pertence a outro utilizador (segurança)
        echo "<h1>Erro</h1>";
        echo "<p>Item não encontrado ou sem permissão para eliminar.</p>";
        echo "<a href='../minhas_colecoes.php'>Voltar às minhas coleções</a>";
    }

} else {
    echo "ID do item em falta.";
}

$conn->close();
?>