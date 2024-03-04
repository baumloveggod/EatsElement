<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

// Add new unit
function insert_into_Eineheiten(){
    global $conn;
    $name = $_POST['name'] ?? ''; // Verwenden Sie den Null-Coalescing-Operator, um sicherzustellen, dass $name nicht NULL ist.
    $umrechnungsfaktor = $_POST['umrechnungsfaktor'] ?? null;
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
}
function einheitsForm(){
    return '<label for="name">Name:</label>
    <input type="text" id="name" name="name" ><br><br>
    
    <label for="umrechnungsfaktor">Umrechnungsfaktor:</label>
    <input type="number" id="umrechnungsfaktor" name="umrechnungsfaktor" step="0.01" >
    bei "spezieler Bassis ist die referenc immer Gramm<br><br>
    
    <label for="basisEinheit">Basis Einheit:</label>
    <select id="basisEinheit" name="basisEinheit"  onchange="checkBasisEinheit(this.value)">
        <option value="">Bitte wählen</option>     
        <option value="Liter">Liter</option>
        <option value="Gramm">Gramm</option>
        <option value="speziell">speziell</option>
    </select><br><br>';
}
?>