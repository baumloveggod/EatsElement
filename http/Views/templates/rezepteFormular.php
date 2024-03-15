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
