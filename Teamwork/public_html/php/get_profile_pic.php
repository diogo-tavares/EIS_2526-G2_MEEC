<?php
require_once 'db.php';
require_once 'auth.php';

if (isset($_SESSION['user_id'])) {
    $uid_pic = $_SESSION['user_id'];
    
    // Preparar a query
    $stmt_pic = $conn->prepare("SELECT photo_path FROM users WHERE id = ?");
    $stmt_pic->bind_param("i", $uid_pic);
    
    if ($stmt_pic->execute()) {
        $res_pic = $stmt_pic->get_result();
        
        if ($row_pic = $res_pic->fetch_assoc()) {
            // Verifica se o campo não está vazio
            if (!empty($row_pic['photo_path'])) {
                $user_photo = $row_pic['photo_path'];
            }
        }
    }
    $stmt_pic->close();
}
?>