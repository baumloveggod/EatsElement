<?php
// Fehlerberichterstattung einschalten
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verbindung zur Datenbank herstellen
require_once '../../Utils/db_connect.php';

require '../templates/zutaten_post_neu.php'; // Verändertes Backend-Script für die spezielle Logik

// Überprüfen, ob das Formular gesendet wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    update_existent_Zutaten(); // Änderung der Funktion zum Aktualisieren existierender Einträge
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Zutat Aktualisieren</title>
</head>
<body>
    <h2>Zutat Aktualisieren</h2>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <?php 
        // Generieren des Dropdown-Menüs für Zutatennamen
        echo "<label for='zutaten_id'>Zutatenname:</label>";
        echo "<select id='zutaten_id' name='zutaten_id'>";
        $kategorieId = getKategorieId($conn, "Neu Zutaten"); // Funktion zum Abrufen der Kategorie-ID
        $zutaten = getZutatenByKategorieId($conn, $kategorieId);
        foreach ($zutaten as $zutat) {
            echo "<option value='{$zutat['id']}'>" . htmlspecialchars($zutat['name']) . "</option>";
        }
        echo "</select><br><br>";
        ?>
        
        <label for="zutaten_name">Name:</label>
        <input type="text" class="zutaten_name" name="zutaten_name"><br><br>
        <?php require '../templates/zutatenFormular.php'; // Beibehaltung des ursprünglichen Formulars mit Anpassungen ?>
        <Script src="../templates/formFunctions.js"></Script>
    </form>
</body>
</html>

<?php
// Hilfsfunktionen
function getKategorieId($conn, $kategorieName) {
    $stmt = $conn->prepare("SELECT id FROM kategorien WHERE name = ?");
    $stmt->bind_param("s", $kategorieName);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['id'];
    }
    return null;
}

function getZutatenByKategorieId($conn, $kategorieId) {
    $zutaten = [];
    $stmt = $conn->prepare("SELECT z.id, zn.name FROM zutaten z JOIN zutaten_namen zn ON z.id = zn.zutat_id WHERE z.kategorie_id = ?");
    $stmt->bind_param("i", $kategorieId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $zutaten[] = $row;
    }
    return $zutaten;
}
?>
