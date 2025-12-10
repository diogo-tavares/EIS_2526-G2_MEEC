<?php
session_start();
require_once 'db.php';
require_once 'auth.php';

if (!isset($_GET['id'])) {
    die("ID inválido");
}

$original_id = intval($_GET['id']);
$my_id = $_SESSION['user_id'];

// 1. Buscar dados do evento original (Público)
$sql = "SELECT name, location, event_date, start_time, price, description 
        FROM events WHERE id = ? AND is_public = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $original_id);
$stmt->execute();
$res = $stmt->get_result();
$evt_data = $res->fetch_assoc();

if (!$evt_data) {
    die("Evento não encontrado ou privado.");
}

// 2. Criar CÓPIA para o utilizador atual
// A cópia nasce privada (is_public = 0)
$new_name = $evt_data['name'] . " (Importado)";

$sql_insert = "INSERT INTO events (creator_id, name, location, event_date, start_time, price, description, is_public) 
               VALUES (?, ?, ?, ?, ?, ?, ?, 0)";

$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("issssds", 
    $my_id,
    $new_name,
    $evt_data['location'],
    $evt_data['event_date'],
    $evt_data['start_time'],
    $evt_data['price'],
    $evt_data['description']
);

if ($stmt_insert->execute()) {
    $new_event_id = $conn->insert_id;
    // Redireciona para a página do NOVO evento (para poderes ver e editar)
    header("Location: ../evento.php?id=" . $new_event_id);
    exit();
} else {
    echo "Erro ao copiar evento: " . $conn->error;
}
?>