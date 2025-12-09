<?php
session_start();
require_once 'db.php';
require_once 'auth.php';

$user_id = $_SESSION['user_id'];

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=meus_eventos.csv');

$output = fopen('php://output', 'w');

// Para o Excel reconhecer UTF-8
fwrite($output, "\xEF\xBB\xBF");

// Header do CSV
fputcsv($output, ['Nome', 'Localizacao', 'Data', 'Hora', 'Preco', 'Descricao']);

// Buscar dados
$stmt = $conn->prepare("SELECT name, location, event_date, start_time, price, description FROM events WHERE creator_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>