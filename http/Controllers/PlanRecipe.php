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
            $zutatId = $zutat['zutat_id'];
            $benotigteMenge = $zutat['menge'];

            // Überprüfe den Vorratsschrank
            $sqlVorrat = "SELECT id, menge, verbrauchsdatum FROM vorratsschrank WHERE zutat_id = ? AND user_id = ? ORDER BY verbrauchsdatum IS NULL, verbrauchsdatum ASC";
            $stmtVorrat = $conn->prepare($sqlVorrat);
            $stmtVorrat->bind_param("ii", $zutatId, $userId);
            $stmtVorrat->execute();
            $resultVorrat = $stmtVorrat->get_result();
            $verarbeitet = false;

            while (!$verarbeitet && $vorrat = $resultVorrat->fetch_assoc()) {
                $vorratMenge = $vorrat['menge'];
                if ($vorratMenge >= $benotigteMenge) {
                    // Menge im Vorrat ausreichend, aktualisiere oder setze Verbrauchsdatum
                    $neueMenge = $vorratMenge - $benotigteMenge;
                    $updateVorratSql = "UPDATE vorratsschrank SET verbrauchsdatum = COALESCE(verbrauchsdatum, ?) WHERE id = ?";
                    $updateVorratStmt = $conn->prepare($updateVorratSql);
                    $updateVorratStmt->bind_param("si", $datum, $vorrat['id']);
                    $updateVorratStmt->execute();
                    $verarbeitet = true;
                } else {
                    // Nicht genug Menge, setze Verbrauchsdatum und verringere die benötigte Menge
                    $benotigteMenge -= $vorratMenge;
                    $updateVorratSql = "UPDATE vorratsschrank SET verbrauchsdatum = COALESCE(verbrauchsdatum, ?) WHERE id = ?";
                    $updateVorratStmt = $conn->prepare($updateVorratSql);
                    $updateVorratStmt->bind_param("si", $datum, $vorrat['id']);
                    $updateVorratStmt->execute();
                }
            }

            // Falls nach Vorratsprüfung noch Menge benötigt wird, prüfe und aktualisiere die Einkaufsliste
            if ($benotigteMenge > 0 && !$verarbeitet) {
                $sqlEinkaufsliste = "SELECT id, menge FROM einkaufsliste WHERE zutat_id = ? AND user_id = ? AND verbrauchsdatum IS NULL";
                $stmtEinkaufsliste = $conn->prepare($sqlEinkaufsliste);
                $stmtEinkaufsliste->bind_param("ii", $zutatId, $userId);
                $stmtEinkaufsliste->execute();
                $resultEinkaufsliste = $stmtEinkaufsliste->get_result();

                if ($eintrag = $resultEinkaufsliste->fetch_assoc()) {
                    // Eintrag vorhanden, aktualisiere die Menge
                    $aktualisierteMenge = $eintrag['menge'] + $benotigteMenge;
                    $updateEinkaufslisteSql = "UPDATE einkaufsliste SET menge = ?, verbrauchsdatum = ? WHERE id = ?";
                    $updateEinkaufslisteStmt = $conn->prepare($updateEinkaufslisteSql);
                    $updateEinkaufslisteStmt->bind_param("isi", $aktualisierteMenge, $datum, $eintrag['id']);
                    $updateEinkaufslisteStmt->execute();
                } else {
                    // Kein Eintrag vorhanden, füge einen neuen Eintrag hinzu
                    $insertEinkaufslisteSql = "INSERT INTO einkaufsliste (user_id, zutat_id, menge, verbrauchsdatum) VALUES (?, ?, ?, ?)";
                    $insertEinkaufslisteStmt = $conn->prepare($insertEinkaufslisteSql);
                    $insertEinkaufslisteStmt->bind_param("iiis", $userId, $zutatId, $benotigteMenge, $datum);
                    $insertEinkaufslisteStmt->execute();
                }
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

