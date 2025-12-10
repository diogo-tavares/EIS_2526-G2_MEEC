<?php
session_start();
require_once 'db.php';
require_once 'auth.php';

// validar ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die('ID inválido.');
}

$itemId = (int) $_GET['id'];

// 1) descobrir a coleção deste item (para depois voltar para lá)
$stmt = $conn->prepare("SELECT collection_id FROM items WHERE id = ?");
$stmt->bind_param("i", $itemId);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

if (!$item) {
    die('Item não encontrado.');
}

$collectionId = (int) $item['collection_id'];

// 2) apagar o item
$stmtDel = $conn->prepare("DELETE FROM items WHERE id = ?");
$stmtDel->bind_param("i", $itemId);
$stmtDel->execute();
$stmtDel->close();

// 3) voltar para a página da coleção
header("Location: ../colecao.php?id=" . $collectionId);
exit;

