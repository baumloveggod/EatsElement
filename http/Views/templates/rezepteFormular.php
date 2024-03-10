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
    checkAndAddZutatBlock();
});

function checkAndAddZutatBlock() {
    const container = document.getElementById('zutatenContainer');
    container.addEventListener('input', function(event) {
        // Findet den zuletzt hinzugefügten Zutatenblock
        const lastZutatBlock = container.querySelector('.zutatBlock:last-of-type');
        const zutatenNameInput = lastZutatBlock.querySelector('.zutatenName');
        const mengeInput = lastZutatBlock.querySelector('.menge');

        // Prüft, ob der letzte Block ausgefüllt wurde
        if (zutatenNameInput && mengeInput && zutatenNameInput.value.trim() !== '' && mengeInput.value.trim() !== '') {
            addZutatBlock(container);
        }
    });
}

function addZutatBlock(container) {
    const newIndex = container.querySelectorAll('.zutatBlock').length;
    const newZutatBlock = document.createElement('div');
    newZutatBlock.classList.add('zutatBlock');
    newZutatBlock.innerHTML = `
        <label>Zutatenname:</label>
        <input type="text" name="zutaten[${newIndex}][name]" class="zutatenName" required>
        <label>Menge:</label>
        <input type="text" name="zutaten[${newIndex}][menge]" class="menge" required>
    `;
    container.appendChild(newZutatBlock);
}
</script>
