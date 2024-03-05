    <?php
// Fehlerberichterstattung einschalten
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verbindung zur Datenbank herstellen
require_once '../../Utils/db_connect.php';

include '/einheitenFormular.php';

// Funktion, um Optionen für ein Dropdown-Menü zu generieren, erweitert um den speziellen Umrechnungsfaktor-Status
function generateOptions($conn, $tableName, $idColumn, $nameColumn, $isEinheiten = false) {
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



// Überprüfen, ob das Formular gesendet wurde
insert_into_Zutaten(){
    // Überprüfen, ob das Formular gesendet wurde und die Aktion "Zutat Unter Anderem Namen Hinzufügen" ist
    if (isset($_POST['aktion_name']) && $_POST['aktion_name'] === "Zutat Unter Anderem Namen Hinzufügen") {
        // Daten aus dem Formular holen
        $alternativerName = $_POST['alternativerName'];

        // Suche nach einer Zutat mit dem alternativen Namen
        $stmt = $conn->prepare("SELECT zutat_id FROM zutaten_namen WHERE name = ?");
        $stmt->bind_param("s", $alternativerName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Zutat existiert, also füge den neuen Namen hinzu
            $row = $result->fetch_assoc();
            $zutatId = $row['zutat_id'];

            // Neuen Namen zur zutaten_namen Tabelle hinzufügen
            $zutaten_name = $_POST['zutaten_name']; // Der "neue" Name der Zutat
            $stmt = $conn->prepare("INSERT INTO zutaten_namen (name, zutat_id) VALUES (?, ?)");
            $stmt->bind_param("si", $zutaten_name, $zutatId);
            if ($stmt->execute()) {
                echo "<p>Neuer Name erfolgreich hinzugefügt.</p>";
            } else {
                echo "<p>Fehler beim Hinzufügen des neuen Namens: " . $stmt->error . "</p>";
            }
        } else {
            // Zutat nicht gefunden
            echo "<p>Die Zutat unter dem Namen '$alternativerName' wurde nicht gefunden. Bitte überprüfen Sie den Namen und versuchen Sie es erneut.</p>";
        }
    
        $stmt->close();
    }else{
        //zutaten_name : alternativerName : haltbarkeit : kategorie_id : phd_kategorie_id : einheit_id : name : einheit_umrechnungsfaktor : basisEinheit : umrechnungsfaktor : volumen
        foreach ($_POST as $a => $value) {
            echo $a . " : ";
        }
        
        if ($_POST['einheit_id'] === 'neuHinzufuegen') {
                
            // Führe die Funktion zum Hinzufügen der neuen Einheit aus und erhalte die neue Einheits-ID
            $einheit_id = insert_into_Eineheiten();
        }
        else{
            $einheit_id = $_POST['einheit_id'];
        }
        $hatSpezifischenUmrechnungsfaktor = $_POST['basisEinheit'] === 'speziell';
        $haltbarkeit = $_POST['haltbarkeit'] ?? null;
        $volumen = $_POST['volumen'] ?? null;
        $kategorie_id = $_POST['kategorie_id'] ?? null;
        $phd_kategorie_id = $_POST['phd_kategorie_id'] ?? null;
        $spezifischer_umrechnungsfaktor = ($hatSpezifischenUmrechnungsfaktor) ? $_POST['einheit_id'] === 'neuHinzufuegen' ? $_POST['einheit_umrechnungsfaktor']:$_POST['umrechnungsfaktor'] : null;

        // Prepared Statement zum Hinzufügen der Zutat vorbereiten
        $stmt = $conn->prepare("INSERT INTO zutaten (uebliche_haltbarkeit, volumen, kategorie_id, phd_kategorie_id, einheit_id, spezifischer_umrechnungsfaktor) VALUES (?, ?, ?, ?, ?, ?)");
        echo $haltbarkeit . " : " .  $volumen . " : " . $kategorie_id . " : " . $phd_kategorie_id . " : " . $einheit_id . " : " . $spezifischer_umrechnungsfaktor;
        // Parameter binden
        $stmt->bind_param("idiiid", $haltbarkeit, $volumen, $kategorie_id, $phd_kategorie_id, $einheit_id, $spezifischer_umrechnungsfaktor);

            // Versuchen, die Prepared Statement auszuführen
            if ($stmt->execute()) {
                if (empty($_POST['zutaten_name'])) {
                    echo "<p>Name is required.</p>";
                    // Handle the error appropriately - perhaps by not proceeding with the DB insert
                }
                $zutaten_name = $_POST['zutaten_name'];

                // Assuming $stmt->execute() was successful and $name is the name of the ingredient
                $zutatId = $conn->insert_id; // Retrieves the ID of the last inserted row
                $stmt = $conn->prepare("INSERT INTO zutaten_namen (name, zutat_id) VALUES (?, ?)");
                $stmt->bind_param("si", $zutaten_name, $zutatId);
                if (!$stmt->execute()) {
                    echo "<p>Fehler beim Hinzufügen des Namens der Zutat: " . $stmt->error . "</p>";
                }

                echo "<p>Zutat erfolgreich hinzugefügt!</p>";
            } else {
                echo "<p>Fehler beim Hinzufügen der Zutat: " . $stmt->error . "</p>";
            }

            // Prepared Statement schließen
            $stmt->close();
        }
    }

ZutatenForm(){
return "
 <form action='<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>' method='post'>
     <label for='zutaten_name'>Name:</label>
     <input type='text' id='zutaten_name' name='zutaten_name' ><br><br>
     
     <script>
        function toggleForm(checkbox) {
            var isChecked = checkbox.checked;
            var alternativerNameContainer = document.getElementById('alternativerNameContainer');
            var restDesFormulars = document.getElementById('restDesFormulars');
            
            // Sichtbarkeit umschalten
            alternativerNameContainer.style.display = isChecked ? 'block' : 'none';
            restDesFormulars.style.display = isChecked ? 'none' : 'block';

            // Setze oder entferne das 'required' Attribut basierend auf dem Zustand des Kontrollkästchens
            var inputsAlternative = alternativerNameContainer.getElementsByTagName('input');
            for (var i = 0; i < inputsAlternative.length; i++) {
                inputsAlternative[i].required = isChecked; // Diese Felder sind nur erforderlich, wenn der Container sichtbar ist
            }

            var inputsRest = restDesFormulars.getElementsByTagName('input');
            for (var i = 0; i < inputsRest.length; i++) {
                 // Überprüfe, ob das Eingabefeld sichtbar ist, bevor du es als required markierst
                if (inputsRest[i].type !== 'submit' && inputsRest[i].id !== 'volumen') { // 'volumen' wird separat behandelt
                    inputsRest[i].required = !isChecked; // Diese Felder sind nur erforderlich, wenn der Container sichtbar ist
                }
            }

            // Spezialfall für 'volumen', das nur erforderlich ist, wenn es sichtbar ist
            var volumenInput = document.getElementById('volumen');
            if (volumenInput.style.display !== 'none') {
                volumenInput.required = true;
            } else {
                volumenInput.required = false;
            }
            if (!checkbox){
                checkNeueEinheit(document.getElementById('einheit_id').value);
            }
        }
        window.onload = function() {
            toggleForm(document.getElementById('existiertUnterAnderemNamen'));
        }
    </script>
     
    <label for='existiertUnterAnderemNamen'>Existiert ide zutat unter einem anderem Namen?</label>
    <input type='checkbox' id='existiertUnterAnderemNamen' name='existiertUnterAnderemNamen' onchange='toggleForm(this)' checked>
     
    <div id='alternativerNameContainer' style='display:block;'>
        <label for='alternativerName'>Anderer Name:</label>
        <input type='text' id='alternativerName' name='alternativerName'><br><br>
        <input type='submit' name='aktion_name' value='Zutat Unter Anderem Namen Hinzufügen'>
    </div>
    <div id='restDesFormulars' style='display:none;'>
        <label for='haltbarkeit'>Haltbarkeit (in Tagen):</label>
        <input class='restDesFormulars' type='number' id='haltbarkeit' name='haltbarkeit' ><br><br>

        <label for='kategorie_id'>Kategorie:</label>
        <select class='restDesFormulars' id='kategorie_id' name='kategorie_id' >
            <option value=''>Bitte wählen</option>    
            <?php echo generateOptions($conn, 'kategorien', 'id', 'name'); ?>
        </select><br><br>
        
        <label for='phd_kategorie_id'>Planetary Health Diet Category:</label>
        <select class='restDesFormulars' id='phd_kategorie_id' name='phd_kategorie_id' >
            <option value=''>Bitte wählen</option> 
            <?php echo generateOptions($conn, 'Planetary_Health_Diet_Categories', 'ID', 'Kategorie'); ?>
        </select><br><br>

        <label for='einheit_id'>einheit:</label>
        <select id='einheit_id' name='einheit_id'>
            <option value=''>Bitte wählen</option>
            <?php echo generateOptions($conn, 'einheiten', 'id', 'name', true); ?>

            <option value='neuHinzufuegen'>Neu hinzufügen...</option>
        </select><br><br>

        <div id='neueEinheitFormular' style='display:none;'>
            <?php echo einheitsForm(); ?>
        </div>

        <div id='umrechnungsfaktorFeld' style='display: none;'>
            <label for='umrechnungsfaktor'>Umrechnungsfaktor:</label>
            <input type='number' id='umrechnungsfaktor' name='umrechnungsfaktor' step='0.01' required>
            <div> bei 'spezieler Bassis ist die referenc immer Gramm</div><br><br>
        </div>

        <div id='volumen_block' style='display:none;'>
            <label for='volumen'>Volumen:</label>
            <input class='restDesFormulars' type='text' id='volumen' name='volumen'>
            wichtig für PHD da die berenung mit gramm arbeitet<br><br>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
            // Bind change event listener to the units dropdown
            var einheitDropdown = document.getElementById('einheit_id');
            if (einheitDropdown) {
                einheitDropdown.addEventListener('change', handleEinheitChange);
            }

            // Initial check to set the correct state when the page loads
            if (einheitDropdown) {
                handleEinheitChange({target: einheitDropdown});
            }
            
            var basisEinheitDropdown = document.getElementById('basisEinheit');
            if (basisEinheitDropdown) {
                basisEinheitDropdown.addEventListener('change', handleBasisEinheitChange);
            }
            
            // Initial check for basisEinheitDropdown
            if (basisEinheitDropdown) {
                handleBasisEinheitChange({target: basisEinheitDropdown});
            }
        });
        function handleEinheitChange(event) {
            var selectedOption = event.target.options[event.target.selectedIndex];
            var umrechnungsfaktorField = document.getElementById('umrechnungsfaktorFeld');
            var umrechnungsfaktorInput = document.getElementById('umrechnungsfaktor');
            
            // Check if the selected unit requires a conversion factor
            if (selectedOption.dataset.spezifischerUmrechnungsfaktor === '1') {
                umrechnungsfaktorField.style.display = 'block';
                umrechnungsfaktorInput.required = true;
            } else {
                umrechnungsfaktorField.style.display = 'none';
                umrechnungsfaktorInput.required = false;
                umrechnungsfaktorInput.value = ''; // Clear the input if it's not required
            }
        }

        function handleBasisEinheitChange(event) {
            var selectedValue = event.target.value;
            var volumenBlock = document.getElementById('volumen_block');
            var volumenInput = document.getElementById('volumen');
            
            // Adjust visibility and required attribute for the volume input
            if (selectedValue === 'Liter') {
                volumenBlock.style.display = 'block';
                volumenInput.required = true;
            } else {
                volumenBlock.style.display = 'none';
                volumenInput.required = false;
                volumenInput.value = ''; // Clear the input if it's hidden
            }
        }// This function toggles the visibility of the new unit form and its inputs' required status
        function checkNeueEinheit(value) {
            var isNewUnitSelected = value === 'neuHinzufuegen';
            var neueEinheitFormular = document.getElementById('neueEinheitFormular');
            var volumenBlock = document.getElementById('volumen_block');
            var volumenInput = document.getElementById('volumen');

            // Toggle the new unit form visibility
            neueEinheitFormular.style.display = isNewUnitSelected ? 'block' : 'none';

            // Set the required attribute for inputs in the new unit form based on its visibility
            var inputs = neueEinheitFormular.getElementsByTagName('input');
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].required = isNewUnitSelected;
            }
            // Adjust visibility and required attribute for the volumen input
            var selectedOption = document.querySelector('#einheit_id option:checked')
            var displayVolumen = 'none';
            if (value === '2' || 
                (isNewUnitSelected && document.getElementById('basisEinheit').value === 'Liter')|| 
                (selectedOption.getAttribute('data-basis') === '2')) {
                    displayVolumen = 'block';
            }
            volumenBlock.style.display = displayVolumen;
            volumenInput.required = displayVolumen === 'block';

            if (isNewUnitSelected){
                checkBasisEinheit(document.getElementById('basisEinheit').value);
            }// Neuer Teil: Überprüfen, ob die ausgewählte Einheit einen speziellen Umrechnungsfaktor benötigt
             var selectedOption = document.querySelector('#einheit_id option:checked');
            if (selectedOption !== null) { // Check if selectedOption is not null
                var hatSpezifischenUmrechnungsfaktor = selectedOption.getAttribute('data-spezifischer-umrechnungsfaktor') === '1'; // Annahme: '1' bedeutet wahr
                // Sichtbarkeit und Required-Status für das Umrechnungsfaktor-Feld anpassen
                var umrechnungsfaktorFeld = document.getElementById('umrechnungsfaktorFeld'); // Stellen Sie sicher, dass Sie ein entsprechendes Feld im HTML-Markup haben
                
                umrechnungsfaktorFeld.style.display = hatSpezifischenUmrechnungsfaktor ? 'block' : 'none';
                umrechnungsfaktorFeld.required = hatSpezifischenUmrechnungsfaktor;
            } else {
                // Handle the case where no option is selected or exists
                document.getElementById('umrechnungsfaktorFeld').style.display = 'none';
                document.getElementById('umrechnungsfaktorFeld').required = false;
            }}
            // Event-Listener für die Auswahländerung hinzufügen
            document.getElementById('einheit_id').addEventListener('change', function() {
                checkNeueEinheit(this.value);
            });
            // Initialen Check ausführen
            checkNeueEinheit(document.getElementById('einheit_id').value);
            // This function updates the visibility of the volumen input based on the selected base unit
            function checkBasisEinheit(value) {
            var volumenBlock = document.getElementById('volumen_block');
            var volumenInput = document.getElementById('volumen');
            var displayVolumen = value === 'Liter' ? 'block' : 'none';

            volumenBlock.style.display = displayVolumen;
            volumenInput.required = displayVolumen === 'block';
                
            var info_speziel = document.getElementById('info_speziel');
            info_speziel.style.display = value === 'speziell' ?  'block' : 'none';    
            }
        </script>
        <input type='submit' value='Zutat Hinzufügen'>
    </div>
</form>";
}
?>