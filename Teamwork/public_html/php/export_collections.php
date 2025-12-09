<?php
session_start();
require_once 'db.php';
require_once 'auth.php';

$user_id = $_SESSION['user_id'];

// Definir cabeçalhos para forçar download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=minhas_colecoes.csv');

// Abrir output stream
$output = fopen('php://output', 'w');

// Para o Excel reconhecer UTF-8
fwrite($output, "\xEF\xBB\xBF");

// Escrever cabeçalho das colunas (CSV Header)
fputcsv($output, ['Titulo', 'Descricao', 'Data Criacao']);

// Buscar dados
$stmt = $conn->prepare("SELECT title, description, created_date FROM collections WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Escrever linhas
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>