<?php
include "db.php";

$msg = "";
$redigerStudent = null;

function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$mode = $_POST['mode'] ?? '';
$brukerInput = trim($_POST['bruker'] ?? '');
$fornavnInput = trim($_POST['fornavn'] ?? '');
$etternavnInput = trim($_POST['etternavn'] ?? '');
$klasseInput = trim($_POST['klasse'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($brukerInput === '' || $fornavnInput === '') {
        $msg = 'Fyll inn brukernavn og fornavn.';
    } else {
        $brukerDb = $conn->real_escape_string($brukerInput);
        $fornavnDb = $conn->real_escape_string($fornavnInput);
        $etternavnDb = $conn->real_escape_string($etternavnInput);
        $klasseDb = $conn->real_escape_string($klasseInput);

        if ($mode === 'lagre') {
            $sql = "INSERT INTO student (brukernavn, fornavn, etternavn, klassekode) VALUES ('$brukerDb', '$fornavnDb', '$etternavnDb', '$klasseDb')";
            if ($conn->query($sql)) {
                $msg = 'Student lagret!';
                $brukerInput = $fornavnInput = $etternavnInput = '';
                $klasseInput = '';
            } else {
                $msg = 'Feil: ' . $conn->error;
            }
        } elseif ($mode === 'oppdater') {
            $sql = "UPDATE student SET fornavn='$fornavnDb', etternavn='$etternavnDb', klassekode='$klasseDb' WHERE brukernavn='$brukerDb'";
            if ($conn->query($sql)) {
                $msg = 'Student oppdatert!';
                unset($_GET['rediger']);
            } else {
                $msg = 'Feil ved oppdatering: ' . $conn->error;
            }
        }
    }
}

if (isset($_GET['slett'])) {
    $bruker = $conn->real_escape_string($_GET['slett']);
    if ($conn->query("DELETE FROM student WHERE brukernavn='$bruker'")) {
        $msg = 'Student slettet!';
    } else {
        $msg = 'Feil ved sletting: ' . $conn->error;
    }
}

if (isset($_GET['rediger'])) {
    $bruker = $conn->real_escape_string($_GET['rediger']);
    $result = $conn->query("SELECT brukernavn, fornavn, etternavn, klassekode FROM student WHERE brukernavn='$bruker'");
    if ($result && $result->num_rows === 1) {
        $redigerStudent = $result->fetch_assoc();
        $brukerInput = $redigerStudent['brukernavn'];
        $fornavnInput = $redigerStudent['fornavn'];
        $etternavnInput = $redigerStudent['etternavn'];
        $klasseInput = $redigerStudent['klassekode'];
    } else {
        $msg = 'Fant ikke studenten som skulle redigeres.';
    }
}

$klasser = [];
$resultKlasse = $conn->query("SELECT klassekode, klassenavn FROM klasse ORDER BY klassekode");
while ($row = $resultKlasse->fetch_assoc()) {
    $klasser[] = $row;
}

// Sett standardvalg for ny student dersom ingen klasse er valgt
if ($klasseInput === '' && count($klasser) > 0) {
    $klasseInput = $klasser[0]['klassekode'];
}

$studenter = [];
$resultStudent = $conn->query(
    "SELECT s.brukernavn, s.fornavn, s.etternavn, s.klassekode, k.klassenavn " .
    "FROM student s LEFT JOIN klasse k ON s.klassekode = k.klassekode ORDER BY s.brukernavn"
);
while ($rad = $resultStudent->fetch_assoc()) {
    $studenter[] = $rad;
}

$formOverskrift = $redigerStudent ? 'Rediger student' : 'Ny student';
$formMode = $redigerStudent ? 'oppdater' : 'lagre';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Studenter</title>
</head>
<body>
<h1>Administrer studenter</h1>
<p><a href="index.php">← Til hovedsiden</a></p>

<?php if ($msg !== ''): ?>
    <p><strong><?php echo h($msg); ?></strong></p>
<?php endif; ?>

<h2><?php echo h($formOverskrift); ?></h2>
<form method="post">
    <input type="hidden" name="mode" value="<?php echo h($formMode); ?>">
    Brukernavn:<br>
    <input type="text" name="bruker" value="<?php echo h($brukerInput); ?>" <?php echo $redigerStudent ? 'readonly' : ''; ?> required><br>
    Fornavn:<br>
    <input type="text" name="fornavn" value="<?php echo h($fornavnInput); ?>" required><br>
    Etternavn:<br>
    <input type="text" name="etternavn" value="<?php echo h($etternavnInput); ?>"><br>
    Klasse:<br>
    <select name="klasse">
        <?php foreach ($klasser as $k): ?>
            <option value="<?php echo h($k['klassekode']); ?>" <?php echo $klasseInput === $k['klassekode'] ? 'selected' : ''; ?>>
                <?php echo h($k['klassekode'] . ' - ' . $k['klassenavn']); ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>
    <input type="submit" value="<?php echo $redigerStudent ? 'Oppdater' : 'Lagre'; ?>">
    <?php if ($redigerStudent): ?>
        <a href="student.php">Avbryt redigering</a>
    <?php endif; ?>
</form>

<h2>Alle studenter</h2>
<table border="1" cellpadding="4">
    <tr><th>Brukernavn</th><th>Fornavn</th><th>Etternavn</th><th>Klasse</th><th>Handlinger</th></tr>
    <?php foreach ($studenter as $rad): ?>
        <tr>
            <td><?php echo h($rad['brukernavn']); ?></td>
            <td><?php echo h($rad['fornavn']); ?></td>
            <td><?php echo h($rad['etternavn']); ?></td>
            <td><?php echo h(trim($rad['klassekode'] . ' ' . ($rad['klassenavn'] ?? ''))); ?></td>
            <td>
                <a href="?rediger=<?php echo urlencode($rad['brukernavn']); ?>">Rediger</a> |
                <a href="?slett=<?php echo urlencode($rad['brukernavn']); ?>" onclick="return confirm('Slette denne studenten?');">Slett</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
