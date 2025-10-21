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

$redigerStudent = null;

$mode = $_POST['mode'] ?? 'lagre';
$brukerInput = trim($_POST['bruker'] ?? '');
$fornavnInput = trim($_POST['fornavn'] ?? '');
$etternavnInput = trim($_POST['etternavn'] ?? '');
$klasseInput = trim($_POST['klasse'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($brukerInput === '' || $fornavnInput === '' || $klasseInput === '') {
        $msg = 'Fyll inn brukernavn, fornavn og klasse.';
        $alertClass = 'alert error';
    } else {
        try {
            if ($mode === 'oppdater') {
                $stmt = $conn->prepare("UPDATE student SET fornavn = ?, etternavn = ?, klassekode = ? WHERE brukernavn = ?");
                $stmt->bind_param('ssss', $fornavnInput, $etternavnInput, $klasseInput, $brukerInput);
                $stmt->execute();
                $stmt->close();
                $_SESSION['flash'] = ['msg' => 'Student oppdatert!', 'class' => 'alert'];
            } else {
                $stmt = $conn->prepare("INSERT INTO student (brukernavn, fornavn, etternavn, klassekode) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('ssss', $brukerInput, $fornavnInput, $etternavnInput, $klasseInput);
                $stmt->execute();
                $stmt->close();
                $_SESSION['flash'] = ['msg' => 'Student lagret!', 'class' => 'alert'];
            }
            header('Location: student.php');
            exit;
        } catch (mysqli_sql_exception $e) {
            $_SESSION['flash'] = ['msg' => 'Feil: ' . $e->getMessage(), 'class' => 'alert error'];
            header('Location: student.php');
            exit;
        }
    }
}

if (isset($_GET['rediger'])) {
    $bruker = trim($_GET['rediger']);
    if ($bruker !== '') {
        $stmt = $conn->prepare("SELECT brukernavn, fornavn, etternavn, klassekode FROM student WHERE brukernavn = ?");
        $stmt->bind_param('s', $bruker);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $redigerStudent = $result->fetch_assoc();
            $brukerInput = $redigerStudent['brukernavn'];
            $fornavnInput = $redigerStudent['fornavn'];
            $etternavnInput = $redigerStudent['etternavn'];
            $klasseInput = $redigerStudent['klassekode'];
        } else {
            $msg = 'Fant ikke studenten som skulle redigeres.';
            $alertClass = 'alert error';
        }
        $stmt->close();
    }
}

$klasser = [];
$resultKlasse = $conn->query("SELECT klassekode, klassenavn FROM klasse ORDER BY klassekode");
while ($row = $resultKlasse->fetch_assoc()) {
    $klasser[] = $row;
}
$resultKlasse->close();

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
$resultStudent->close();

$formOverskrift = $redigerStudent ? 'Rediger student' : 'Ny student';
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="utf-8">
    <title>Administrer studenter</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="page">
    <a class="nav-link" href="index.php"><span>←</span>Til hovedsiden</a>

    <div class="card">
        <h1>Administrer studenter</h1>
        <p>Registrer nye studenter, rediger informasjon, eller slett registreringer.</p>

        <?php if ($msg !== ''): ?>
            <div class="<?php echo h($alertClass); ?>"><?php echo h($msg); ?></div>
        <?php endif; ?>

        <h2><?php echo h($formOverskrift); ?></h2>
        <form method="post">
            <input type="hidden" name="mode" value="<?php echo h($redigerStudent ? 'oppdater' : 'lagre'); ?>">

            <div class="field">
                <label for="bruker">Brukernavn</label>
                <input id="bruker" type="text" name="bruker" value="<?php echo h($brukerInput); ?>" <?php echo $redigerStudent ? 'readonly' : ''; ?> required>
            </div>

            <div class="field">
                <label for="fornavn">Fornavn</label>
                <input id="fornavn" type="text" name="fornavn" value="<?php echo h($fornavnInput); ?>" required>
            </div>

            <div class="field">
                <label for="etternavn">Etternavn</label>
                <input id="etternavn" type="text" name="etternavn" value="<?php echo h($etternavnInput); ?>">
            </div>

            <div class="field">
                <label for="klasse">Klasse</label>
                <select id="klasse" name="klasse">
                    <?php foreach ($klasser as $k): ?>
                        <option value="<?php echo h($k['klassekode']); ?>" <?php echo $klasseInput === $k['klassekode'] ? 'selected' : ''; ?>>
                            <?php echo h($k['klassekode'] . ' - ' . $k['klassenavn']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input type="submit" value="<?php echo $redigerStudent ? 'Oppdater student' : 'Lagre student'; ?>">
            <?php if ($redigerStudent): ?>
                <a class="muted-link" href="student.php">Avbryt redigering</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h2>Alle studenter</h2>
        <?php if (count($studenter) === 0): ?>
            <p>Ingen studenter registrert ennå.</p>
        <?php else: ?>
            <table>
                <tr><th>Brukernavn</th><th>Fornavn</th><th>Etternavn</th><th>Klasse</th><th>Handlinger</th></tr>
                <?php foreach ($studenter as $rad): ?>
                    <tr>
                        <td><?php echo h($rad['brukernavn']); ?></td>
                        <td><?php echo h($rad['fornavn']); ?></td>
                        <td><?php echo h($rad['etternavn']); ?></td>
                        <td><?php echo h(trim($rad['klassekode'] . ' ' . ($rad['klassenavn'] ?? ''))); ?></td>
                        <td class="actions">
                            <a href="?rediger=<?php echo urlencode($rad['brukernavn']); ?>">Rediger</a>
                            <a href="slett_student.php?bruker=<?php echo urlencode($rad['brukernavn']); ?>" onclick="return confirm('Slette denne studenten?');">Slett</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
