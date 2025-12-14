<?php
session_start();
require_once 'db.php';
require_once 'auth.php';

$user_id = $_SESSION['user_id'];

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=minhas_colecoes.csv');

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM

// Header do CSV (Adicionei 'Tags')
fputcsv($output, ['Titulo', 'Descricao', 'Data Criacao', 'Tags']);

// Buscar dados (incluindo ID para buscar as tags)
$stmt = $conn->prepare("SELECT id, title, description, created_date FROM collections WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    // Buscar tags desta coleção
    $stmt_t = $conn->prepare("SELECT tag_name FROM collection_tags WHERE collection_id = ?");
    $stmt_t->bind_param("i", $row['id']);
    $stmt_t->execute();
    $res_t = $stmt_t->get_result();
    
    $tags = [];
    while($t = $res_t->fetch_assoc()) {
        $tags[] = $t['tag_name'];
    }
    // Junta as tags com vírgula (ex: "carro, classico, vermelho")
    $tags_str = implode(", ", $tags);

    // Escreve no CSV (Titulo, Descricao, Data, Tags)
    fputcsv($output, [$row['title'], $row['description'], $row['created_date'], $tags_str]);
}

fclose($output);
exit();
?>