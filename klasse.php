<?php
include "db.php";

$msg = "";
$redigerKlasse = null;

function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? '';
    $kode = trim($_POST['kode'] ?? '');
    $navn = trim($_POST['navn'] ?? '');
    $studium = trim($_POST['studium'] ?? '');

    if ($kode === '' || $navn === '') {
        $msg = 'Fyll inn kode og navn.';
    } else {
        $kodeDb = $conn->real_escape_string($kode);
        $navnDb = $conn->real_escape_string($navn);
        $studiumDb = $conn->real_escape_string($studium);

        if ($mode === 'lagre') {
            $sql = "INSERT INTO klasse (klassekode, klassenavn, studiumkode) VALUES ('$kodeDb', '$navnDb', '$studiumDb')";
            if ($conn->query($sql)) {
                $msg = 'Klasse lagret!';
            } else {
                $msg = 'Feil: ' . $conn->error;
            }
        } elseif ($mode === 'oppdater') {
            $sql = "UPDATE klasse SET klassenavn='$navnDb', studiumkode='$studiumDb' WHERE klassekode='$kodeDb'";
            if ($conn->query($sql)) {
                $msg = 'Klasse oppdatert!';
                unset($_GET['rediger']);
            } else {
                $msg = 'Feil ved oppdatering: ' . $conn->error;
            }
        }
    }
}

if (isset($_GET['slett'])) {
    $kode = $conn->real_escape_string($_GET['slett']);
    if ($conn->query("DELETE FROM klasse WHERE klassekode='$kode'")) {
        $msg = 'Klasse slettet!';
    } else {
        $msg = 'Feil ved sletting: ' . $conn->error;
    }
}

if (isset($_GET['rediger'])) {
    $kode = $conn->real_escape_string($_GET['rediger']);
    $result = $conn->query("SELECT klassekode, klassenavn, studiumkode FROM klasse WHERE klassekode='$kode'");
    if ($result && $result->num_rows === 1) {
        $redigerKlasse = $result->fetch_assoc();
    } else {
        $msg = 'Fant ikke klassen som skulle redigeres.';
    }
}

$klasser = [];
$result = $conn->query("SELECT klassekode, klassenavn, studiumkode FROM klasse ORDER BY klassekode");
while ($row = $result->fetch_assoc()) {
    $klasser[] = $row;
}

$formOverskrift = $redigerKlasse ? 'Rediger klasse' : 'Ny klasse';
$formMode = $redigerKlasse ? 'oppdater' : 'lagre';
$kodeVerdi = $redigerKlasse['klassekode'] ?? '';
$navnVerdi = $redigerKlasse['klassenavn'] ?? '';
$studiumVerdi = $redigerKlasse['studiumkode'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Klasser</title>
</head>
<body>
<h1>Administrer klasser</h1>
<p><a href="index.php">← Til hovedsiden</a></p>

<?php if ($msg !== ''): ?>
    <p><strong><?php echo h($msg); ?></strong></p>
<?php endif; ?>

<h2><?php echo h($formOverskrift); ?></h2>
<form method="post">
    <input type="hidden" name="mode" value="<?php echo h($formMode); ?>">
    Klassekode:<br>
    <input type="text" name="kode" value="<?php echo h($kodeVerdi); ?>" <?php echo $redigerKlasse ? 'readonly' : ''; ?> required><br>
    Klassenavn:<br>
    <input type="text" name="navn" value="<?php echo h($navnVerdi); ?>" required><br>
    Studiumkode:<br>
    <input type="text" name="studium" value="<?php echo h($studiumVerdi); ?>" required><br><br>
    <input type="submit" value="<?php echo $redigerKlasse ? 'Oppdater' : 'Lagre'; ?>">
    <?php if ($redigerKlasse): ?>
        <a href="klasse.php">Avbryt redigering</a>
    <?php endif; ?>
</form>

<h2>Alle klasser</h2>
<table border="1" cellpadding="4">
    <tr><th>Kode</th><th>Navn</th><th>Studium</th><th>Handlinger</th></tr>
    <?php foreach ($klasser as $row): ?>
        <tr>
            <td><?php echo h($row['klassekode']); ?></td>
            <td><?php echo h($row['klassenavn']); ?></td>
            <td><?php echo h($row['studiumkode']); ?></td>
            <td>
                <a href="?rediger=<?php echo urlencode($row['klassekode']); ?>">Rediger</a> |
                <a href="?slett=<?php echo urlencode($row['klassekode']); ?>" onclick="return confirm('Slette denne klassen?');">Slett</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
