<?php
session_start();
require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['followed_id'])) {
        echo json_encode(['success' => false, 'message' => 'ID em falta']);
        exit;
    }

    $follower_id = $_SESSION['user_id'];
    $followed_id = intval($data['followed_id']);

    if ($follower_id === $followed_id) {
        echo json_encode(['success' => false, 'message' => 'Não te podes seguir a ti mesmo']);
        exit;
    }

    // Verificar se já segue
    $check = $conn->prepare("SELECT 1 FROM user_follows WHERE follower_id = ? AND followed_id = ?");
    $check->bind_param("ii", $follower_id, $followed_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Já segue -> REMOVER (Deixar de seguir)
        $stmt = $conn->prepare("DELETE FROM user_follows WHERE follower_id = ? AND followed_id = ?");
        $stmt->bind_param("ii", $follower_id, $followed_id);
        $stmt->execute();
        $action = 'unfollowed';
    } else {
        // Não segue -> ADICIONAR (Seguir)
        $stmt = $conn->prepare("INSERT INTO user_follows (follower_id, followed_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $follower_id, $followed_id);
        $stmt->execute();
        $action = 'followed';
    }

    echo json_encode(['success' => true, 'action' => $action]);
    exit;
}
?>