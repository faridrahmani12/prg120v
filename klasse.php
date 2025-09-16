<?php
include "db.php";
$msg = "";

// Legg til ny klasse
if(isset($_POST['lagre'])){
    $kode = $conn->real_escape_string($_POST['kode']);
    $navn = $conn->real_escape_string($_POST['navn']);
    $studium = $conn->real_escape_string($_POST['studium']);

    if($kode == "" || $navn == ""){
        $msg = "Fyll inn kode og navn.";
    } else {
        $sql = "INSERT INTO klasse (klassekode, klasssenavn, studiumkode) 
                VALUES ('$kode', '$navn', '$studium')";
        if($conn->query($sql)) $msg = "Klasse lagret!";
        else $msg = "Feil: " . $conn->error;
    }
}

// Slett klasse
if(isset($_GET['slett'])){
    $kode = $conn->real_escape_string($_GET['slett']);
    if($conn->query("DELETE FROM klasse WHERE klassekode='$kode'"))
        $msg = "Klasse slettet!";
    else
        $msg = "Feil ved sletting: " . $conn->error;
}

// Hent alle klasser
$klasser = $conn->query("SELECT * FROM klasse ORDER BY klassekode");
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Klasser</title></head>
<body>
<h1>Administrer klasser</h1>
<p><a href="index.php">← Til hovedsiden</a></p>

<?php if($msg != "") echo "<p><strong>$msg</strong></p>"; ?>

<h2>Ny klasse</h2>
<form method="post">
    Klassekode:<br><input type="text" name="kode" required><br>
    Klassenavn:<br><input type="text" name="navn" required><br>
    Studiumkode:<br><input type="text" name="studium" required><br><br>
    <input type="submit" name="lagre" value="Lagre">
</form>

<h2>Alle klasser</h2>
<table border="1" cellpadding="4">
<tr><th>Kode</th><th>Navn</th><th>Studium</th><th>Slett</th></tr>
<?php while($row = $klasser->fetch_assoc()): ?>
<tr>
    <td><?php echo $row['klassekode']; ?></td>
    <td><?php echo $row['klasssenavn']; ?></td>
    <td><?php echo $row['studiumkode']; ?></td>
    <td><a href="?slett=<?php echo $row['klassekode']; ?>" onclick="return confirm('Slette?')">Slett</a></td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>
