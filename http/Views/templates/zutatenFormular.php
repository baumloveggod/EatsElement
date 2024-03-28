<?php
// Fehlerberichterstattung einschalten
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verbindung zur Datenbank herstellen
require_once '../../Utils/db_connect.php';

require '../templates/einheiten_post.php';

// Funktion, um Optionen für ein Dropdown-Menü zu generieren, erweitert um den speziellen Umrechnungsfaktor-Status
function generateOptions($tableName, $idColumn, $nameColumn, $isEinheiten = false) {
    global $conn;
    $options = '';
    $sql = $isEinheiten ? "SELECT $idColumn, $nameColumn, basis_einheit_id, hat_spezifischen_umrechnungsfaktor FROM $tableName ORDER BY $nameColumn ASC" : "SELECT $idColumn, $nameColumn FROM $tableName ORDER BY $nameColumn ASC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            if ($isEinheiten) {
                $options .= "<option value='" . $row[$idColumn] . "' data-basis='" . $row['basis_einheit_id'] . "' data-spezifischer-umrechnungsfaktor='" . $row['hat_spezifischen_umrechnungsfaktor'] . "'>" . htmlspecialchars($row[$nameColumn]) . "</option>";
            } else {
                $options .= "<option value='" . $row[$idColumn] . "'>" . htmlspecialchars($row[$nameColumn]) . "</option>";
            }
        }
    }
    return $options;
}
?>
    <div class="zutatenFormularContainer">
    
        <label for="existiertUnterAnderemNamen">Existiert die Zutat unter einem anderen Namen?</label>
        <input type="checkbox" class="existiertUnterAnderemNamen" name="existiertUnterAnderemNamen" onchange="toggleForm(this)" checked>
        
        <div class="alternativerNameContainer" style="display:block;">
            <label for="alternativerName">Anderer Name:</label>
            <select class="alternativerName" name="alternativerName">
                <?php echo generateOptions('zutaten_namen', 'zutat_id', 'name'); // Passen Sie die Tabelle und Spaltennamen entsprechend an ?>
            </select><br><br>
            <input type="submit" name="aktion_name" value="Zutat Unter Anderem Namen Hinzufügen">
        </div>

        <div class="restDesFormulars" style="display:none;">

            <label for="haltbarkeit">Haltbarkeit (in Tagen):</label>
            <input type="number" class="haltbarkeit" name="haltbarkeit" ><br><br>
      
      
            <label for="kategorie_id">Kategorie:</label>
            <select class="kategorie_id" name="kategorie_id" >
                <option value="">Bitte wählen</option>    
                <?php echo generateOptions('kategorien', 'id', 'name'); ?>
            </select><br><br>
            
            <label for="phd_kategorie_id">Planetary Health Diet Category:</label>
            <select class="phd_kategorie_id" name="phd_kategorie_id" >
                <option value="">Bitte wählen</option> 
                <?php echo generateOptions('Planetary_Health_Diet_Categories', 'ID', 'Kategorie'); ?>
            </select><br><br>
            
            <label for="einheit_id">Einheit:</label>
            <select class="einheit_id" name="einheit_id">
                <option value="">Bitte wählen</option>
                <?php echo generateOptions('einheiten', 'id', 'name', true); ?>
                <option value="neuHinzufuegen">Neu hinzufügen...</option>
            </select><br><br>
            
            <div class="neueEinheitFormular" style="display:none;"> 
                <?php require '../templates/einheitenFormular.html';?>
            </div>
            <div class="umrechnungsfaktorFeld" style="display: none;">
                <label for="umrechnungsfaktor">Umrechnungsfaktor:</label>
                <input type="number" class="umrechnungsfaktor" name="umrechnungsfaktor" step="0.01">
                <div> bei "spezieller Basis" ist die Referenz immer Gramm</div><br><br>
            </div>

            <div class="volumen_block" style="display:none;">
                <label for="volumen">Volumen:</label>
                <input type="text" class="volumen" name="volumen">
                Wichtig für PHD, da die Berechnung mit Gramm arbeitet<br><br>
            </div>      
        </div>
    </div>
    <input type="submit" value="Zutat Hinzufügen">   
</form>
