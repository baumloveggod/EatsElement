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
    $sqlQuery = buildSqlQuery($searchCriteria, $userId);
    $recipes = executeSearch($sqlQuery, $userId);
    displayRecipes($recipes);
} else{
    displaySearchForm();
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
    global $conn;
    $sqlBase = "SELECT r.*, (0";
    $sqlEnd = ") AS relevanz FROM rezepte r WHERE 1=1";
    $params = [];

    // Example for one criterion, repeat similar structure for others
    $sql_saisonalitaet = "";
    if ($criteria['saisonalitaet']) {
        $sql_saisonalitaet .= " + CASE WHEN EXISTS (
            SELECT 1 FROM zutaten_saisonalitaet zs
            JOIN rezept_zutaten rz ON zs.zutat_id = rz.zutat_id
            WHERE rz.rezept_id = r.id
            AND CURRENT_DATE BETWEEN zs.saison_start AND zs.saison_ende
          ) THEN 1 ELSE 0 END";
    }
    $sql_unverplanteLebensmittel = "";
    if ($criteria['unverplanteLebensmittel']) {
        echo "debug: 1"; // Debug-Statement 1
        // Schritt 1: Ermittle alle Lebensmittel im Vorratsschrank des Benutzers, die noch nicht in einem geplanten Essen verwendet werden.
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
        // Debug-Statement 2: Ausgabe der unverplanten Zutaten
        echo "debug: Unverplante Zutaten: ";
        print_r($unverplanteZutaten);
    
        // Schritt 2: Priorisiere Rezepte, die diese unverplanten Lebensmittel verwenden.
        // Dies könnte z.B. durch eine Erhöhung der Relevanz in der Suchabfrage erfolgen.
        if (!empty($unverplanteZutaten)) {
            foreach ($unverplanteZutaten as $zutatId) {
                // Debug-Statement 3: Ausgabe der verarbeiteten Zutat-ID
                echo "debug: Verarbeite Zutat-ID: $zutatId";
                
                // Erhöhe die Relevanz für Rezepte, die die unverplanten Zutaten enthalten
                $sql_unverplanteLebensmittel .= " + CASE WHEN EXISTS (
                            SELECT 1 FROM rezept_zutaten rz 
                            WHERE rz.rezept_id = r.id AND rz.zutat_id = ?
                          ) THEN 1 ELSE 0 END";
                $params[] = $zutatId; // Füge die Zutat-ID den Parametern für das Prepared Statement hinzu
            }
        }
    }
    $sql_phdPriorisierung = "";
    if ($criteria['planetaryHealthDiet']){
        // Pseudo-Code, um die Logik darzustellen
        $phdDifferenz = berechnePhdDifferenz($userId); // Funktion, die Differenzen für jede Kategorie berechnet

        foreach ($phdDifferenz as $kategorieId => $differenz) {
            if ($differenz < 0) { // Zu wenig konsumiert
                $sql_phdPriorisierung .= " + CASE WHEN EXISTS (
                    SELECT 1 FROM rezept_zutaten rz
                    JOIN zutaten z ON rz.zutat_id = z.id
                    WHERE rz.rezept_id = r.id AND z.phd_kategorie_id = $kategorieId
                ) THEN ".abs($differenz)." ELSE 0 END";
            }
        }
    }         
    /*if (!empty($suchbegriff)) {
    }

    if (!empty($sollEnthalten)) {
        // Logik zur Berücksichtigung spezifischer Zutaten (benötigt spezifische Implementierung)
    }*/
    $sql = $sqlBase . $sql_saisonalitaet . $sql_unverplanteLebensmittel . $sql_phdPriorisierung . $sqlEnd . " ORDER BY relevanz DESC";
    return ['query' => $sql, 'params' => $params];
}
function berechnePhdDifferenz($userId) {
    global $conn;
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
    // SQL-Abfrage, um die Gesamtaufnahme pro PHD-Kategorie in den letzten 30 Tagen zu ermitteln
    $sql = "SELECT z.phd_kategorie_id, SUM(rz.menge) AS gesamt_menge
            FROM essenplan e
            JOIN rezept_zutaten rz ON e.rezept_id = rz.rezept_id
            JOIN zutaten z ON rz.zutat_id = z.id
            WHERE e.user_id = ? AND e.datum BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE()
            GROUP BY z.phd_kategorie_id";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $tatsaechlicheAufnahme = [];
    while ($row = $result->fetch_assoc()) {
        $tatsaechlicheAufnahme[$row['phd_kategorie_id']] = $row['gesamt_menge'];
    }

    // Hole die idealen Mengen für jede PHD-Kategorie
    $idealeMengen = getIdealePhdMengen(); // Diese Funktion müsste die idealen Mengen aus der Datenbank oder einem Array zurückgeben

    $differenzen = [];
    foreach ($idealeMengen as $kategorieId => $idealeMenge) {
        $tatsaechlich = $tatsaechlicheAufnahme[$kategorieId] ?? 0;
        // Berücksichtige die ideale Menge für Tage ohne Konsum
        $tatsaechlich += $tageOhneKonsum * $idealeMenge;
        $differenz = $tatsaechlich - ($idealeMenge * 30); // Umwandlung der Tagesmenge in eine 30-Tage-Menge
        $differenzen[$kategorieId] = $differenz;
    }

    return $differenzen;
}
function getIdealePhdMengen() {
    global $conn;
    $sql = "SELECT ID, Taegliche_Menge_g FROM Planetary_Health_Diet_Categories";
    $result = $conn->query($sql);

    $idealeMengen = [];
    while ($row = $result->fetch_assoc()) {
        $idealeMengen[$row['ID']] = $row['Taegliche_Menge_g'];
    }

    return $idealeMengen;
}
function executeSearch($sqlQuery, $userId) {
    global $conn;
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
function displayRecipes($rezepte) {
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
            <input type="checkbox" id="saisonalitaet" name="saisonalitaet" <?php echo (isset($_GET['saisonalitaet']) ? 'checked' : ''); ?>><br>

            <label for="unverplanteLebensmittel">Berücksichtige unverplante Lebensmittel(working):</label>
            <input type="checkbox" id="unverplanteLebensmittel" name="unverplanteLebensmittel" <?php echo (isset($_GET['unverplanteLebensmittel']) ? 'checked' : ''); ?>><br>

            <label for="allergien">Berücksichtige Allergien(Not implemntet jet):</label>
            <input type="text" id="allergien" name="allergien" placeholder="z.B. Nüsse, Gluten" value="<?php echo htmlspecialchars($_GET['allergien'] ?? ''); ?>"><br>

            <label for="planetaryHealthDiet">Berücksichtige Planetary Health Diet(Not implemntet jet):</label>
            <input type="checkbox" id="planetaryHealthDiet" name="planetaryHealthDiet" <?php echo (isset($_GET['planetaryHealthDiet']) ? 'checked' : ''); ?>><br>

            <label for="suchbegriff">Suchbegriff(mehr schelcht asl recht, aber es läuft):</label>
            <input type="text" id="suchbegriff" name="suchbegriff" placeholder="Suchbegriff eingeben" value="<?php echo htmlspecialchars($_GET['suchbegriff'] ?? ''); ?>"><br>

            <label for="sollEnthalten">Soll enthalten(Not implemntet jet):</label>
            <input type="text" id="sollEnthalten" name="sollEnthalten" placeholder="Zutat" value="<?php echo htmlspecialchars($_GET['sollEnthalten'] ?? ''); ?>"><br>

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
function displaySearchForm() {
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

        <?php include '../templates/footer.php'; ?>
    </body>
    </html>
<?php
}
?>