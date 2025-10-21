<?php
$host = "farah6535";
$user = "farah6535";      // standard for XAMPP
$pass = "a999farah6535";          // passord, sett hvis du har ett
$db   = "farah6535";     // databasen vi lagde

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Tilkoblingsfeil: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
