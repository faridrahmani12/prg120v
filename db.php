<?php
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$config = dbConfig();

try {
    $conn = new mysqli(
        $config['host'],
        $config['user'],
        $config['pass'],
        $config['name'],
        $config['port']
    );
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    $friendly = "Tilkoblingsfeil: " . $e->getMessage() .
        ". Kontroller databasedetaljene i config.php eller miljøvariabler, eller kjør setup_db.php.";
    die($friendly);
}
?>
