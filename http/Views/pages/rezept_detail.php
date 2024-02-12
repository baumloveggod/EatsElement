<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

$userId = $_SESSION['userId'];

$datum = $_GET['datum'] ?? date("Y-m-d");


$sql = "SELECT r.titel, r.beschreibung, e.rezept_id
        FROM essenplan e
        JOIN rezepte r ON e.rezept_id = r.id
        WHERE e.user_id = $userId AND e.datum = '$datum'";

$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "Kein Rezept für das gewählte Datum gefunden.";
} else {
    $rezept = $result->fetch_assoc();
    $rezeptId = $rezept['rezept_id'];
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <?php include '../templates/header.php'; ?>
    <title>Rezept Details</title>
</head>
<body>
    <header>
        <?php include '../templates/navigation.php'; ?>
    </header>
    <main>
        <?php if (isset($rezept)): ?>
            <h2><?= htmlspecialchars($rezept['titel']); ?></h2>

            <!-- Vor dem Kochen -->
            <section>
                <h3>Vor dem Kochen</h3>
                <?php
                    $sqlAnzahlPersonen = "SELECT anzahl_personen FROM essenplan WHERE user_id = $userId AND datum = '$datum'";
                    $resultAnzahlPersonen = $conn->query($sqlAnzahlPersonen);
                    $rowAnzahlPersonen = $resultAnzahlPersonen->fetch_assoc();
                    $anzahlPersonen = $rowAnzahlPersonen['anzahl_personen'];

                    // Prepare the SQL statement with placeholders
                    $sqlZutaten = "SELECT zn.name, rz.menge, e.name AS einheit, 
                    IF(vs.id IS NOT NULL, 'Im Vorrat', 'Einkaufen') AS status 
                    FROM rezept_zutaten rz 
                    LEFT JOIN einheiten e ON rz.einheit_id = e.id 
                    JOIN zutaten_namen zn ON rz.zutat_id = zn.zutat_id 
                    LEFT JOIN vorratsschrank vs ON zn.zutat_id = vs.zutat_id AND vs.user_id = ? 
                    WHERE rz.rezept_id = ?";

                    // Prepare the statement
                    $stmt = $conn->prepare($sqlZutaten);

                    // Bind parameters to the prepared statement
                    $stmt->bind_param("ii", $userId, $rezeptId); // "ii" means both parameters are integers

                    // Execute the prepared statement
                    $stmt->execute();

                    // Get the result of the query
                    $resultZutaten = $stmt->get_result();

                    // Check if there are results
                    if ($resultZutaten->num_rows > 0) {
                    echo "<p>Zutatenliste und Verfügbarkeit:</p>";
                    echo "<ul>";
                    while ($zutat = $resultZutaten->fetch_assoc()) {
                    // Correctly concatenate and escape output to prevent XSS
                    echo "<li>" . htmlspecialchars($zutat['name']) . " - " . htmlspecialchars($zutat['menge']) . " " . htmlspecialchars($zutat['einheit']) . " (" . htmlspecialchars($zutat['status']) . ")</li>";
                    }
                    echo "</ul>";
                    } else {
                    echo "Keine Zutaten gefunden.";
                    }

                    // Close the statement
                    $stmt->close();
                ?>
                <form action="updatePersonenanzahl.php" method="post">
                    <input type="hidden" name="datum" value="<?= htmlspecialchars($datum); ?>">
                    <label for="anzahlPersonen">Anzahl Personen:</label>
                    <input type="number" id="anzahlPersonen" name="anzahlPersonen" value="<?= $anzahlPersonen; ?>" min="1">
                    <button type="submit">Aktualisieren</button>
                </form>

            </section>

            <!-- Während des Kochens -->
            <section>
                <h3>Während des Kochens</h3>
                <p>... Kochanweisungen und Details ...</p>
            </section>

            <!-- Nach dem Essen -->
            <section>
                <h3>Nach dem Essen</h3>
                <p>Reflektion und Planung:</p>
                <ul>
                    <li><a href="#">Wie hat es geschmeckt?</a></li>
                    <li><a href="#">Noch Hunger?</a></li>
                    <li><a href="#">Gibt es Reste?</a>
                        <ul>
                            <li><a href="#">Für morgen aufheben</a></li>
                            <li><a href="#">Dem Nachbarn geben</a></li>
                        </ul>
                    </li>
                </ul>
            </section>

            <?php else: ?>
            <p>Kein Rezept für das gewählte Datum gefunden.</p>
            <form method="post" action="zufalligesGerichtPlanen.php">
                <input type="hidden" name="datum" value="<?= htmlspecialchars($datum); ?>">
                <button type="submit">Zufälliges Gericht planen</button>
            </form>
        <?php endif; ?>
    </main>
    <footer>
        <p>&copy; 2024 Transformations-Design</p>
    </footer>
</body>
</html>
