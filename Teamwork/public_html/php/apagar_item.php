<?php
session_start();
// Estes caminhos estão corretos, pois apagar_item.php está dentro da pasta 'php'
require_once 'db.php';
require_once 'auth.php'; 

// 1. Verificar se o ID foi fornecido na URL
if (isset($_GET['id'])) {
    $item_id = $_GET['id'];
    $user_id = $_SESSION['user_id']; 
    
    // 2. Verificar Permissão e Obter ID da Coleção (para o redirecionamento)
    // CRUCIAL: Verificamos o 'user_id' através da tabela 'collections'
    $stmt = $conn->prepare("
        SELECT i.collection_id 
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

        // 3. Executar a Eliminação
        $stmt_del = $conn->prepare("DELETE FROM items WHERE id = ?");
        $stmt_del->bind_param("i", $item_id);
        
        if ($stmt_del->execute()) {
            // Sucesso: Redireciona de volta para a página da coleção
            // Usamos o caminho relativo para voltar à raiz (..) e aceder a 'colecao.php'
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