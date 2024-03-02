<?php foreach ($rezept['zutaten'] as $zutat) {
    $stmtCheckZutat->bind_param("s", $zutat['name']);
    $stmtCheckZutat->execute();
    $result = $stmtCheckZutat->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $zutatId = $row['zutat_id'];
        
        // Extract quantity and unit from 'menge'
        preg_match('/^(\d+)\s*(.*)$/', $zutat['menge'], $matches);
        $quantity = (int)$matches[1];
        $unit = trim($matches[2]);
        echo $matches[1] . " + " . $matches[2]
        // Check if the combination of rezept_id and zutat_id already exists
        $stmtCheckExisting = $conn->prepare("SELECT COUNT(*) FROM rezept_zutaten WHERE rezept_id = ? AND zutat_id = ?");
        $stmtCheckExisting->bind_param("ii", $rezeptId, $zutatId);
        $stmtCheckExisting->execute();
        $stmtCheckExisting->bind_result($count);
        $stmtCheckExisting->fetch();
        
        if ($count == 0) {
            // If the entry does not exist, insert it
            // Note: Ensure you have the correct unit ID before this step
            $stmtZutaten->bind_param("iiis", $rezeptId, $zutatId, $quantity, $einheitId);
            $stmtZutaten->execute();
        } else {
            // Entry exists, you might want to skip or update the existing record
            // For example, to update the quantity you could prepare another SQL statement here
        }
    }
    $stmtCheckExisting->close();
}

// Angenommen, $unit enthält den Namen der Einheit
$einheitName = $unit; // Beispiel: "g" für Gramm

// SQL-Abfrage, um die einheit_id zu ermitteln
$stmtEinheit = $conn->prepare("SELECT id FROM einheiten WHERE name = ?");
$stmtEinheit->bind_param("s", $einheitName);
$stmtEinheit->execute();
$resultEinheit = $stmtEinheit->get_result();

if ($resultEinheit->num_rows > 0) {
    // Einheit existiert, benutze existierende einheit_id
    $rowEinheit = $resultEinheit->fetch_assoc();
    $einheitId = $rowEinheit['id'];
} else {
    // Optional: Einheit existiert nicht, füge sie ein und benutze neue einheit_id
    // Dies hängt von deiner Anforderung ab, ob du neue Einheiten automatisch hinzufügen möchtest
    $stmtEinheitInsert = $conn->prepare("INSERT INTO einheiten (name, umrechnungsfaktor_zu_basis) VALUES (?, ?)");
    $umrechnungsfaktorZuBasis = 1; // Standardwert oder berechne basierend auf Einheit
    $stmtEinheitInsert->bind_param("sd", $einheitName, $umrechnungsfaktorZuBasis);
    $stmtEinheitInsert->execute();
    $einheitId = $stmtEinheitInsert->insert_id;
}
?>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../Utils/db_connect.php';
require_once __DIR__ . '/../Utils/SessionManager.php';
checkUserAuthentication();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['zutatenName']) || empty($_POST['menge'])) {
        exit('Fehler: Zutatenname oder Menge fehlt.');
    }
    $zutatenName = $_POST['zutatenName'];
    $menge = $_POST['menge'];
    $userId = $_SESSION['userId'];

    // Überprüfen, ob die Zutat bereits existiert
    $stmt = $conn->prepare("SELECT id FROM zutaten_namen WHERE name = ?");
    $stmt->bind_param("s", $zutatenName);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        // Zutat existiert nicht, also füge sie ein
        // Hier fügen wir nur in 'zutaten_namen' und 'zutaten' ein, ohne 'beschreibung'
        $stmt = $conn->prepare("INSERT INTO zutaten (kategorie_id) VALUES (NULL)"); // Adjust according to your schema
        $stmt->execute();
        $zutatId = $conn->insert_id;
        
        // Füge den Namen in 'zutaten_namen' ein
        $stmt = $conn->prepare("INSERT INTO zutaten_namen (name, zutat_id) VALUES (?, ?)");
        $stmt->bind_param("si", $zutatenName, $zutatId);
        $stmt->execute();
    } else {
        // Zutat existiert, hole die zutat_id
        $zutat = $result->fetch_assoc();
        $zutatId = $zutat['id'];
    }

    // Füge die Zutat in die Einkaufsliste ein
    $stmt = $conn->prepare("INSERT INTO einkaufsliste (user_id, zutat_id, menge) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $userId, $zutatId, $menge);
    $stmt->execute();
    header("Location: /Views/pages/einkaufsliste.php");
    exit;
}
?>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

$userId = $_SESSION['userId'];

if ($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET)) {
    $searchCriteria = prepareSearchCriteria($_GET);
    $sqlQuery = buildSqlQuery($searchCriteria, $userId);
    $recipes = executeSearch($sqlQuery, $userId);
    displayRecipes($recipes);
}

function prepareSearchCriteria($getData) {
    return [
        'saisonalitaet' => isset($getData['saisonalitaet']),
        'unverplanteLebensmittel' => isset($getData['unverplanteLebensmittel']),
        'allergien' => $getData['allergien'] ?? '',
        'planetaryHealthDiet' => isset($getData['planetaryHealthDiet']),
        'suchbegriff' => $getData['suchbegriff'] ?? '',
        'sollEnthalten' => $getData['sollEnthalten'] ?? '',
    ];
}

function buildSqlQuery($criteria, $userId) {
    $sqlBase = "SELECT r.*, (0";
    $sqlAddons = "";
    $sqlEnd = ") AS relevanz FROM rezepte r WHERE 1=1";
    $params = [];

    // Example for one criterion, repeat similar structure for others
    if ($criteria['saisonalitaet']) {
        $sqlAddons .= " + CASE WHEN EXISTS (...) THEN 1 ELSE 0 END";
    }

    // Continue building $sqlAddons based on other criteria...

    $sql = $sqlBase . $sqlAddons . $sqlEnd . " ORDER BY relevanz DESC";
    return ['query' => $sql, 'params' => $params];
}

function executeSearch($sqlQuery, $userId) {
    global $conn; // Assuming $conn is your database connection variable
    $stmt = $conn->prepare($sqlQuery['query']);
    // Assuming all parameters are integers, adjust as necessary
    if (!empty($sqlQuery['params'])) {
        $types = str_repeat("i", count($sqlQuery['params']));
        $stmt->bind_param($types, ...$sqlQuery['params']);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $recipes = [];
    while ($row = $result->fetch_assoc()) {
        $recipes[] = $row;
    }
    return $recipes;
}

function displayRecipes($recipes) {
?>
    <!DOCTYPE html>
<html lang="de">
<head>
    <?php include '../templates/header.php'; ?>
    <title>Rezeptsuche</title>
</head>
<body>
    <?php include '../templates/navigation.php'; ?>

    <main>
        <h2>Rezeptsuche</h2>
        <form action="rezeptsuche.php" method="get">
            <label for="saisonalitaet">Berücksichtige Saisonalität (noch keine Saisonalität tabelle):</label>
            <input type="checkbox" id="saisonalitaet" name="saisonalitaet"><br>

            <label for="unverplanteLebensmittel">Berücksichtige unverplante Lebensmittel(working):</label>
            <input type="checkbox" id="unverplanteLebensmittel" name="unverplanteLebensmittel"><br>

            <label for="allergien">Berücksichtige Allergien(Not implemntet jet):</label>
            <input type="text" id="allergien" name="allergien" placeholder="z.B. Nüsse, Gluten"><br>

            <label for="planetaryHealthDiet">Berücksichtige Planetary Health Diet(Not implemntet jet):</label>
            <input type="checkbox" id="planetaryHealthDiet" name="planetaryHealthDiet"><br>

            <label for="suchbegriff">Suchbegriff(mehr schelcht asl recht, aber es läuft):</label>
            <input type="text" id="suchbegriff" name="suchbegriff" placeholder="Suchbegriff eingeben"><br>

            <label for="sollEnthalten">Soll enthalten(Not implemntet jet):</label>
            <input type="text" id="sollEnthalten" name="sollEnthalten" placeholder="Zutat"><br>

            <button type="submit">Suchen</button>
        </form>
    </main>
    <main>
    <h2>Suchergebnisse</h2>
    <?php if (!empty($rezepte)): ?>
        
        <ul>
            <?php foreach ($rezepte as $rezept): ?>
                <li>
                    <h3><?= htmlspecialchars($rezept['titel']) ?></h3>
                    <p><?= htmlspecialchars($rezept['beschreibung']) ?></p>
                    <p>Relevanz: <?= htmlspecialchars($rezept['relevanz']) ?></p>

                    <!-- Weitere Details zum Rezept -->
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Keine Rezepte gefunden.</p>
    <?php endif; ?>
</main>

    <?php include '../templates/footer.php'; ?>
</body>
</html>
<?php
}

function berechnePhdDifferenz($userId, $conn) {
    // Ermittle die Anzahl der Tage in den letzten 30 Tagen, an denen der Benutzer Essen konsumiert hat
    $sqlTageMitKonsum = "SELECT COUNT(DISTINCT datum) AS tage_mit_konsum
                         FROM essenplan
                         WHERE user_id = ? AND datum BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE()";
    $stmtTageMitKonsum = $conn->prepare($sqlTageMitKonsum);
    $stmtTageMitKonsum->bind_param("i", $userId);
    $stmtTageMitKonsum->execute();
    $resultTageMitKonsum = $stmtTageMitKonsum->get_result();
    $rowTageMitKonsum = $resultTageMitKonsum->fetch_assoc();
    $tageMitKonsum = $rowTageMitKonsum['tage_mit_konsum'];

    // Berechne die Anzahl der Tage ohne Konsum
    $tageOhneKonsum = 30 - $tageMitKonsum;

    // Führe die restliche Logik aus, um die tatsächliche Aufnahme zu ermitteln
    // (Der restliche Teil der Funktion bleibt gleich wie im vorherigen Beispiel)

    // Hole die idealen Mengen für jede PHD-Kategorie
    $idealeMengen = getIdealePhdMengen($conn);

    $differenzen = [];
    foreach ($idealeMengen as $kategorieId => $idealeMenge) {
        $tatsaechlich = $tatsaechlicheAufnahme[$kategorieId] ?? 0;
        // Berücksichtige die ideale Menge für Tage ohne Konsum
        $tatsaechlich += $tageOhneKonsum * $idealeMenge;
        $differenz = $tatsaechlich - (30 * $idealeMenge); // Umwandlung der Tagesmenge in eine 30-Tage-Menge
        $differenzen[$kategorieId] = $differenz;
    }

    return $differenzen;
}


<form action="rezeptsuche.php" method="get">
            <label for="saisonalitaet">Berücksichtige Saisonalität (noch keine Saisonalität tabelle):</label>
            <input type="checkbox" id="saisonalitaet" name="saisonalitaet"><br>

            <label for="unverplanteLebensmittel">Berücksichtige unverplante Lebensmittel(working):</label>
            <input type="checkbox" id="unverplanteLebensmittel" name="unverplanteLebensmittel"><br>

            <label for="allergien">Berücksichtige Allergien(Not implemntet jet):</label>
            <input type="text" id="allergien" name="allergien" placeholder="z.B. Nüsse, Gluten"><br>

            <label for="planetaryHealthDiet">Berücksichtige Planetary Health Diet(Not implemntet jet):</label>
            <input type="checkbox" id="planetaryHealthDiet" name="planetaryHealthDiet"><br>

            <label for="suchbegriff">Suchbegriff(mehr schelcht asl recht, aber es läuft):</label>
            <input type="text" id="suchbegriff" name="suchbegriff" placeholder="Suchbegriff eingeben"><br>

            <label for="sollEnthalten">Soll enthalten(Not implemntet jet):</label>
            <input type="text" id="sollEnthalten" name="sollEnthalten" placeholder="Zutat"><br>

            <button type="submit">Suchen</button>
        </form>
?>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

$userId = $_SESSION['userId'];

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $searchCriteria = prepareSearchCriteria($_GET);
    $sqlQueryDetails = buildSqlQuery($searchCriteria, $userId);
    $recipes = executeSearch($sqlQueryDetails, $userId);
    displayRecipes($recipes);
} else {
    displaySearchForm();
}

function prepareSearchCriteria($getData) {
    // Ihre vorhandene Funktion
}

function buildSqlQuery($criteria, $userId) {
    global $conn;
    $sqlBase = "SELECT r.*";
    $sqlRelevanzFelder = ", (0"; // Start der gesamtrelevanz Berechnung
    $sqlEnd = " FROM rezepte r WHERE 1=1";
    $params = [];

    // Saisonalitätskriterium
    $sql_saisonalitaet = "";
    if ($criteria['saisonalitaet']) {
        $sqlRelevanzFelder .= " + CASE WHEN EXISTS (
            SELECT 1 FROM zutaten_saisonalitaet zs
            JOIN rezept_zutaten rz ON zs.zutat_id = rz.zutat_id
            WHERE rz.rezept_id = r.id
            AND CURRENT_DATE BETWEEN zs.saison_start AND zs.saison_ende
          ) THEN 1 ELSE 0 END";
        $sql_saisonalitaet = ", CASE WHEN EXISTS (
            SELECT 1 FROM zutaten_saisonalitaet zs
            JOIN rezept_zutaten rz ON zs.zutat_id = rz.zutat_id
            WHERE rz.rezept_id = r.id
            AND CURRENT_DATE BETWEEN zs.saison_start AND zs.saison_ende
          ) THEN 1 ELSE 0 END AS relevanz_saisonalitaet";
    }

    // Implementierung für unverplante Lebensmittel und PHD hier hinzufügen...

    $sqlRelevanzFelder .= ") AS gesamtrelevanz"; // Ende der gesamtrelevanz Berechnung

    // Zusammenbau des finalen SQL-Queries
    $sql = $sqlBase . $sql_saisonalitaet /* Weitere spezifische Relevanzfelder hier */ . $sqlRelevanzFelder . $sqlEnd . " ORDER BY gesamtrelevanz DESC";
    return ['query' => $sql, 'params' => $params];
}

function executeSearch($sqlQueryDetails, $userId) {
    global $conn;
    $stmt = $conn->prepare($sqlQueryDetails['query']);
    if (!empty($sqlQueryDetails['params'])) {
        $types = str_repeat("i", count($sqlQueryDetails['params']));
        $stmt->bind_param($types, ...$sqlQueryDetails['params']);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $recipes = [];
    while ($row = $result->fetch_assoc()) {
        $recipes[] = $row;
    }
    return $recipes;
}

function displayRecipes($rezepte) {
    // Ihr modifizierter HTML- und PHP-Code zur Anzeige der Rezepte mit Relevanzaufschlüsselung
}

function displaySearchForm() {
    // Ihr vorhandener Code zur Anzeige des Suchformulars
}

// Funktionen wie berechnePhdDifferenz, getIdealePhdMengen, und alle weiteren benötigten Hilfsfunktionen hier...
if ($criteria['unverplanteLebensmittel']) {
    echo "debug: 1"; // Debug-Statement 1
    
    // Step 1: Identify all unallocated ingredients in the user's pantry.
    $vorratsQuery = "SELECT vs.zutat_id FROM vorratsschrank vs
                     WHERE vs.user_id = ?
                     AND vs.zutat_id NOT IN (
                         SELECT rz.zutat_id FROM essenplan e
                         JOIN rezept_zutaten rz ON e.rezept_id = rz.rezept_id
                         WHERE e.user_id = ? AND e.datum BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                     )";
    $vorratsStmt = $conn->prepare($vorratsQuery);
    $vorratsStmt->bind_param("ii", $userId, $userId);
    
    $vorratsStmt->execute();
    $vorratsResult = $vorratsStmt->get_result();
    $unverplanteZutaten = [];
    while ($row = $vorratsResult->fetch_assoc()) {
        $unverplanteZutaten[] = $row['zutat_id'];
    }
    
    echo "debug: Unverplante Zutaten: "; // Debug-Statement 2
    print_r($unverplanteZutaten);

    // Step 2: Prioritize recipes that use these unallocated ingredients.
    if (!empty($unverplanteZutaten)) {
        $placeholders = implode(',', array_fill(0, count($unverplanteZutaten), '?')); // Create placeholders
        $sql_unverplanteLebensmittel = " AND r.id IN (
                SELECT rz.rezept_id FROM rezept_zutaten rz WHERE rz.zutat_id IN ($placeholders)
            )";
        foreach ($unverplanteZutaten as $zutatId) {
            echo "debug: Verarbeite Zutat-ID: $zutatId"; // Debug-Statement 3
        }
        $params = array_merge($params, $unverplanteZutaten); // Merge ingredient IDs into params
    }s da
}
<?php
require_once '../Utils/db_connect.php';
require_once '../Utils/SessionManager.php';

checkUserAuthentication(); // Stellen Sie sicher, dass der Benutzer eingeloggt ist

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rezept_id'], $_POST['datum'])) {
    $rezeptId = $_POST['rezept_id'];
    $datum = $_POST['datum'];
    $userId = $_SESSION['userId']; // Holen Sie die Benutzer-ID aus der Session

    // Bereiten Sie die SQL-Anweisung vor, um das Rezept zum Essensplan hinzuzufügen
    $sql = "INSERT INTO essenplan (user_id, datum, rezept_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $userId, $datum, $rezeptId);

    if ($stmt->execute()) {
        // Erfolg: Weiterleitung zurück zum Rezept-Detail oder einer Erfolgsmeldung
        header("Location: /Views/pages/rezept_detail.php?datum=" . urlencode($datum));
        exit();
    } else {
        // Fehlerbehandlung
        echo "Fehler beim Hinzufügen des Rezepts zum Essensplan.";
    }
}
?>
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
                                    LEFT JOIN einheiten e ON rz.einkaufseinheit_id = e.id 
                                    JOIN zutaten_namen zn ON rz.zutat_id = zn.zutat_id 
                                    LEFT JOIN zutaten z ON zn.zutat_id = z.id
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
