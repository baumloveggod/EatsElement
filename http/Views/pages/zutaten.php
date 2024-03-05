<?php
// Fehlerberichterstattung einschalten
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

include '../templates/zutatenFormular.php';

// Überprüfung, ob das Formular gesendet wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    insertZutat();
}

// Anzeigen der vorhandenen Zutaten
function zeigeZutaten(){
    global $conn;
    $sql = "SELECT z.id, zn.name, z.uebliche_haltbarkeit, z.volumen, k.name AS kategorie, phd.Kategorie AS phd_kategorie, e.name AS einheit
            FROM zutaten z
            LEFT JOIN zutaten_namen zn ON z.id = zn.zutat_id
            LEFT JOIN kategorien k ON z.kategorie_id = k.id
            LEFT JOIN Planetary_Health_Diet_Categories phd ON z.phd_kategorie_id = phd.ID
            LEFT JOIN einheiten e ON z.einheit_id = e.id
            GROUP BY z.id
            ORDER BY zn.name ASC";
    $result = $conn->query($sql);

    echo "<table>";
    echo "<tr><th>Name</th><th>Haltbarkeit</th><th>Volumen</th><th>Kategorie</th><th>PHD Kategorie</th><th>Einheit</th></tr>";
    while($row = $result->fetch_assoc()) {
       
