<?php
session_start();

// Define manualmente o ID do utilizador (1 = Admin do teu SQL)
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Admin (Modo Dev)';

// Redireciona logo para a tua página de eventos
header("Location: social.php");
exit();
?>