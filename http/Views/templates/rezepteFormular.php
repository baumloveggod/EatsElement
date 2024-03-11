    <?php
    // Fehlerberichterstattung einschalten
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Verbindung zur Datenbank herstellen
    require_once '../../Utils/db_connect.php';

    // Funktion zum Überprüfen der Existenz einer Zutat und Rückgabe eines JSON-Objekts für die Autovervollständigung
    if(isset($_GET['action']) && $_GET['action'] == 'checkZutat' && !empty($_GET['zutatName'])) {
        $zutatName = $_GET['zutatName'];

        $stmt = $conn->prepare("SELECT id, name FROM zutaten_namen WHERE name LIKE ?");
        $searchTerm = "%" . $zutatName . "%";
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();

        $zutaten = [];
        while($row = $result->fetch_assoc()) {
            $zutaten[] = ['id' => $row['id'], 'name' => $row['name']];
        }

        echo json_encode($zutaten);
        exit;
    }
    ?>
    <form action="rezepte_post.php" method="post" enctype="multipart/form-data">
        <label for="titel">Titel:</label><br>
        <input type="text" id="titel" name="titel" required><br>

        <label for="untertitel">Untertitel:</label><br>
        <input type="text" id="untertitel" name="untertitel"><br>

        <label for="zubereitungszeit">Zubereitungszeit (in Minuten):</label><br>
        <input type="number" id="zubereitungszeit" name="zubereitungszeit" required><br>

        <label for="basis_personenanzahl">Basis Personenanzahl:</label><br>
        <input type="number" id="basis_personenanzahl" name="basis_personenanzahl" required><br>

        <label for="bilder">Bilder:</label><br>
        <input type="file" id="bilder" name="bilder"><br>

        <div id="zutatenContainer">
        </div>
        <br>

        <input type="submit" value="Rezept Hinzufügen">
    </form>

<Script src="../templates/formFunctions.js" ></Script>
    <script defer>
document.addEventListener("DOMContentLoaded", function() {
    var zutatenNameInput = document.getElementById('zutaten_name');
    if (zutatenNameInput) { // Überprüfen, ob das Element existiert
        zutatenNameInput.addEventListener('blur', function() {
            var zutatenName = this.value;
    console.log("Geprüfter Zutatenname:", zutatenName); // Debug: Überprüften Namen anzeigen

    if (zutatenName.length > 0) {
        let url = `./Views/rezepte.php?action=checkZutat&zutatName=${encodeURIComponent(zutatenName)}`;
        console.log("Anfrage-URL:", url); // Debug: Anfrage-URL anzeigen

        fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log("Antwort-Daten:", data); // Debug: Antwort-Daten anzeigen

            if (data.length > 0) {
                document.getElementById('existiertUnterAnderemNamen').closest('form').querySelectorAll("input[type='submit']").forEach(function(submitBtn) {
                    submitBtn.closest('div').style.display = 'none';
                });
                console.log("Zutat existiert. Formular wird ausgeblendet."); // Debug: Bestätigung des Ausblendens
            } else {
                document.getElementById('existiertUnterAnderemNamen').closest('form').querySelectorAll("input[type='submit']").forEach(function(submitBtn) {
                    submitBtn.closest('div').style.display = 'block';
                });
                console.log("Zutat existiert nicht. Formular wird eingeblendet."); // Debug: Bestätigung des Einblendens
            }
        })
        .catch(error => {
            console.error('Fehler beim Abrufen der Daten:', error);
        });
    } else {
        console.error("Element mit der ID 'zutaten_name' wurde nicht gefunden.");
    }
    });
    }addZutatBlock();
});
function addZutatBlock() {
    const container = document.getElementById('zutatenContainer');
    const newIndex = container.querySelectorAll('.zutatBlock').length;

    // Erstellen des Zutatenblocks
    const newZutatBlock = document.createElement('div');
    newZutatBlock.classList.add('zutatBlock');
    container.appendChild(newZutatBlock);

    // Laden des HTML-Contents über zutatenFormular.php
    fetch('../templates/zutatenFormular.php')
    .then(response => response.text())
    .then(htmlContent => {
        // HTML-Content anpassen, um eindeutige Namen für die Inputs zu setzen
        const modifiedHtmlContent = htmlContent.replace(/name="zutaten_name"/g, `name="zutaten[${newIndex}][name]"`)
                                               .replace(/id="zutaten_name"/g, `id="zutaten_name_${newIndex}"`)
                                               .replace(/for="zutaten_name"/g, `for="zutaten_name_${newIndex}"`);
        
        // HTML für das angepasste Namensfeld setzen
        newZutatBlock.innerHTML = modifiedHtmlContent;
        
        // HTML für das Mengenfeld direkt hinzufügen
        const mengeHTML = `
            <label for="zutaten_${newIndex}_menge">Menge:</label>
            <input type="text" id="zutaten_${newIndex}_menge" name="zutaten[${newIndex}][menge]"><br><br>
        `;
        newZutatBlock.innerHTML += mengeHTML;

        // Event-Listener für das neue Block hinzufügen
        initInputListener(newZutatBlock, newIndex);
    })
    .catch(error => console.error('Fehler beim Laden des Zutatenblocks:', error));
}


function initInputListener(block, index) {
    const inputs = block.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('input', function(event) {
            // Prüfen, ob es der erste Input ist und ob ein weiterer Block hinzugefügt werden soll
            const isInputFilled = event.target.value.trim() !== '';
            const alreadyAdded = block.dataset.added === "true";
            if (isInputFilled && !alreadyAdded) {
                addZutatBlock();
                block.dataset.added = "true"; // Markieren, dass ein neuer Block hinzugefügt wurde, um Doppelungen zu vermeiden
            }
        });
    });
}
</script>
