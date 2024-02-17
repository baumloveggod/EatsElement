<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../Utils/db_connect.php';
require_once '../Utils/SessionManager.php';
checkUserAuthentication();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rezept_id'], $_POST['datum'])) {
    $rezeptId = $_POST['rezept_id'];
    $datum = $_POST['datum'];
    $userId = $_SESSION['userId'];
    // Bereiten Sie die SQL-Anweisung vor, um das Rezept zum Essensplan hinzuzufügen
    $sql = "INSERT INTO essenplan (user_id, datum, rezept_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $userId, $datum, $rezeptId);

    if ($stmt->execute()) {
        // Hole die Zutaten des Rezepts
        $sqlZutaten = "SELECT zutat_id, menge FROM rezept_zutaten WHERE rezept_id = ?";
        $stmtZutaten = $conn->prepare($sqlZutaten);
        $stmtZutaten->bind_param("i", $rezeptId);
        $stmtZutaten->execute();
        $resultZutaten = $stmtZutaten->get_result();

        while ($zutat = $resultZutaten->fetch_assoc()) {
            // Überprüfe, ob die Zutat bereits im Vorratsschrank ist und ob sie für andere Mahlzeiten geplant ist
            $sqlVorrat = "SELECT id FROM vorratsschrank WHERE zutat_id = ? AND user_id = ?";
            $stmtVorrat = $conn->prepare($sqlVorrat);
            $stmtVorrat->bind_param("ii", $zutat['zutat_id'], $userId);
            $stmtVorrat->execute();
            $resultVorrat = $stmtVorrat->get_result();

            if ($resultVorrat->num_rows == 0) {
                // Zutat ist nicht im Vorratsschrank, also füge sie zur Einkaufsliste hinzu
                $sqlEinkaufsliste = "INSERT INTO einkaufsliste (user_id, zutat_id, menge) VALUES (?, ?, ?)";
                $stmtEinkaufsliste = $conn->prepare($sqlEinkaufsliste);
                $stmtEinkaufsliste->bind_param("iii", $userId, $zutat['zutat_id'], $zutat['menge']);
                $stmtEinkaufsliste->execute();
            }
        }
        // Erfolg: Weiterleitung zurück zum Rezept-Detail oder einer Erfolgsmeldung
        header("Location: /Views/pages/rezept_detail.php?datum=" . urlencode($datum));
        exit();
    } else {
        // Fehlerbehandlung
        echo "Fehler beim Hinzufügen des Rezepts zum Essensplan.";
    }
}
?>
