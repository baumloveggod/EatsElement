<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

// Berechne die Anzahl der notwendigen Durchläufe für die paginierte Anzeige
$countSql = "SELECT COUNT(id) AS total FROM einheiten";
$countResult = $conn->query($countSql);
$countRow = $countResult->fetch_assoc();
$totalUnits = $countRow['total'];

$batchSize = 5; // Festlegung der Anzahl der Einheiten pro Batch
$loops = ceil($totalUnits / $batchSize); // Berechnung der Anzahl der Durchläufe
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <?php include '../templates/header.php'; ?>
    <title>Einheiten Verwaltung</title>
</head>
<body>
    <?php include '../templates/navigation.php'; ?>
    
    <main>
        <h2>Einheiten Verwaltung</h2>
        
        <h3>Einheit hinzufügen</h3>
            <?php include '../templates/einheitenFormular.php'; ?>

        <h3>Vorhandene Einheiten</h3>
        <table>
            <thead>
                    <tr>
                        <th>Name</th>
                        <th>Umrechnungsfaktor</th>
                        <th>Basis Einheit ID</th>
                        <th>hat_spezifischen_umrechnungsfaktor</th>
                    </tr>
                </thead>
            <tbody>
                <?php 
                for ($i = 0; $i < $loops; $i++) {
                    $offset = $i * $batchSize;
                    $sql = "SELECT id, name, umrechnungsfaktor_zu_basis, basis_einheit_id, hat_spezifischen_umrechnungsfaktor FROM einheiten ORDER BY name ASC LIMIT $offset, $batchSize";
                    $result = $conn->query($sql);

                    while ($row = $result->fetch_assoc()) {
                        // Ausgabe jedes Datensatzes
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['umrechnungsfaktor_zu_basis']) . "</td>";
                        echo "<td>" . (is_null($row['basis_einheit_id']) ? 'NULL' : htmlspecialchars($row['basis_einheit_id'])) . "</td>";
                        echo "<td>" . (htmlspecialchars($row['hat_spezifischen_umrechnungsfaktor']) ? 'ja' : 'Nein') . "</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>

    </main>
    
    <?php include '../templates/footer.php'; ?>
</body>
</html>
