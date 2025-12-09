<?php
session_start();
require_once 'db.php';
require_once 'auth.php'; // Garante que está logado

// Ler os dados
$dados = json_decode(file_get_contents('php://input'), true);

if (isset($dados['event_id']) && isset($dados['presence'])) {
    
    $event_id = $dados['event_id'];
    $presence = $dados['presence']; 
    $rating = (isset($dados['rating']) && $dados['rating'] !== "") ? $dados['rating'] : NULL;
    $user_id = $_SESSION['user_id']; // O ID de quem está logado

    // CORREÇÃO: Adicionado "AND creator_id = ?" na cláusula WHERE
    $sql = "UPDATE events SET is_present = ?, rating = ? WHERE id = ? AND creator_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $presence, $rating, $event_id, $user_id); // Adicionado mais um 'i' para o user_id

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0 || $stmt->errno == 0) {
            // Sucesso (mesmo que os dados sejam iguais aos que já lá estavam)
            echo json_encode(['sucesso' => true]);
        } else {
            // Se falhar aqui, provavelmente o evento é de outro user
            echo json_encode(['sucesso' => false, 'erro' => 'Sem permissão ou evento não existe']);
        }
    } else {
        echo json_encode(['sucesso' => false, 'erro' => $conn->error]);
    }
} else {
    echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos']);
}
?>