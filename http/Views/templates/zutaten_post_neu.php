<?php
// Fehlerberichterstattung einschalten
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/db_connect.php';

function update_existent_Zutaten() {
    global $conn;
    
    // Daten vom Formular empfangen
    $zutaten_id = $_POST['zutaten_id'] ?? null;
    $existiertUnterAnderemNamen = isset($_POST['existiertUnterAnderemNamen']) ? true : false;
    $alternativerName = $_POST['alternativerName'] ?? '';

    // Validierung
    if ($zutaten_id === null) {
        echo "Fehler: Keine Zutaten-ID übergeben.";
        return;
    }
    if ($existiertUnterAnderemNamen && empty($alternativerName)) {
        echo "Fehler: Kein alternativer Name angegeben.";
        return;
    }

    // Wenn die Zutat unter einem anderen Namen existiert, ändere nur den zutaten_namen Eintrag
    if ($existiertUnterAnderemNamen) {
        // Zuerst entferne den zutaten Eintrag
        $deleteSql = "DELETE FROM zutaten WHERE id = ?";
        $stmt = $conn->prepare($deleteSql);
        if (!$stmt) {
            echo "Fehler: Vorbereitung des Statements fehlgeschlagen: " . $conn->error;
            return;
        }
        $stmt->bind_param("i", $zutaten_id);
        $deleteResult = $stmt->execute();
        if (!$deleteResult) {
            echo "Fehler beim Entfernen der Zutat: " . $conn->error;
            return;
        }

        // Füge dann den alternativen Namen als neuen Eintrag in zutaten_namen hinzu
        $insertSql = "INSERT INTO zutaten_namen (name, zutat_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insertSql);
        if (!$stmt) {
            echo "Fehler: Vorbereitung des Insert-Statements fehlgeschlagen: " . $conn->error;
            return;
        }
        $stmt->bind_param("si", $alternativerName, $zutaten_id);
        $insertResult = $stmt->execute();
        if ($insertResult) {
            echo "Alternativer Zutatenname erfolgreich hinzugefügt.";
        } else {
            echo "Fehler beim Hinzufügen des alternativen Namens: " . $conn->error;
        }
    } else {
        // Wenn nicht unter einem anderen Namen, führe die normale Aktualisierung durch
        // (Implementiere hier die Logik für die Aktualisierung anderer Felder, falls erforderlich)
        echo "Keine Änderungen vorgenommen, da die Zutat nicht unter einem anderen Namen existiert.";
    }
}
?>
