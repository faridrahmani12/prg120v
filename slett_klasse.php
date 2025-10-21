<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require 'db.php';

$kode = trim($_GET['kode'] ?? '');

if ($kode === '') {
    $_SESSION['flash'] = ['msg' => 'Ugyldig kode for sletting.', 'class' => 'alert error'];
    header('Location: klasse.php');
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM klasse WHERE klassekode = ?");
    $stmt->bind_param('s', $kode);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['flash'] = ['msg' => 'Klasse slettet!', 'class' => 'alert'];
    } else {
        $_SESSION['flash'] = ['msg' => 'Fant ingen klasse med den gitte kode.', 'class' => 'alert error'];
    }

    $stmt->close();
} catch (mysqli_sql_exception $e) {
    $_SESSION['flash'] = ['msg' => 'Feil ved sletting: ' . $e->getMessage(), 'class' => 'alert error'];
}

header('Location: klasse.php');
exit;
