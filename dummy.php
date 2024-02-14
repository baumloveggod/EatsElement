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