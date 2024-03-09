<?php
// Fehlerberichterstattung einschalten
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verbindung zur Datenbank herstellen
require_once '../../Utils/db_connect.php';

require_once '../templates/rezepte_post.php';
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Rezept Hinzufügen</title>
</head>
<body>
    <h2>Neues Rezept hinzufügen</h2>
    <?php include '../templates/rezepteFormular.php'; ?>

    <h2>Vorhandene Rezepte</h2>
    <table border="1">
        <tr>
            <th>Titel</th>
            <th>Autor</th>
            <th>Untertitel</th>
            <th>Zubereitungszeit</th>
            <th>Personenanzahl</th>
            <th>Bilder</th>
            <th>Zutaten</th>
        </tr>
        <?php
        $sql = "SELECT 
        r.id, 
        r.titel, 
        r.autor, 
        r.untertitel, 
        r.bilder, 
        r.beschreibung, 
        r.zubereitungszeit, 
        r.basis_personenanzahl, 
        zn.name AS zutaten_name, 
        rz.menge, 
        e.name AS einheit
    FROM 
        rezepte r
    LEFT JOIN 
        rezept_zutaten rz ON r.id = rz.rezept_id
    LEFT JOIN 
        zutaten z ON rz.zutat_id = z.id
    LEFT JOIN 
        zutaten_namen zn ON z.id = zn.zutat_id
    LEFT JOIN 
        einheiten e ON rz.einheit_id = e.id
    GROUP BY 
        r.id, rz.zutat_id
    ORDER BY 
        r.id, zn.name;
    ";
        $result = $conn->query($sql);

        $rezepte = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $rezeptId = $row['id'];
                if (!isset($rezepte[$rezeptId])) {
                    $rezepte[$rezeptId] = [
                        'titel' => $row['titel'],
                        'autor' => $row['autor'],
                        'untertitel' => $row['untertitel'],
                        'bilder' => $row['bilder'],
                        'beschreibung' => $row['beschreibung'],
                        'zubereitungszeit' => $row['zubereitungszeit'],
                        'basis_personenanzahl' => $row['basis_personenanzahl'],
                        'zutaten' => []
                    ];
                }
                if ($row['zutaten_name']) { // Überprüfen, ob der Eintrag eine Zutat hat
                    $rezepte[$rezeptId]['zutaten'][] = htmlspecialchars($row['zutaten_name']) . " " . htmlspecialchars($row['menge']) . " " . htmlspecialchars($row['einheit']);
                }
            }

            foreach ($rezepte as $rezept) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($rezept['titel']?? '') . "</td>";
                echo "<td>" . htmlspecialchars($rezept['autor']?? '') . "</td>";
                echo "<td>" . htmlspecialchars($rezept['untertitel']?? '') . "</td>";
                echo "<td>" . htmlspecialchars($rezept['zubereitungszeit']?? '') . " Minuten</td>";
                echo "<td>" . htmlspecialchars($rezept['basis_personenanzahl']?? '') . "</td>";
                echo "<td>";
                if ($rezept['bilder']) {
                    $bilder = explode(',', $rezept['bilder']);
                    foreach ($bilder as $bild) {
                        echo "<img src='" . htmlspecialchars($bild ?? '') . "' alt='Bild' height='100' /> ";
                    }
                }
                echo "</td>";
                echo "<td>" . implode(', ', $rezept['zutaten']?? '') . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>Keine Rezepte gefunden.</td></tr>";
        }
        ?>
    </table>
</body>
</html>
