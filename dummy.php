<?php foreach ($rezept['zutaten'] as $zutat) {
    $stmtCheckZutat->bind_param("s", $zutat['name']);
    $stmtCheckZutat->execute();
    $result = $stmtCheckZutat->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $zutatId = $row['zutat_id'];
        
        // Extract quantity and unit from 'menge'
        preg_match('/^(\d+)\s*(.*)$/', $zutat['menge'], $matches);
        $quantity = (int)$matches[1];
        $unit = trim($matches[2]);
        echo $matches[1] . " + " . $matches[2]
        // Check if the combination of rezept_id and zutat_id already exists
        $stmtCheckExisting = $conn->prepare("SELECT COUNT(*) FROM rezept_zutaten WHERE rezept_id = ? AND zutat_id = ?");
        $stmtCheckExisting->bind_param("ii", $rezeptId, $zutatId);
        $stmtCheckExisting->execute();
        $stmtCheckExisting->bind_result($count);
        $stmtCheckExisting->fetch();
        
        if ($count == 0) {
            // If the entry does not exist, insert it
            // Note: Ensure you have the correct unit ID before this step
            $stmtZutaten->bind_param("iiis", $rezeptId, $zutatId, $quantity, $einheitId);
            $stmtZutaten->execute();
        } else {
            // Entry exists, you might want to skip or update the existing record
            // For example, to update the quantity you could prepare another SQL statement here
        }
    }
    $stmtCheckExisting->close();
}

// Angenommen, $unit enthält den Namen der Einheit
$einheitName = $unit; // Beispiel: "g" für Gramm

// SQL-Abfrage, um die einheit_id zu ermitteln
$stmtEinheit = $conn->prepare("SELECT id FROM einheiten WHERE name = ?");
$stmtEinheit->bind_param("s", $einheitName);
$stmtEinheit->execute();
$resultEinheit = $stmtEinheit->get_result();

if ($resultEinheit->num_rows > 0) {
    // Einheit existiert, benutze existierende einheit_id
    $rowEinheit = $resultEinheit->fetch_assoc();
    $einheitId = $rowEinheit['id'];
} else {
    // Optional: Einheit existiert nicht, füge sie ein und benutze neue einheit_id
    // Dies hängt von deiner Anforderung ab, ob du neue Einheiten automatisch hinzufügen möchtest
    $stmtEinheitInsert = $conn->prepare("INSERT INTO einheiten (name, umrechnungsfaktor_zu_basis) VALUES (?, ?)");
    $umrechnungsfaktorZuBasis = 1; // Standardwert oder berechne basierend auf Einheit
    $stmtEinheitInsert->bind_param("sd", $einheitName, $umrechnungsfaktorZuBasis);
    $stmtEinheitInsert->execute();
    $einheitId = $stmtEinheitInsert->insert_id;
}
?>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../Utils/db_connect.php';
require_once __DIR__ . '/../Utils/SessionManager.php';
checkUserAuthentication();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['zutatenName']) || empty($_POST['menge'])) {
        exit('Fehler: Zutatenname oder Menge fehlt.');
    }
    $zutatenName = $_POST['zutatenName'];
    $menge = $_POST['menge'];
    $userId = $_SESSION['userId'];

    // Überprüfen, ob die Zutat bereits existiert
    $stmt = $conn->prepare("SELECT id FROM zutaten_namen WHERE name = ?");
    $stmt->bind_param("s", $zutatenName);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        // Zutat existiert nicht, also füge sie ein
        // Hier fügen wir nur in 'zutaten_namen' und 'zutaten' ein, ohne 'beschreibung'
        $stmt = $conn->prepare("INSERT INTO zutaten (kategorie_id) VALUES (NULL)"); // Adjust according to your schema
        $stmt->execute();
        $zutatId = $conn->insert_id;
        
        // Füge den Namen in 'zutaten_namen' ein
        $stmt = $conn->prepare("INSERT INTO zutaten_namen (name, zutat_id) VALUES (?, ?)");
        $stmt->bind_param("si", $zutatenName, $zutatId);
        $stmt->execute();
    } else {
        // Zutat existiert, hole die zutat_id
        $zutat = $result->fetch_assoc();
        $zutatId = $zutat['id'];
    }

    // Füge die Zutat in die Einkaufsliste ein
    $stmt = $conn->prepare("INSERT INTO einkaufsliste (user_id, zutat_id, menge) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $userId, $zutatId, $menge);
    $stmt->execute();
    header("Location: /Views/pages/einkaufsliste.php");
    exit;
}
?>
