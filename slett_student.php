<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require 'db.php';

$bruker = trim($_GET['bruker'] ?? '');

if ($bruker === '') {
    $_SESSION['flash'] = ['msg' => 'Ugyldig brukernavn for sletting.', 'class' => 'alert error'];
    header('Location: student.php');
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM student WHERE brukernavn = ?");
    $stmt->bind_param('s', $bruker);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['flash'] = ['msg' => 'Student slettet!', 'class' => 'alert'];
    } else {
        $_SESSION['flash'] = ['msg' => 'Fant ingen student med det brukernavnet.', 'class' => 'alert error'];
    }

    $stmt->close();
} catch (mysqli_sql_exception $e) {
    $_SESSION['flash'] = ['msg' => 'Feil ved sletting: ' . $e->getMessage(), 'class' => 'alert error'];
}

header('Location: student.php');
exit;
