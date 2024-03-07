<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/db_connect.php';

// Add new unit
function insert_into_Eineheiten(){
    global $conn;
    global $_POST;
    $name = $_POST['name'] ?? ''; // Verwenden Sie den Null-Coalescing-Operator, um sicherzustellen, dass $name nicht NULL ist.
    $umrechnungsfaktor = $_POST['einheit_umrechnungsfaktor'] ?? null;
    $basisEinheit = $_POST['basisEinheit'] ?? '';
    
    $basisEinheitId = null;
    $hatSpezifischenUmrechnungsfaktor = false;

    // Determine basisEinheitId and hatSpezifischenUmrechnungsfaktor based on basisEinheit selection
    if ($basisEinheit == 'Gramm') {
        $basisEinheitId = 1; // Ensure this ID exists in your database
    } elseif ($basisEinheit == 'Liter') {
        $basisEinheitId = 2; // Ensure this ID exists in your database
    } elseif ($basisEinheit == 'speziell') {
        $hatSpezifischenUmrechnungsfaktor = true;
        $basisEinheitId = NULL;
        $umrechnungsfaktor = NULL;
    }
    
    $insertSql = "INSERT INTO einheiten (name, umrechnungsfaktor_zu_basis, basis_einheit_id, hat_spezifischen_umrechnungsfaktor) VALUES (?, ?, ?,?)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("sdii", $name, $umrechnungsfaktor, $basisEinheitId, $hatSpezifischenUmrechnungsfaktor);

    if ($stmt->execute()) {
        echo "<p>Einheit erfolgreich hinzugefügt!</p>";
        // Gib die ID der neu eingefügten Einheit zurück
        return $conn->insert_id;
    } else {
        echo "<p>Fehler beim Hinzufügen der Einheit: " . $stmt->error . "</p>";
        return null; // Rückgabe von null im Fehlerfall
}
}?>