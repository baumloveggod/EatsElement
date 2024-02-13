<?php
require_once '../../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

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
    $sqlEnd = ") AS relevanz FROM rezepte r WHERE 1=1";
    $params = []; // Initialisiere das Parameter-Array für Prepared Statements

    
    // Dynamische Ergänzung der SQL-Query basierend auf Suchkriterien
    if ($saisonalitaet) {
        $sqlBase .= " + CASE WHEN EXISTS (
                      SELECT 1 FROM zutaten_saisonalitaet zs
                      JOIN rezept_zutaten rz ON zs.zutat_id = rz.zutat_id
                      WHERE rz.rezept_id = r.id
                      AND CURRENT_DATE BETWEEN zs.saison_start AND zs.saison_ende
                    ) THEN 1 ELSE 0 END";
    }
    

    if ($unverplanteLebensmittel) {
        // Logik zur Berücksichtigung unverplanter Lebensmittel (benötigt spezifische Implementierung)
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


    $sql = $sqlBase . $sqlEnd . " ORDER BY relevanz DESC";
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
            <label for="saisonalitaet">Berücksichtige Saisonalität (Not implemntet jet):</label>
            <input type="checkbox" id="saisonalitaet" name="saisonalitaet"><br>

            <label for="unverplanteLebensmittel">Berücksichtige unverplante Lebensmittel(Not implemntet jet):</label>
            <input type="checkbox" id="unverplanteLebensmittel" name="unverplanteLebensmittel"><br>

            <label for="allergien">Berücksichtige Allergien(Not implemntet jet):</label>
            <input type="text" id="allergien" name="allergien" placeholder="z.B. Nüsse, Gluten"><br>

            <label for="planetaryHealthDiet">Berücksichtige Planetary Health Diet(Not implemntet jet):</label>
            <input type="checkbox" id="planetaryHealthDiet" name="planetaryHealthDiet"><br>

            <label for="suchbegriff">Suchbegriff:</label>
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
