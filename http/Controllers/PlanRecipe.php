<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../Utils/SessionManager.php';
require_once '../Utils/db_connect.php';
checkUserAuthentication();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rezept_id'], $_POST['datum'])) {
    $rezeptId = $_POST['rezept_id'];
    $datum = $_POST['datum'];
    $userId = $_SESSION['userId'];

    // Überprüfen, ob für diesen Tag bereits ein Essen geplant ist
    $checkSql = "SELECT id FROM essenplan WHERE user_id = ? AND datum = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("is", $userId, $datum);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        // Kein Essen geplant, füge das neue Rezept ein
        $insertSql = "INSERT INTO essenplan (user_id, datum, rezept_id, anzahl_personen) VALUES (?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        // Die Anzahl der Personen könnte dynamisch angepasst werden, hier verwenden wir einen Standardwert
        $anzahlPersonen = 4;
        $insertStmt->bind_param("isii", $userId, $datum, $rezeptId, $anzahlPersonen);

        if ($insertStmt->execute()) {
            //echo "Rezept erfolgreich für $datum geplant.";
        } else {
            echo "Fehler beim Planen des Rezepts.";
        }
    } else {
        echo "Für dieses Datum ist bereits ein Essen geplant.";
    }

    $conn->close();
    // Leite zurück zur Rezeptdetailseite oder zum Essenplan
    header("Location: /Views/pages/rezept_detail.php?datum=" . urlencode($datum));
    exit;
}
?>
