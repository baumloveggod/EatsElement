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
    <Script src="../templates/formFunctions.js"></Script>
        <?php require '../templates/zutatenFormular.php';?>
        <h2>Vorhandene Zutaten</h2>
        <?php
$sql = "SELECT 
zutaten.id, 
IFNULL(GROUP_CONCAT(zutaten_namen.name SEPARATOR ', '), 'Kein Name vorhanden') AS names, 
zutaten.uebliche_haltbarkeit, 
zutaten.volumen, 
kategorien.name AS kategorie_name, 
Planetary_Health_Diet_Categories.Kategorie AS phd_kategorie_name, 
einheiten.name AS einheit_name
FROM 
zutaten 
LEFT JOIN zutaten_namen ON zutaten.id = zutaten_namen.zutat_id
LEFT JOIN kategorien ON zutaten.kategorie_id = kategorien.id
LEFT JOIN Planetary_Health_Diet_Categories ON zutaten.phd_kategorie_id = Planetary_Health_Diet_Categories.ID
LEFT JOIN einheiten ON zutaten.einheit_id = einheiten.id
GROUP BY 
zutaten.id 
ORDER BY    
names ASC";


if ($conn->error) {
    die("SQL-Abfrage fehlgeschlagen: " . $conn->error);
}
$result = $conn->query($sql);
if (!$result) {
    die("Fehler bei der Ausführung der Abfrage: " . $conn->error);
}

echo "Anzahl der gefundenen Zeilen: " . $result->num_rows;

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Namen</th><th>Haltbarkeit (Tage)</th><th>Volumen</th><th>Kategorie</th><th>PHD Kategorie</th><th>Einheit</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . (isset($row['names']) ? htmlspecialchars($row['names']) : 'Kein Name vorhanden') . "</td>";
        echo "<td>" . (isset($row['uebliche_haltbarkeit']) ? htmlspecialchars($row['uebliche_haltbarkeit']) : 'N/A') . "</td>";
        echo "<td>" . (isset($row['volumen']) ? htmlspecialchars($row['volumen']) : 'N/A') . "</td>";
        echo "<td>" . (isset($row['kategorie_name']) ? htmlspecialchars($row['kategorie_name']) : 'Keine Kategorie') . "</td>";
        echo "<td>" . (isset($row['phd_kategorie_name']) ? htmlspecialchars($row['phd_kategorie_name']) : 'Keine PHD Kategorie') . "</td>";
        echo "<td>" . (isset($row['einheit_name']) ? htmlspecialchars($row['einheit_name']) : 'Keine Einheit') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Keine Zutaten gefunden.";
}

?>
 </body>
 </html>
