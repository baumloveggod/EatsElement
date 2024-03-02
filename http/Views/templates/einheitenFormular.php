<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

// Add new unit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name'], $_POST['umrechnungsfaktor'], $_POST['basisEinheit'])) {
    $name = $_POST['name'];
    $umrechnungsfaktor = $_POST['umrechnungsfaktor'];
    $basisEinheit = $_POST['basisEinheit'];
    $volumen = $_POST['volumen'];
    
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
    
    $insertSql = "INSERT INTO einheiten (name,volumen, umrechnungsfaktor_zu_basis, basis_einheit_id, hat_spezifischen_umrechnungsfaktor) VALUES (?, ?, ?, ?,?)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("sddii", $name, $volumen, $umrechnungsfaktor, $basisEinheitId, $hatSpezifischenUmrechnungsfaktor);

    if ($stmt->execute()) {
        echo "<p>Einheit erfolgreich hinzugefügt!</p>";
    } else {
        echo "<p>Fehler beim Hinzufügen der Einheit: " . $stmt->error . "</p>";
    }
}
?>
<form action="" method="post">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required><br><br>
    
    <label for="umrechnungsfaktor">Umrechnungsfaktor:</label>
    <input type="number" id="umrechnungsfaktor" name="umrechnungsfaktor" step="0.01" required>
    bei "spezieler Bassis ist die referenc immer Gramm<br><br>
    
    <label for="basisEinheit">Basis Einheit:</label>
    <select id="basisEinheit" name="basisEinheit" required>
        <option value="Liter">Liter</option>
        <option value="Gramm">Gramm</option>
        <option value="speziell">speziell</option>
    </select><br><br>
    
    
    <label for="volumen">volumen:</label>
    <input type="number" id="volumen" name="volumen" step="0.01" required>
    nur wichtig für bassis Liter, da PHD in Gramm mist. <br><br>
    <button type="submit">Hinzufügen</button>
</form>