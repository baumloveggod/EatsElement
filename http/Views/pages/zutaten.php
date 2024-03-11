<?php
    // Fehlerberichterstattung einschalten
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Verbindung zur Datenbank herstellen
    require_once '../../Utils/db_connect.php';

    require '../templates/zutaten_post.php';
    // Überprüfen, ob das Formular gesendet wurde
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        insert_into_Zutaten();
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
        
        <form action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
        <Script> src="../templates/formFunctions.js"</Script>
        <?php require '../templates/zutatenFormular.php';?>
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
        echo "<tr><td>" . htmlspecialchars($row['names']) . "</td><td>" . htmlspecialchars($row['uebliche_haltbarkeit']) . "</td><td>" . htmlspecialchars($row['volumen'] ?? '') . "</td><td>" . htmlspecialchars($row['kategorie_name']) . "</td><td>" . htmlspecialchars($row['phd_kategorie_name']) . "</td><td>" . htmlspecialchars($row['einheit_name']) . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "Keine Zutaten gefunden.";
}
?>
 </body>
 </html>
