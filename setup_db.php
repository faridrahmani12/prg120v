<?php
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$config = dbConfig();
$messages = [];

try {
    $serverConn = new mysqli(
        $config['host'],
        $config['user'],
        $config['pass'],
        '',
        $config['port']
    );
    $serverConn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die("Feil ved tilkobling til MySQL-serveren: " . $e->getMessage());
}

$dbName = $config['name'];
if (!preg_match('/^[A-Za-z0-9_]+$/', $dbName)) {
    die("Databasenavnet '$dbName' inneholder ugyldige tegn. Tillatte tegn er bokstaver, tall og _. ");
}

$serverConn->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$messages[] = "Database `$dbName` er klar.";
$serverConn->select_db($dbName);

$serverConn->query("
    CREATE TABLE IF NOT EXISTS klasse (
        klassekode VARCHAR(10) PRIMARY KEY,
        klassenavn VARCHAR(100) NOT NULL,
        studiumkode VARCHAR(50) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$messages[] = "Tabell `klasse` er klar.";

$kolonneSjekk = $serverConn->query("SHOW COLUMNS FROM klasse LIKE 'klasssenavn'");
if ($kolonneSjekk && $kolonneSjekk->num_rows === 1) {
    $serverConn->query("ALTER TABLE klasse CHANGE klasssenavn klassenavn VARCHAR(100) NOT NULL");
    $messages[] = "Rettet kolonnenavnet i `klasse`-tabellen til `klassenavn`.";
}

$serverConn->query("
    CREATE TABLE IF NOT EXISTS student (
        brukernavn VARCHAR(20) PRIMARY KEY,
        fornavn VARCHAR(100) NOT NULL,
        etternavn VARCHAR(100) NOT NULL DEFAULT '',
        klassekode VARCHAR(10) NOT NULL,
        CONSTRAINT fk_student_klasse
            FOREIGN KEY (klassekode)
            REFERENCES klasse(klassekode)
            ON UPDATE CASCADE
            ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$messages[] = "Tabell `student` er klar.";

$klasserSeed = [
    ['IT1', 'IT og ledelse 1. år', 'ITLED'],
    ['IT2', 'IT og ledelse 2. år', 'ITLED'],
    ['IT3', 'IT og ledelse 3. år', 'ITLED'],
];
$stmtKlasse = $serverConn->prepare("
    INSERT INTO klasse (klassekode, klassenavn, studiumkode)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE
        klassenavn = VALUES(klassenavn),
        studiumkode = VALUES(studiumkode)
");
foreach ($klasserSeed as $klasse) {
    $stmtKlasse->bind_param('sss', $klasse[0], $klasse[1], $klasse[2]);
    $stmtKlasse->execute();
}
$stmtKlasse->close();
$messages[] = "Standard klasser er lagt inn/oppdatert.";

$studenterSeed = [
    ['gb', 'Geir', 'Bjarvin', 'IT1'],
    ['mrj', 'Marius R.', 'Johannesen', 'IT1'],
    ['tb', 'Tove', 'Boe', 'IT2'],
];
$stmtStudent = $serverConn->prepare("
    INSERT INTO student (brukernavn, fornavn, etternavn, klassekode)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        fornavn = VALUES(fornavn),
        etternavn = VALUES(etternavn),
        klassekode = VALUES(klassekode)
");
foreach ($studenterSeed as $student) {
    $stmtStudent->bind_param('ssss', $student[0], $student[1], $student[2], $student[3]);
    $stmtStudent->execute();
}
$stmtStudent->close();
$messages[] = "Eksempelstudenter er lagt inn/oppdatert.";

$serverConn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Databaseoppsett</title>
</head>
<body>
<h1>Databaseoppsett fullført</h1>
<ul>
    <?php foreach ($messages as $message): ?>
        <li><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></li>
    <?php endforeach; ?>
</ul>
<p><a href="index.php">Tilbake til hovedsiden</a></p>
</body>
</html>
