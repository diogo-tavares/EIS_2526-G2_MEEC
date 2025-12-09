<?php
session_start();
require_once 'db.php';
require_once 'auth.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $user_id = $_SESSION['user_id']; 
    
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ? AND creator_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    
    if ($stmt->execute()) {
        // Verifica se alguma linha foi realmente apagada
        if ($stmt->affected_rows > 0) {
            header("Location: ../eventos.php");
            exit();
        } else {
            // Se chegou aqui, o evento não existe OU pertence a outro user
            echo "Erro: Evento não encontrado ou sem permissão para eliminar.";
        }
    } else {
        echo "Erro SQL: " . $conn->error;
    }
} else {
    echo "ID não fornecido.";
}
?>