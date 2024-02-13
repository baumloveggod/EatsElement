<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

$userId = $_SESSION['userId'];

// Verarbeitung der Suchanfrage
if ($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET)) {
    // Initialisiere Variablen für Suchkriterien
    $saisonalitaet = isset($_GET['saisonalitaet']) ? true : false;
    $unverplanteLebensmittel = isset($_GET['unverplanteLebensmittel']) ? true : false;
    $allergien = $_GET['allergien'] ?? '';
    $planetaryHealthDiet = isset($_GET['planetaryHealthDiet']) ? true : false;
    $suchbegriff = $_GET['suchbegriff'] ?? '';
    $sollEnthalten = $_GET['sollEnthalten'] ?? '';

    // Basis SQL-Query
    // Basis-SQL-Query mit initialer Punktebewertung
    
    $sqlBase = "SELECT r.*, (0";
    $sql_addons = "";
    $sqlEnd = ") AS relevanz FROM rezepte r WHERE 1=1";
    $params = []; // Initialisiere das Parameter-Array für Prepared Statements

    
    // Dynamische Ergänzung der SQL-Query basierend auf Suchkriterien
    if ($saisonalitaet) {
        $sql_addons .= " + CASE WHEN EXISTS (
                      SELECT 1 FROM zutaten_saisonalitaet zs
                      JOIN rezept_zutaten rz ON zs.zutat_id = rz.zutat_id
                      WHERE rz.rezept_id = r.id
                      AND CURRENT_DATE BETWEEN zs.saison_start AND zs.saison_ende
                    ) THEN 1 ELSE 0 END";
    }
    

    if ($unverplanteLebensmittel) {
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
                $sql_addons .= " + CASE WHEN EXISTS (
                            SELECT 1 FROM rezept_zutaten rz 
                            WHERE rz.rezept_id = r.id AND rz.zutat_id = ?
                          ) THEN 1 ELSE 0 END";
                $params[] = $zutatId; // Füge die Zutat-ID den Parametern für das Prepared Statement hinzu
            }
        }
    }
    
    

    if (!empty($allergien)) {
        // Logik zur Berücksichtigung von Allergien (benötigt spezifische Implementierung)
    }

    if ($planetaryHealthDiet) {
        // Logik zur Berücksichtigung der Planetary Health Diet (benötigt spezifische Implementierung)
    }

    if (!empty($suchbegriff)) {
    }

    if (!empty($sollEnthalten)) {
        // Logik zur Berücksichtigung spezifischer Zutaten (benötigt spezifische Implementierung)
    }


    $sql = $sqlBase . $sql_addons .  $sqlEnd . " ORDER BY relevanz DESC";
    echo $sql;
    // SQL-Query vorbereiten und ausführen
    $stmt = $conn->prepare($sql);
    // Hier müssten die Parameter entsprechend der tatsächlichen Anzahl und Typen gebunden werden
    $stmt->execute($params);
    $result = $stmt->get_result();

    $rezepte = [];
    while ($row = $result->fetch_assoc()) {
        $rezepte[] = $row;
    }
}
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
