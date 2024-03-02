<?php
// Fehlerberichterstattung einschalten
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verbindung zur Datenbank herstellen
require_once '../../Utils/db_connect.php';

// Funktion, um Optionen für ein Dropdown-Menü zu generieren
function generateOptions($conn, $tableName, $idColumn, $nameColumn) {
    $options = '';
    $sql = "SELECT $idColumn, $nameColumn FROM $tableName ORDER BY $nameColumn ASC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $options .= "<option value='" . $row[$idColumn] . "'>" . htmlspecialchars($row[$nameColumn]) . "</option>";
        }
    }
    return $options;
}

// Überprüfen, ob das Formular gesendet wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verbindung zur Datenbank herstellen
    require_once '../../Utils/db_connect.php';

    // Daten aus dem Formular holen und bereinigen
    $name = $_POST['name'];
    $haltbarkeit = $_POST['haltbarkeit'];
    $volumen = $_POST['volumen'];
    $kategorie_id = $_POST['kategorie_id'];
    $phd_kategorie_id = $_POST['phd_kategorie_id'];
    $einheit_id = $_POST['einheit_id'];
    $umrechnungsfaktor = !empty($_POST['umrechnungsfaktor']) ? $_POST['umrechnungsfaktor'] : NULL;

    // Prepared Statement vorbereiten
    $stmt = $conn->prepare("INSERT INTO zutaten (uebliche_haltbarkeit, volumen, kategorie_id, phd_kategorie_id, einheit_id, , spezifischer_umrechnungsfaktor) VALUES (?, ?, ?, ?, ?, ?)");

    // Parameter binden
    $stmt->bind_param("iididd", $haltbarkeit, $volumen, $kategorie_id, $phd_kategorie_id, $einheit_id, $umrechnungsfaktor);

    // Versuchen, die Prepared Statement auszuführen
    if ($stmt->execute()) {
        // Assuming $stmt->execute() was successful and $name is the name of the ingredient
        $zutatId = $conn->insert_id; // Retrieves the ID of the last inserted row
        $stmt = $conn->prepare("INSERT INTO zutaten_namen (name, zutat_id) VALUES (?, ?)");
        $stmt->bind_param("si", $name, $zutatId);
        if (!$stmt->execute()) {
            echo "<p>Fehler beim Hinzufügen des Namens der Zutat: " . $stmt->error . "</p>";
        }

        echo "<p>Zutat erfolgreich hinzugefügt!</p>";
    } else {
        echo "<p>Fehler beim Hinzufügen der Zutat: " . $stmt->error . "</p>";
    }

    // Prepared Statement schließen
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Zutat Hinzufügen</title>
</head>
<body>
    <h2>Zutat Hinzufügen</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required><br><br>
        
        <label for="haltbarkeit">Haltbarkeit (in Tagen):</label>
        <input type="number" id="haltbarkeit" name="haltbarkeit" required><br><br>
        
        <label for="volumen">Volumen:</label>
        <input type="text" id="volumen" name="volumen" required><br><br>
        
        <label for="kategorie_id">Kategorie:</label>
        <select id="kategorie_id" name="kategorie_id" required>
            <?php echo generateOptions($conn, 'kategorien', 'id', 'name'); ?>
        </select><br><br>
        
        <label for="phd_kategorie_id">Planetary Health Diet Category:</label>
        <select id="phd_kategorie_id" name="phd_kategorie_id" required>
            <?php echo generateOptions($conn, 'Planetary_Health_Diet_Categories', 'ID', 'Kategorie'); ?>
        </select><br><br>
        
        <label for="einheit_id">einheit:</label>
        <select id="einheit_id" name="einheit_id" required>
            <?php echo generateOptions($conn, 'einheiten', 'id', 'name'); ?>
        </select><br><br>
        
        <input type="submit" value="Zutat Hinzufügen">
    </form>
    <h2>Vorhandene Zutaten</h2>
    <?php
    // Vorhandene Zutaten auflisten
    $sql = "SELECT zutaten.id, zutaten_namen.name, zutaten.uebliche_haltbarkeit, zutaten.volumen FROM zutaten JOIN zutaten_namen ON zutaten.id = zutaten_namen.zutat_id ORDER BY zutaten_namen.name ASC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Name</th><th>Haltbarkeit (Tage)</th><th>Volumen</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>" . htmlspecialchars($row['name']) . "</td><td>" . htmlspecialchars($row['uebliche_haltbarkeit']) . "</td><td>" . htmlspecialchars($row['volumen']) . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "Keine Zutaten gefunden.";
    }
    ?>
</body>
</html>
