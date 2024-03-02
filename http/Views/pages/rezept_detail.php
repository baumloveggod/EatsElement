<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

$userId = $_SESSION['userId'];
$datum = $_GET['datum'] ?? date("Y-m-d");
$rezept = null;
$zufallsRezepte = [];

// Versuche, ein Rezept für das gewählte Datum zu finden
$sql = "SELECT r.titel, r.beschreibung, e.rezept_id FROM essenplan e JOIN rezepte r ON e.rezept_id = r.id WHERE e.user_id = ? AND e.datum = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $userId, $datum);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $rezept = $result->fetch_assoc();
} else {
    // Kein Rezept gefunden, hole drei zufällige Rezepte
    $zufallsRezepteSql = "SELECT id, titel FROM rezepte ORDER BY RAND() LIMIT 3";
    $resultZufallsRezepte = $conn->query($zufallsRezepteSql);
    while ($rezeptZufall = $resultZufallsRezepte->fetch_assoc()) {
        $zufallsRezepte[] = $rezeptZufall;
    }
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
        <?php if ($rezept): ?>
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
                                        CASE 
                                            WHEN vs.id IS NULL THEN 'Einkaufen'
                                            WHEN DATE_ADD(vs.verbrauchsdatum, INTERVAL z.uebliche_haltbarkeit DAY) < CURDATE() THEN 'Abgelaufen'
                                            WHEN DATE_ADD(vs.verbrauchsdatum, INTERVAL z.uebliche_haltbarkeit DAY) >= CURDATE() THEN 'Im Vorratsschrank'
                                            ELSE 'Nichts'
                                        END AS status 
                                    FROM rezept_zutaten rz 
                                    JOIN zutaten_namen zn ON rz.zutat_id = zn.zutat_id 
                                    JOIN zutaten z ON zn.zutat_id = z.id
                                    LEFT JOIN einheiten e ON z.einkaufseinheit_id = e.id 
                                    LEFT JOIN vorratsschrank vs ON zn.zutat_id = vs.zutat_id AND vs.user_id = ? 
                                    WHERE rz.rezept_id = ?";

                    // Prepare the statement
                    $stmt = $conn->prepare($sqlZutaten);

                    // Bind parameters to the prepared statement
                    $stmt->bind_param("ii", $userId, $rezept['rezept_id']); // "ii" means both parameters are integers

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
                    <li><a href="#">Wie hat es geschmeckt?(noch nicht implementiert)</a></li>
                    <li><a href="#">Noch Hunger?(noch nicht implementiert)</a></li>
                    <li><a href="#">Gibt es Reste?(noch nicht implementiert)</a>
                        <ul>
                            <li><a href="#">Für morgen aufheben(noch nicht implementiert)</a></li>
                            <li><a href="#">Dem Nachbarn geben(noch nicht implementiert)</a></li>
                        </ul>
                    </li>
                </ul>
            </section>
        <?php else: ?>
            <h2>Kein Rezept für das gewählte Datum gefunden</h2>
            <?php if (!empty($zufallsRezepte)): ?>
                <h3>Vielleicht interessieren Sie sich für:</h3>
                <ul>
                    <?php foreach ($zufallsRezepte as $rezeptZufall): ?>
                        <li><a href='rezept_detail.php?rezeptId=<?= $rezeptZufall['id'] ?>'><?= htmlspecialchars($rezeptZufall['titel']) ?></a></li>
                        <form action="/Controllers/PlanRecipe.php" method="post">
                            <input type="hidden" name="rezept_id" value="<?= $rezeptZufall['id'] ?>">
                            <input type="hidden" name="datum" value="<?= $datum ?>">
                            <button type="submit"> asuwahl</button>
                        </form>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <div>
                <a href='rezeptsuche.php'>Rezept suchen</a> | <a href='rezept_hinzufuegen.php'>Rezept hinzufügen</a>
            </div>
        <?php endif; ?>
    </main>
    <footer>
        <p>&copy; 2024 Transformations-Design</p>
    </footer>
</body>
</html>
