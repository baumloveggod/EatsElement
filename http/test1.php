<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Utils/SessionManager.php';
checkAccess();
require_once 'Utils/db_connect.php';

$userId = $_SESSION['id'];
$today = date("Y-m-d");
$fourteenDaysLater = date("Y-m-d", strtotime("+14 days"));

// Ermittle Tage ohne Rezept
$sql = "SELECT datum FROM essenplan WHERE user_id = ? AND datum BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $userId, $today, $fourteenDaysLater);
$stmt->execute();
$result = $stmt->get_result();

$besetzteTage = [];
while ($row = $result->fetch_assoc()) {
    $besetzteTage[] = $row['datum'];
}

$tageOhneRezept = [];
for ($i = 0; $i < 14; $i++) {
    $datum = date("Y-m-d", strtotime("$today +$i days"));
    if (!in_array($datum, $besetzteTage)) {
        $tageOhneRezept[] = $datum;
    }
}

// Wähle ein zufälliges Tag ohne Rezept aus
if (!empty($tageOhneRezept)) {
    $zufallstag = $tageOhneRezept[array_rand($tageOhneRezept)];

    // Wähle ein zufälliges Rezept aus
    $rezeptSql = "SELECT id FROM rezepte ORDER BY RAND() LIMIT 1";
    $rezeptResult = $conn->query($rezeptSql);
    if ($rezeptRow = $rezeptResult->fetch_assoc()) {
        $zufallsRezeptId = $rezeptRow['id'];

        // Füge das ausgewählte Rezept zum ausgewählten Datum in den Essensplan ein
        $insertSql = "INSERT INTO essenplan (user_id, datum, rezept_id, anzahl_personen) VALUES (?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $anzahlPersonen = 1; // Setze eine Standardanzahl von Personen
        $insertStmt->bind_param("isii", $userId, $zufallstag, $zufallsRezeptId, $anzahlPersonen);
        
        if ($insertStmt->execute()) {
            echo "Rezept erfolgreich zum Datum $zufallstag hinzugefügt.";
        } else {
            echo "Fehler beim Hinzufügen des Rezepts: " . $conn->error;
        }
        // Hole die Zutaten für das ausgewählte Rezept
        $zutatenSql = "SELECT zutat_id, menge FROM rezept_zutaten WHERE rezept_id = ?";
        $zutatenStmt = $conn->prepare($zutatenSql);
        $zutatenStmt->bind_param("i", $zufallsRezeptId);
        $zutatenStmt->execute();
        $zutatenResult = $zutatenStmt->get_result();

        while ($zutat = $zutatenResult->fetch_assoc()) {
            // Füge jede Zutat zur Einkaufsliste hinzu
            $einkaufslisteSql = "INSERT INTO einkaufsliste (user_id, zutat_id, menge, verbrauchsdatum) VALUES (?, ?, ?, ?)";
            $einkaufslisteStmt = $conn->prepare($einkaufslisteSql);
            $einkaufslisteStmt->bind_param("iiss", $userId, $zutat['zutat_id'], $zutat['menge'], $zufallstag);
            $einkaufslisteStmt->execute();
        }
        echo "Zutaten wurden der Einkaufsliste für das Datum $zufallstag hinzugefügt.";
    } else {
        echo "Keine Rezepte gefunden.";
    }
} else {
    echo "Keine freien Tage in den nächsten 14 Tagen.";
}

$conn->close();
?>
