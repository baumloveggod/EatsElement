<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

$userId = $_SESSION['userId'];
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
// Überprüfen, ob das Formular abgesendet wurde
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['anzahlPersonen'])) {
    $anzahlPersonen = intval($_POST['anzahlPersonen']);
    $userId = $_SESSION['userId']; // Stellen Sie sicher, dass der Benutzer eingeloggt ist und Sie die Benutzer-ID haben

    // Aktualisieren der Anzahl der Personen in der Datenbank
    // Hinweis: Sie müssen eine neue Spalte in der Tabelle `users` oder eine neue Einstellungstabelle erstellen, um diese Einstellung zu speichern, wenn Sie dies noch nicht getan haben
    $sql = "UPDATE users SET anzahl_personen_einstellung = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt->bind_param("ii", $anzahlPersonen, $userId)) {
        if (!$stmt->execute()) {
            echo "Fehler beim Aktualisieren der Anzahl der Personen.";
        }
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
    <?php include '../templates/navigation.php'; ?>

    <main>
        <h2>Einstellungen</h2>
        <h3>Anzahl der Personen für Rezepte</h3>
            <form action="settings.php" method="post">
                <label for="anzahlPersonen">Anzahl Personen:</label>
                <input type="number" id="anzahlPersonen" name="anzahlPersonen" min="1" value="2" required> <!-- Angenommener Standardwert -->
                <button type="submit">Speichern</button>
            </form>
        <h2>Einstlungen, die noch nichts tun :)</h2>
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

