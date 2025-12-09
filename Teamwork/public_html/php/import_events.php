<?php
session_start();
require_once 'db.php';
require_once 'auth.php';

$user_id = $_SESSION['user_id'];

if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, "r");

    if ($handle !== FALSE) {
        // Ignorar Header
        fgetcsv($handle, 1000, ",");

        $stmt = $conn->prepare("INSERT INTO events (creator_id, name, location, event_date, start_time, price, description) VALUES (?, ?, ?, ?, ?, ?, ?)");

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // CSV: [0]Nome, [1]Local, [2]Data, [3]Hora, [4]Preco, [5]Desc
            $name = $data[0];
            $location = $data[1];
            $date = $data[2];     // Formato deve ser YYYY-MM-DD
            $time = $data[3];     // HH:MM:SS
            $price = floatval($data[4]);
            $desc = $data[5];

            if (!empty($name) && !empty($date)) {
                $stmt->bind_param("issssds", $user_id, $name, $location, $date, $time, $price, $desc);
                $stmt->execute();
            }
        }
        fclose($handle);
        header("Location: ../eventos.php?msg=import_success");
        exit();
    }
}

header("Location: ../eventos.php?msg=import_error");
exit();
?>