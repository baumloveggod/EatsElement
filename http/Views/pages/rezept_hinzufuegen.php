<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

$userId = $_SESSION['userId'];

$einheiten = [];
$kategorien = [];
$phdKategorien = [];

$conn->begin_transaction();
try {
    // Load units
    $sqlEinheiten = "SELECT id, name FROM einheiten";
    $resultEinheiten = $conn->query($sqlEinheiten);
    while ($row = $resultEinheiten->fetch_assoc()) {
        $einheiten[$row['id']] = $row['name'];
    }

    // Load categories
    $sqlKategorien = "SELECT id, name FROM kategorien ORDER BY sortierreihenfolge";
    $resultKategorien = $conn->query($sqlKategorien);
    while ($row = $resultKategorien->fetch_assoc()) {
        $kategorien[$row['id']] = $row['name'];
    }

    // Load Planetary Health Diet Categories
    $sqlPhdKategorien = "SELECT ID, Kategorie FROM Planetary_Health_Diet_Categories";
    $resultPhdKategorien = $conn->query($sqlPhdKategorien);
    while ($row = $resultPhdKategorien->fetch_assoc()) {
        $phdKategorien[$row['ID']] = $row['Kategorie'];
    }

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    echo "Fehler beim Laden der Daten: " . $e->getMessage();
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['titel'], $_POST['beschreibung'])) {
    $titel = $_POST['titel'];
    $beschreibung = $_POST['beschreibung'];
    $zutaten = isset($_POST['zutaten']) ? $_POST['zutaten'] : [];

    $conn->begin_transaction();
    try {
        $sqlRezept = "INSERT INTO rezepte (titel, beschreibung) VALUES (?, ?)";
        $stmtRezept = $conn->prepare($sqlRezept);
        if (!$stmtRezept->bind_param("ss", $titel, $beschreibung) || !$stmtRezept->execute()) {
            throw new Exception('Fehler beim Speichern des Rezepts.');
        }
        $rezeptId = $stmtRezept->insert_id;
        $stmtRezept->close();

        foreach ($zutaten as $zutat) {
            if (isset($zutat['einheit_name'], $zutat['zutat_id'], $zutat['menge']) &&
                !empty($zutat['einheit_name']) && !empty($zutat['zutat_id']) && !empty($zutat['menge'])) {
                $einheitName = $zutat['einheit_name'];
                $einheitId = null;
                if (!empty($einheitName)) {
                    $sqlFindEinheit = "SELECT id FROM einheiten WHERE name = ?";
                    $stmtFindEinheit = $conn->prepare($sqlFindEinheit);
                    $stmtFindEinheit->bind_param("s", $einheitName);
                    $stmtFindEinheit->execute();
                    $resultFindEinheit = $stmtFindEinheit->get_result();
                    if ($resultFindEinheit->num_rows > 0) {
                        $einheitId = $resultFindEinheit->fetch_assoc()['id'];
                    } else {
                        $sqlAddEinheit = "INSERT INTO einheiten (name, umrechnungsfaktor_zu_basis) VALUES (?, 1)";
                        $stmtAddEinheit = $conn->prepare($sqlAddEinheit);
                        $stmtAddEinheit->bind_param("s", $einheitName);
                        $stmtAddEinheit->execute();
                        $einheitId = $stmtAddEinheit->insert_id;
                    }
                }

                $zutatId = $zutat['zutat_id'];
                $menge = $zutat['menge'];
                $sqlZutat = "INSERT INTO rezept_zutaten (rezept_id, zutat_id, menge, einheit_id) VALUES (?, ?, ?, ?)";
                $stmtZutat = $conn->prepare($sqlZutat);
                if (!$stmtZutat->bind_param("iidi", $rezeptId, $zutatId, $menge, $einheitId) || !$stmtZutat->execute()) {
                    throw new Exception('Fehler beim Speichern der Zutaten.');
                }
                $stmtZutat->close();
            } else {
                // Handle missing or incomplete zutaten information
                throw new Exception('Unvollständige Zutateninformation.');
            }
        }

        $conn->commit();
        header("Location: rezept_detail.php?rezeptId=" . $rezeptId);
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo "Fehler: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <title>Rezept hinzufügen</title>
    <script>
        // Function to dynamically add ingredient fields
        function addZutat() {
            let container = document.getElementById('zutatenContainer');
            let div = document.createElement('div');
            let index = container.childElementCount; // Use the number of current children as the index
            div.setAttribute('data-index', index);
            div.className = 'zutat';
            div.innerHTML = `
                <label>Zutat:</label>
                <input type="text" name="zutaten[${index}][name]" required oninput="searchIngredient(this.value, ${index})">
                <label>Menge:</label>
                <input type="number" step="any" name="zutaten[${index}][menge]" required>
                <label>Einheit (neu oder existierend):</label>
                <input type="text" name="zutaten[${index}][einheit_name]" required>
                <button type="button" onclick="removeZutat(this)">Entfernen</button>
            `;
            container.appendChild(div);
        }


        // Function to remove ingredient fields
        function removeZutat(button) {
            button.parentElement.remove();
        }

        // Placeholder for AJAX call to search for an ingredient
        function searchIngredient(value, index) {
            // Assuming you have an endpoint like "search_ingredient.php" that expects a query parameter "name"
            fetch(`search_ingredient.php?name=${encodeURIComponent(value)}`)
            .then(response => response.json())
            .then(data => {
                // Assuming the response includes a flag indicating if more details are needed and any relevant data
                if (data.needsMoreDetails) {
                    // Dynamically add or modify input fields for this ingredient
                    // For example, adding a select field for the unit if it's not determined
                    let container = document.querySelector(`#zutatenContainer .zutat[data-index="${index}"]`);
                    if (!container.querySelector('.additionalDetails')) { // Avoid adding multiple times
                        let select = document.createElement('select');
                        select.name = `zutaten[${index}][einheit_id]`;
                        select.innerHTML = '<option value="">Wähle Einheit...</option>';
                        data.units.forEach(unit => {
                            select.innerHTML += `<option value="${unit.id}">${unit.name}</option>`;
                        });
                        select.className = 'additionalDetails';
                        container.appendChild(select);
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }

    </script>
</head>
<body>
    <header></header>
    <main>
        <h2>Rezept hinzufügen</h2>
        <form action="rezept_hinzufuegen.php" method="post">
            <div>
                <label for="titel">Titel:</label>
                <input type="text" id="titel" name="titel" required>
            </div>
            <div>
                <label for="beschreibung">Beschreibung:</label>
                <textarea id="beschreibung" name="beschreibung" required></textarea>
            </div>
            <div id="zutatenContainer"></div>
            <button type="button" onclick="addZutat()">Zutat hinzufügen</button>
            <button type="submit">Rezept speichern</button>
        </form>
    </main>
    <footer></footer>
</body>
</html>

CREATE TABLE einheiten (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    umrechnungsfaktor_zu_basis DECIMAL(10, 2) NOT NULL,
    basis_einheit_id INT NULL,
    hat_spezifischen_umrechnungsfaktor BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (basis_einheit_id) REFERENCES einheiten(id),
);
-- Basiseinheiten einfügen
INSERT INTO einheiten (name, umrechnungsfaktor_zu_basis) VALUES ('Gramm', 1), ('Liter', 1);
INSERT INTO einheiten (name, umrechnungsfaktor_zu_basis, basis_einheit_id) VALUES ('Kilogramm', 1000, 1), ('Pfund', 453.59, 1), ('Unze', 28.35, 1);
INSERT INTO einheiten (name, umrechnungsfaktor_zu_basis, basis_einheit_id) VALUES ('Milliliter', 0.001, 2), ('Teelöffel', 0.005, 2), ('Esslöffel', 0.015, 2), ('Tasse', 0.24, 2);

CREATE TABLE zutaten_saisonalitaet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zutat_id INT NOT NULL,
    saison_start DATE NOT NULL,
    saison_ende DATE NOT NULL,
    FOREIGN KEY (zutat_id) REFERENCES zutaten(id)
);

CREATE TABLE Planetary_Health_Diet_Categories (
    ID INT PRIMARY KEY,
    Kategorie VARCHAR(255),
    Taegliche_Menge_g INT,
    Beispiele TEXT
);
INSERT INTO Planetary_Health_Diet_Categories (ID, Kategorie, Taegliche_Menge_g, Beispiele) VALUES
(1, 'Getreide (Vollkorn)', 232, 'Vollkornprodukte, unverarbeitete Mais-, Weizen-, Reis- oder Haferprodukte'),
(2, 'Hülsenfrüchte', 50, 'Linsen, Bohnen, Erbsen, Kichererbsen'),
(3, 'Gemüse', 300, 'Ein Mix aus verschiedenen Gemüsesorten'),
(4, 'Obst', 200, 'Äpfel, Bananen, Orangen, Beeren'),
(5, 'Nüsse und Samen', 50, ''),
(6, 'Fleisch (Rot und verarbeitet)', 14, 'Begrenzen auf rotes und verarbeitetes Fleisch'),
(7, 'Geflügel', 29, ''),
(8, 'Fisch', 28, ''),
(9, 'Milchprodukte', 250, 'Milch, Joghurt, Käse'),
(10, 'Eier', 13, 'Entspricht etwa 1,5 Eiern pro Woche'),
(11, 'Pflanzliche Öle', 40, 'Olivenöl, Rapsöl, Sonnenblumenöl'),
(12, 'Zucker', 31, ''),
(13, 'Stärkehaltiges Gemüse', 50, 'Kartoffeln, Süßkartoffeln');

CREATE TABLE zutaten_namen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    zutat_id INT NOT NULL,
    FOREIGN KEY (zutat_id) REFERENCES zutaten(id)
);

CREATE TABLE konventionen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    single_name_id INT NOT NULL,
    plural_name_id INT,
    zutat_id INT NOT NULL,
    FOREIGN KEY (zutat_id) REFERENCES zutaten(id)
);


CREATE TABLE zutaten (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uebliche_haltbarkeit INT DEFAULT 7 COMMENT 'Haltbarkeit in Tagen',
    kategorie_id INT DEFAULT NULL,
    phd_kategorie_id INT,
    volumen DECIMAL(10, 2),
    einkaufseinheit_id INT,
    kocheinheit_id INT,    
    spezifischer_umrechnungsfaktor DECIMAL(10, 2),
    FOREIGN KEY (phd_kategorie_id) REFERENCES Planetary_Health_Diet_Categories(ID),
    FOREIGN KEY (kategorie_id) REFERENCES kategorien(id),
    FOREIGN KEY (einkaufseinheit_id) REFERENCES einheiten(id),
    FOREIGN KEY (kocheinheit_id) REFERENCES einheiten(id)
);

CREATE TABLE kategorien (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sortierreihenfolge INT NOT NULL
);