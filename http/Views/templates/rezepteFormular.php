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
    <select name="bilder_vorhanden" id="bilder_vorhanden">
        <option value="">Bitte wählen</option>
        <!-- Optionen für vorhandene Bilder -->
    </select>
    oder
    <input type="file" id="bilder" name="bilder"><br>

    <div id="zutatenContainer">
        <div class="zutatBlock">
            <?php require '../templates/zutatenFormular.php'; ?>
            <label>Menge:</label>
            <input type="text" name="zutaten[0][menge]">
        </div>
    </div>
    <br>

    <input type="submit" value="Rezept Hinzufügen">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const zutatenContainer = document.getElementById('zutatenContainer');

    // Event-Delegation für zutatenContainer, um Änderungen an allen Zutatenname-Eingabefeldern zu überwachen
    zutatenContainer.addEventListener('input', function(event) {
        if (event.target.classList.contains('zutatenName')) {
            checkAndAddZutatBlock(event.target);
        }
    });
});

function checkAndAddZutatBlock(currentInput) {
    const container = document.getElementById('zutatenContainer');
    const allZutatenBlocks = container.querySelectorAll('.zutatBlock');
    const lastZutatBlock = allZutatenBlocks[allZutatenBlocks.length - 1];
    const lastZutatenNameInput = lastZutatBlock.querySelector('.zutatenName');

    // Prüft, ob das aktuelle Eingabefeld das letzte Zutatenname-Feld ist und ob es einen Wert hat
    if (currentInput === lastZutatenNameInput && currentInput.value.trim() !== '') {
        const newIndex = allZutatenBlocks.length;
        const newZutatBlock = document.createElement('div');
        newZutatBlock.classList.add('zutatBlock');
        newZutatBlock.innerHTML = `
            <label>Zutatenname:</label>
            <input type="text" name="zutaten[${newIndex}][name]" class="zutatenName">
            <?php require '../templates/zutatenFormular.php'; ?>
            <label>Menge:</label>
            <input type="text" name="zutaten[${newIndex}][menge]">
        `;
        container.appendChild(newZutatBlock);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var zutatenNameInput = document.getElementById('zutatenName');

    zutatenNameInput.addEventListener('input', function() {
        var zutatenName = this.value;

        // AJAX-Anfrage, um zu überprüfen, ob die Zutat existiert
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '../templates/checkZutatExist.php?zutatName=' + encodeURIComponent(zutatenName), true);
        xhr.onload = function() {
            if (this.status == 200) {
                var exists = JSON.parse(this.responseText).exists;
                // Logik, um das Zutatenformular ein- oder auszublenden
                var zutatenFormularContainer = document.getElementById('zutatenFormularContainer');
                if (exists) {
                    zutatenFormularContainer.style.display = 'none';
                } else {
                    zutatenFormularContainer.style.display = 'block';
                }
            }
        };
        xhr.send();
    });
});

</script>
