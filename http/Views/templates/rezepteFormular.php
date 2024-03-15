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
    <script >
document.addEventListener("DOMContentLoaded", function() {
    try {
    addZutatBlock();
} catch (error) {
    console.error("Fehler beim Hinzufügen des Zutatenblocks:", error);
}
 // Initialer Aufruf, um mindestens einen Zutatenblock hinzuzufügen
});
function addZutatBlock() {
    const container = document.getElementById('zutatenContainer');
    const newIndex = container.querySelectorAll('.zutatBlock').length;

    // Erstellen des Zutatenblocks
    const newZutatBlock = document.createElement('div');
    newZutatBlock.classList.add('zutatBlock');
    container.appendChild(newZutatBlock);

    // HTML für das Namens- und Mengenfeld sowie das Dropdown für die Einheiten
    const zutatBlockHTML = `
        <label for="zutaten_${newIndex}_name">Name:</label>
        <input type="text" id="zutaten_${newIndex}_name" name="zutaten[${newIndex}][name]" required><br><br>
        
        <label for="zutaten_${newIndex}_menge">Menge:</label>
        <input type="number" id="zutaten_${newIndex}_menge" name="zutaten[${newIndex}][menge]" required><br><br>
        
        <label for="zutaten_${newIndex}_einheit">Einheit:</label>
        <select id="zutaten_${newIndex}_einheit" name="zutaten[${newIndex}][einheit]" required>
            <option value="">Bitte wählen</option>
            <?php echo generateOptions('einheiten', 'id', 'name'); ?>
        </select><br><br>
    `;
    
    newZutatBlock.innerHTML = zutatBlockHTML;
    
    // Markierung hinzufügen und Listener für den neuen Block initialisieren
    newZutatBlock.dataset.added = "true";
    initInputListener(newZutatBlock, newIndex);
    console.log("Zutatenblock wird hinzugefügt, Index:", newIndex);

}

function initInputListener(block, index) {
    const input = block.querySelector(`#zutaten_${index}_name`);
    input.addEventListener('input', function(event) {
        const isInputFilled = event.target.value.trim() !== '';
        if (isInputFilled && !block.nextSibling) {
            addZutatBlock();
        }
    });
}

</script>
