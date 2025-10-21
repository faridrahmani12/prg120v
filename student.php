<?php
include "db.php";
$msg = "";

// Legg til student
if(isset($_POST['lagre'])){
    $bruker = $conn->real_escape_string($_POST['bruker']);
    $fornavn = $conn->real_escape_string($_POST['fornavn']);
    $etternavn = $conn->real_escape_string($_POST['etternavn']);
    $klasse = $conn->real_escape_string($_POST['klasse']);

    if($bruker == "" || $fornavn == ""){
        $msg = "Fyll inn brukernavn og fornavn.";
    } else {
        $sql = "INSERT INTO student (brukernavn, fornavn, etternavn, klassekode)
                VALUES ('$bruker', '$fornavn', '$etternavn', '$klasse')";
        if($conn->query($sql)) $msg = "Student lagret!";
        else $msg = "Feil: " . $conn->error;
    }
}

// Slett student
if(isset($_GET['slett'])){
    $bruker = $conn->real_escape_string($_GET['slett']);
    if($conn->query("DELETE FROM student WHERE brukernavn='$bruker'"))
        $msg = "Student slettet!";
    else
        $msg = "Feil ved sletting: " . $conn->error;
}

// Hent klasser til listeboks
$klasser = $conn->query("SELECT klassekode, klassenavn FROM klasse ORDER BY klassekode");

// Hent alle studenter med klassenavn
$studenter = $conn->query("
    SELECT s.brukernavn, s.fornavn, s.etternavn, s.klassekode, k.klassenavn
    FROM student s
    LEFT JOIN klasse k ON s.klassekode = k.klassekode
    ORDER BY s.brukernavn
");
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Studenter</title></head>
<body>
<h1>Administrer studenter</h1>
<p><a href="index.php">← Til hovedsiden</a></p>

<?php if($msg != "") echo "<p><strong>$msg</strong></p>"; ?>

<h2>Ny student</h2>
<form method="post">
Brukernavn:<br><input type="text" name="bruker" required><br>
Fornavn:<br><input type="text" name="fornavn" required><br>
Etternavn:<br><input type="text" name="etternavn"><br>
Klasse:<br>
<select name="klasse">
<?php while($k = $klasser->fetch_assoc()): ?>
    <option value="<?php echo $k['klassekode']; ?>">
        <?php echo $k['klassekode'] . " - " . $k['klassenavn']; ?>
    </option>
<?php endwhile; ?>
</select><br><br>
<input type="submit" name="lagre" value="Lagre">
</form>

<h2>Alle studenter</h2>
<table border="1" cellpadding="4">
<tr><th>Brukernavn</th><th>Fornavn</th><th>Etternavn</th><th>Klasse</th><th>Slett</th></tr>
<?php while($r = $studenter->fetch_assoc()): ?>
<tr>
<td><?php echo $r['brukernavn']; ?></td>
<td><?php echo $r['fornavn']; ?></td>
<td><?php echo $r['etternavn']; ?></td>
<td><?php echo $r['klassekode'] . " " . $r['klassenavn']; ?></td>
<td><a href="?slett=<?php echo $r['brukernavn']; ?>" onclick="return confirm('Slette?')">Slett</a></td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>
