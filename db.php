<?php
$host = "localhost";
$user = "root";      // standard for XAMPP
$pass = "";          // passord, sett hvis du har ett
$db   = "skole";     // databasen vi lagde

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Tilkoblingsfeil: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
