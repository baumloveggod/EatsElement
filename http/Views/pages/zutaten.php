    <?php
    // Fehlerberichterstattung einschalten
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Verbindung zur Datenbank herstellen
    require_once '../../Utils/db_connect.php';

    include '../templates/einheitenFormular.php';

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
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
                
                if ($_POST['einheit_id'] === 'neuHinzufuegen') {
                    
                    // Führe die Funktion zum Hinzufügen der neuen Einheit aus und erhalte die neue Einheits-ID
                    $einheit_id = insert_into_Eineheiten();
                }
                else{
                $einheit_id = $_POST['einheit_id'];
                }

            // Überprüfen, ob die ausgewählte Einheit einen spezifischen Umrechnungsfaktor erfordert
            $einheitQuery = $conn->prepare("SELECT hat_spezifischen_umrechnungsfaktor FROM einheiten WHERE id = ?");
            $einheitQuery->bind_param("i", $einheit_id);
            $einheitQuery->execute();
            $einheitResult = $einheitQuery->get_result();
            if ($einheitRow = $einheitResult->fetch_assoc()) {
                $hatSpezifischenUmrechnungsfaktor = $einheitRow['hat_spezifischen_umrechnungsfaktor'];
            } else {
                $hatSpezifischenUmrechnungsfaktor = false;
            }

            // Setze den Umrechnungsfaktor basierend auf der Einheit
            $umrechnungsfaktor = NULL;
            if ($hatSpezifischenUmrechnungsfaktor) {
                $umrechnungsfaktor = !empty($_POST['umrechnungsfaktor']) ? $_POST['umrechnungsfaktor'] : NULL;
            }

            // Prepared Statement zum Hinzufügen der Zutat vorbereiten
            $stmt = $conn->prepare("INSERT INTO zutaten (uebliche_haltbarkeit, volumen, kategorie_id, phd_kategorie_id, einheit_id, spezifischer_umrechnungsfaktor) VALUES (?, ?, ?, ?, ?, ?)");

            // Parameter binden
            $stmt->bind_param("idiiid", $haltbarkeit, $volumen, $kategorie_id, $phd_kategorie_id, $einheit_id, $umrechnungsfaktor);

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

    ?>

    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <title>Zutat Hinzufügen</title>
    </head>
    <body>
        <h2>Zutat Hinzufügen</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="zutaten_name">Name:</label>
            <input type="text" id="zutaten_name" name="zutaten_name" ><br><br>
            
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
            
            <label for="existiertUnterAnderemNamen">Existiert ide zutat unter einem anderem Namen?</label>
            <input type="checkbox" id="existiertUnterAnderemNamen" name="existiertUnterAnderemNamen" onchange="toggleForm(this)" checked>
            
            <div id="alternativerNameContainer" style="display:block;">
                <label for="alternativerName">Anderer Name:</label>
                <input type="text" id="alternativerName" name="alternativerName"><br><br>
                <input type="submit" name="aktion_name" value="Zutat Unter Anderem Namen Hinzufügen">
            </div>

            <div id="restDesFormulars" style="display:none;">

                <label for="haltbarkeit">Haltbarkeit (in Tagen):</label>
                <input class="restDesFormulars" type="number" id="haltbarkeit" name="haltbarkeit" ><br><br>
                
                
                <label for="kategorie_id">Kategorie:</label>
                <select class="restDesFormulars" id="kategorie_id" name="kategorie_id" >
                    <option value="">Bitte wählen</option>    
                    <?php echo generateOptions($conn, 'kategorien', 'id', 'name'); ?>
                </select><br><br>
                
                <label for="phd_kategorie_id">Planetary Health Diet Category:</label>
                <select class="restDesFormulars" id="phd_kategorie_id" name="phd_kategorie_id" >
                    <?php echo generateOptions($conn, 'Planetary_Health_Diet_Categories', 'ID', 'Kategorie'); ?>
                </select><br><br>
                <label for="einheit_id">einheit:</label>
                <select id="einheit_id" name="einheit_id"  onchange=checkNeueEinheit(this.value)>
                    <option value="">Bitte wählen</option>
                    <?php echo generateOptions($conn, 'einheiten', 'id', 'name', true); ?>

                    <option value="neuHinzufuegen">Neu hinzufügen...</option>
                </select><br><br>
                <div id="neueEinheitFormular" style="display:none;">
                    <?php echo einheitsForm(); ?>
                </div>
                <div id="umrechnungsfaktorFeld" style="display: none;">
                    <label for="umrechnungsfaktor">Umrechnungsfaktor:</label>
                    <input type="number" id="umrechnungsfaktor" name="umrechnungsfaktor" step="0.01" required>
                </div>

                <div id="volumen_block" style="display:none;">
                <label for="volumen">Volumen:</label>
                <input class="restDesFormulars" type="text" id="volumen" name="volumen" style="display:none;" >
                wichtig für PHD da die berenung mit gramm arbeitet<br><br>
                </div>
                <script>
    // This function toggles the visibility of the new unit form and its inputs' required status
    function checkNeueEinheit(value) {
        var isNewUnitSelected = value === "neuHinzufuegen";
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
        var displayVolumen = 'none';
        if (value === '2' || 
        (isNewUnitSelected && document.getElementById('basisEinheit').value === 'Liter')|| 
        (selectedOption.getAttribute('data-basis') === '2')) {
            displayVolumen = 'block';
        }
        
        volumenBlock.style.display = displayVolumen;
        volumenInput.style.display = displayVolumen;
        volumenInput.required = displayVolumen === 'block';

        if (isNewUnitSelected)
            checkBasisEinheit(document.getElementById('basisEinheit').value);

        // Neuer Teil: Überprüfen, ob die ausgewählte Einheit einen speziellen Umrechnungsfaktor benötigt
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
        }
    }
    // Event-Listener für die Auswahländerung hinzufügen
    document.getElementById('einheit_id').addEventListener('change', function() {
        checkNeueEinheit(this.value);
    });
    // Initialen Check ausführen
    checkNeueEinheit(document.getElementById('einheit_id').value);
</script>




                <input type="submit" value="Zutat Hinzufügen">
            
            </div>
        </form>
        <h2>Vorhandene Zutaten</h2>
        <?php
// Vorhandene Zutaten auflisten mit Anpassungen
$sql = "SELECT zutaten.id, 
               GROUP_CONCAT(zutaten_namen.name SEPARATOR ', ') AS names, 
               zutaten.uebliche_haltbarkeit, 
               zutaten.volumen, 
               kategorien.name AS kategorie_name, 
               Planetary_Health_Diet_Categories.Kategorie AS phd_kategorie_name, 
               einheiten.name AS einheit_name
        FROM zutaten 
        JOIN zutaten_namen ON zutaten.id = zutaten_namen.zutat_id
        JOIN kategorien ON zutaten.kategorie_id = kategorien.id
        JOIN Planetary_Health_Diet_Categories ON zutaten.phd_kategorie_id = Planetary_Health_Diet_Categories.ID
        JOIN einheiten ON zutaten.einheit_id = einheiten.id
        GROUP BY zutaten.id
        ORDER BY names ASC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Namen</th><th>Haltbarkeit (Tage)</th><th>Volumen</th><th>Kategorie</th><th>PHD Kategorie</th><th>Einheit</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . htmlspecialchars($row['names']) . "</td><td>" . htmlspecialchars($row['uebliche_haltbarkeit']) . "</td><td>" . htmlspecialchars($row['volumen']) . "</td><td>" . htmlspecialchars($row['kategorie_name']) . "</td><td>" . htmlspecialchars($row['phd_kategorie_name']) . "</td><td>" . htmlspecialchars($row['einheit_name']) . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "Keine Zutaten gefunden.";
}
?>
    </body>
    </html>
