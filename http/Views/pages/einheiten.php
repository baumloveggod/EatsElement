<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

// Add new unit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name'], $_POST['umrechnungsfaktor'], $_POST['basisEinheit'])) {
    $name = $_POST['name'];
    $umrechnungsfaktor = $_POST['umrechnungsfaktor'];
    $basisEinheit = $_POST['basisEinheit'];
    $basisEinheitId = null;
    $hatSpezifischenUmrechnungsfaktor = false;

    // Determine basisEinheitId and hatSpezifischenUmrechnungsfaktor based on basisEinheit selection
    if ($basisEinheit == 'Gramm') {
        $basisEinheitId = 0; // Ensure this ID exists in your database
    } elseif ($basisEinheit == 'Liter') {
        $basisEinheitId = 1; // Ensure this ID exists in your database
    } elseif ($basisEinheit == 'speziell') {
        $hatSpezifischenUmrechnungsfaktor = true;
        $basisEinheitId = NULL;
        $umrechnungsfaktor = NULL;
    }
    
    $insertSql = "INSERT INTO einheiten (name, umrechnungsfaktor_zu_basis, basis_einheit_id, hat_spezifischen_umrechnungsfaktor) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("sdii", $name, $umrechnungsfaktor, $basisEinheitId, $hatSpezifischenUmrechnungsfaktor);
    
    if ($stmt->execute()) {
        echo "<p>Einheit erfolgreich hinzugefügt!</p>";
    } else {
        echo "<p>Fehler beim Hinzufügen der Einheit: " . $stmt->error . "</p>";
    }
}

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
        <form action="einheiten.php" method="post">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required><br><br>
            
            <label for="umrechnungsfaktor">Umrechnungsfaktor:</label>
            <input type="number" id="umrechnungsfaktor" name="umrechnungsfaktor" step="0.01" required><br><br>
            
            <label for="basisEinheit">Basis Einheit:</label>
            <select id="basisEinheit" name="basisEinheit" required>
                <option value="Liter">Liter</option>
                <option value="Gramm">Gramm</option>
                <option value="speziell">speziell</option>
            </select><br><br>
            
            <button type="submit">Hinzufügen</button>
        </form>
        
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
