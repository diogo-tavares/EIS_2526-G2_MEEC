<?php
session_start();
require_once 'db.php';
require_once 'auth.php'; // Garante que o utilizador está logado

// 1. Verificar se o ID foi fornecido na URL
if (isset($_GET['id'])) {
    $col_id = $_GET['id'];
    $user_id = $_SESSION['user_id']; 
    
    // 2. Preparar a eliminação
    // A cláusula "AND user_id = ?" é CRUCIAL para a segurança.
    // Impede que um utilizador apague coleções de outros mudando o ID na URL.
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
