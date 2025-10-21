<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require 'db.php';

$kode = trim(
    $_GET['kode']
        ?? $_GET['slett']
        ?? $_POST['kode']
        ?? $_POST['slett']
        ?? ''
);

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
    if ((int)$e->getCode() === 1451) {
        $_SESSION['flash'] = [
            'msg' => 'Kan ikke slette klassen fordi det finnes studenter registrert i den. Slett studentene først.',
            'class' => 'alert error'
        ];
    } else {
        $_SESSION['flash'] = ['msg' => 'Feil ved sletting: ' . $e->getMessage(), 'class' => 'alert error'];
    }
}

header('Location: klasse.php');
exit;
