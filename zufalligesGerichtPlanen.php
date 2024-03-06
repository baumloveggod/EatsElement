    <?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

$userId = $_SESSION['userId'];

$datum = $_POST['datum'] ?? date("Y-m-d");

// Wähle ein zufälliges Rezept
$rezeptSql = "SELECT id FROM rezepte ORDER BY RAND() LIMIT 1";
$rezeptResult = $conn->query($rezeptSql);
if ($rezeptRow = $rezeptResult->fetch_assoc()) {
    $zufallsRezeptId = $rezeptRow['id'];

    // Füge das ausgewählte Rezept zum ausgewählten Datum in den Essensplan ein
    $insertSql = "INSERT INTO essenplan (user_id, datum, rezept_id, anzahl_personen) VALUES (?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $anzahlPersonen = 1; // Setze eine Standardanzahl von Personen
    if (!$insertStmt->bind_param("isii", $userId, $datum, $zufallsRezeptId, $anzahlPersonen)) {
        echo "Bind Param Error";
    } else {
        if (!$insertStmt->execute()) {
            echo "Execute Error";
        } else {
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
                $einkaufslisteStmt->bind_param("iiss", $userId, $zutat['zutat_id'], $zutat['menge'], $datum);
                $einkaufslisteStmt->execute();
            }
            header("Location: rezept_detail.php?datum=" . urlencode($datum));
            exit; // Ensure script execution ends after redirection
        }
    }
    $insertStmt->close();
} else {
    echo "Keine Rezepte zur Planung verfügbar.";
}
$conn->close();
