<?php
$host = "localhost";
$user = "root";
$pass = ""; // XAMPP default has no password
$dbname = "teamwork";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
