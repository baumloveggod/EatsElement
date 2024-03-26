<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    require_once __DIR__ . '/../Utils/db_connect.php';
    require_once __DIR__ . '/../Utils/SessionManager.php';
    checkUserAuthentication();

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $zutatenName = $_GET['zutatenName'];
    
    
    $einheiten = [];

    // Prüfen, ob die Zutat bekannt ist
    if ($zutatenName) {
        $sql = "SELECT z.einheit_id, e.name, e.basis_einheit_id FROM zutaten_namen zn 
                JOIN zutaten z ON zn.zutat_id = z.id 
                JOIN einheiten e ON z.einheit_id = e.id 
                WHERE zn.name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $zutatenName);
        $stmt->execute();
        $result = $stmt->get_result();
        $zutatInfo = $result->fetch_assoc();

        if ($zutatInfo) {
            // Spezielle Logik für bekannte Zutat
            if ($zutatInfo['basis_einheit_id'] != null) {
                // Einheiten für spezifische Basiseinheit laden (z.B. Gramm oder Liter)
                $sql = "SELECT id, name FROM einheiten WHERE id = ? OR basis_einheit_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $zutatInfo['einheit_id'], $zutatInfo['basis_einheit_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $einheiten[] = $row;
                }
            } else {
                // Nur die spezifische Einheit der Zutat anzeigen
                $einheiten[] = ['id' => $zutatInfo['einheit_id'], 'name' => $zutatInfo['name']];
            }
        } else {
            // Zutat ist unbekannt: Alle Einheiten laden
            $sql = "SELECT id, name FROM einheiten";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $einheiten[] = $row;
            }
        }
    } else {
        // Zutat ist unbekannt: Alle Einheiten laden
        $sql = "SELECT id, name FROM einheiten";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $einheiten[] = $row;
        }
    }

    echo json_encode($einheiten);

}