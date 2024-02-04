<?php
require_once '../../Utils/SessionManager.php';
checkAccess();
require_once '../../Utils/db_connect.php';

$userId = $_SESSION['id'];
$aktuelleEinkaufsoption = '';

// Abfrage der aktuellen Einkaufsoption
$sql = "SELECT einkaufsoption FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $aktuelleEinkaufsoption = $row['einkaufsoption'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['einkaufsOption'])) {
    $auswahl = $_POST['einkaufsOption'];

    // SQL-Anweisung zum Aktualisieren der Einkaufsoption des Benutzers
    $sql = "UPDATE users SET einkaufsoption = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $auswahl, $userId);
    if ($stmt->execute()) {
        echo "<p>Ihre Auswahl wurde erfolgreich gespeichert.</p>";
    } else {
        echo "<p>Fehler beim Speichern Ihrer Auswahl.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <?php include '../templates/header.php'; ?>
    <title>Einstellungen</title>
</head>
<body>
    <?php include '../templates/header.php'; ?>
    <?php include '../templates/navigation.php'; ?>

    <main>
        <h2>Einstellungen</h2>
        <form action="settings.php" method="post">
            <label>Wählen Sie Ihre bevorzugte Einkaufsmethode:</label>
            <div>
                <input type="radio" id="perfekteMenge" name="einkaufsOption" value="perfekteMenge" <?php echo ($aktuelleEinkaufsoption == 'perfekteMenge') ? 'checked' : ''; ?>>
                <label for="perfekteMenge">Perfekte Menge verwenden</label>
            </div>
            <div>
                <input type="radio" id="ueblicheVerpackungsgroesse" name="einkaufsOption" value="ueblicheVerpackungsgroesse" <?php echo ($aktuelleEinkaufsoption == 'ueblicheVerpackungsgroesse') ? 'checked' : ''; ?>>
                <label for="ueblicheVerpackungsgroesse">Übliche Verpackungsgröße verwenden</label>
            </div>
            <div>
                <input type="radio" id="unverpackt" name="einkaufsOption" value="unverpackt" <?php echo ($aktuelleEinkaufsoption == 'unverpackt') ? 'checked' : ''; ?>>
                <label for="unverpackt">Unverpackt - Größe von Gefäßen berücksichtigen</label>
            </div>
            <button type="submit">Auswahl speichern</button>
        </form>
    </main>

    <?php include '../templates/footer.php'; ?>
</body>
</html>

