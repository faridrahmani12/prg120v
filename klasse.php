<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

include "db.php";

function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$msg = '';
$alertClass = 'alert';
if (!empty($_SESSION['flash'])) {
    $msg = $_SESSION['flash']['msg'] ?? '';
    $alertClass = $_SESSION['flash']['class'] ?? 'alert';
    unset($_SESSION['flash']);
}

$redigerKlasse = null;

$kodeInput = trim($_POST['kode'] ?? '');
$navnInput = trim($_POST['navn'] ?? '');
$studiumInput = trim($_POST['studium'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'lagre';

    if ($kodeInput === '' || $navnInput === '' || $studiumInput === '') {
        $msg = 'Fyll inn alle feltene.';
        $alertClass = 'alert error';
    } else {
        try {
            if ($mode === 'oppdater') {
                $stmt = $conn->prepare("UPDATE klasse SET klassenavn = ?, studiumkode = ? WHERE klassekode = ?");
                $stmt->bind_param('sss', $navnInput, $studiumInput, $kodeInput);
                $stmt->execute();
                $stmt->close();
                $_SESSION['flash'] = ['msg' => 'Klasse oppdatert!', 'class' => 'alert'];
            } else {
                $stmt = $conn->prepare("INSERT INTO klasse (klassekode, klassenavn, studiumkode) VALUES (?, ?, ?)");
                $stmt->bind_param('sss', $kodeInput, $navnInput, $studiumInput);
                $stmt->execute();
                $stmt->close();
                $_SESSION['flash'] = ['msg' => 'Klasse lagret!', 'class' => 'alert'];
            }
            header('Location: klasse.php');
            exit;
        } catch (mysqli_sql_exception $e) {
            $_SESSION['flash'] = ['msg' => 'Feil: ' . $e->getMessage(), 'class' => 'alert error'];
            header('Location: klasse.php');
            exit;
        }
    }
}

if (isset($_GET['slett'])) {
    $kode = trim($_GET['slett']);
    if ($kode === '') {
        $_SESSION['flash'] = ['msg' => 'Ugyldig kode for sletting.', 'class' => 'alert error'];
    } else {
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
    }
    header('Location: klasse.php');
    exit;
}

if (isset($_GET['rediger'])) {
    $kode = trim($_GET['rediger']);
    if ($kode !== '') {
        $stmt = $conn->prepare("SELECT klassekode, klassenavn, studiumkode FROM klasse WHERE klassekode = ?");
        $stmt->bind_param('s', $kode);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $redigerKlasse = $result->fetch_assoc();
            $kodeInput = $redigerKlasse['klassekode'];
            $navnInput = $redigerKlasse['klassenavn'];
            $studiumInput = $redigerKlasse['studiumkode'];
        } else {
            $msg = 'Fant ikke klassen som skulle redigeres.';
            $alertClass = 'alert error';
        }
        $stmt->close();
    }
}

$klasser = [];
$result = $conn->query("SELECT klassekode, klassenavn, studiumkode FROM klasse ORDER BY klassekode");
while ($row = $result->fetch_assoc()) {
    $klasser[] = $row;
}
$result->close();

$formMode = $redigerKlasse ? 'oppdater' : 'lagre';
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="utf-8">
    <title>Administrer klasser</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="page">
    <a class="nav-link" href="index.php"><span>←</span>Til hovedsiden</a>

    <div class="card">
        <h1>Administrer klasser</h1>
        <p>Registrer, oppdater eller slett klassene i studiet.</p>

        <?php if ($msg !== ''): ?>
            <div class="<?php echo h($alertClass); ?>"><?php echo h($msg); ?></div>
        <?php endif; ?>

        <h2><?php echo h($redigerKlasse ? 'Rediger klasse' : 'Ny klasse'); ?></h2>
        <form method="post">
            <input type="hidden" name="mode" value="<?php echo h($formMode); ?>">

            <div class="field">
                <label for="kode">Klassekode</label>
                <input id="kode" type="text" name="kode" value="<?php echo h($kodeInput); ?>" <?php echo $redigerKlasse ? 'readonly' : ''; ?> required>
            </div>

            <div class="field">
                <label for="navn">Klassenavn</label>
                <input id="navn" type="text" name="navn" value="<?php echo h($navnInput); ?>" required>
            </div>

            <div class="field">
                <label for="studium">Studiumkode</label>
                <input id="studium" type="text" name="studium" value="<?php echo h($studiumInput); ?>" required>
            </div>

            <input type="submit" value="<?php echo $redigerKlasse ? 'Oppdater klasse' : 'Lagre klasse'; ?>">
            <?php if ($redigerKlasse): ?>
                <a class="muted-link" href="klasse.php">Avbryt redigering</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h2>Alle klasser</h2>
        <?php if (count($klasser) === 0): ?>
            <p>Ingen klasser funnet ennå.</p>
        <?php else: ?>
            <table>
                <tr><th>Kode</th><th>Navn</th><th>Studium</th><th>Handlinger</th></tr>
                <?php foreach ($klasser as $row): ?>
                    <tr>
                        <td><?php echo h($row['klassekode']); ?></td>
                        <td><?php echo h($row['klassenavn']); ?></td>
                        <td><?php echo h($row['studiumkode']); ?></td>
                        <td class="actions">
                            <a href="?rediger=<?php echo urlencode($row['klassekode']); ?>">Rediger</a>
                            <a href="?slett=<?php echo urlencode($row['klassekode']); ?>" onclick="return confirm('Slette denne klassen?');">Slett</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
