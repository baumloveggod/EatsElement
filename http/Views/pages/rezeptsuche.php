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
    $sql = "SELECT * FROM rezepte WHERE 1 = 1";
    
    // Dynamische Ergänzung der SQL-Query basierend auf Suchkriterien
    if ($saisonalitaet) {
        // Logik zur Berücksichtigung der Saisonalität (benötigt spezifische Implementierung)
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
        $sql .= " AND (titel LIKE ? OR beschreibung LIKE ?)";
        $params[] = "%$suchbegriff%";
        $params[] = "%$suchbegriff%";
    }

    if (!empty($sollEnthalten)) {
        // Logik zur Berücksichtigung spezifischer Zutaten (benötigt spezifische Implementierung)
    }

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
            <label for="saisonalitaet">Berücksichtige Saisonalität:</label>
            <input type="checkbox" id="saisonalitaet" name="saisonalitaet"><br>

            <label for="unverplanteLebensmittel">Berücksichtige unverplante Lebensmittel:</label>
            <input type="checkbox" id="unverplanteLebensmittel" name="unverplanteLebensmittel"><br>

            <label for="allergien">Berücksichtige Allergien:</label>
            <input type="text" id="allergien" name="allergien" placeholder="z.B. Nüsse, Gluten"><br>

            <label for="planetaryHealthDiet">Berücksichtige Planetary Health Diet:</label>
            <input type="checkbox" id="planetaryHealthDiet" name="planetaryHealthDiet"><br>

            <label for="suchbegriff">Suchbegriff:</label>
            <input type="text" id="suchbegriff" name="suchbegriff" placeholder="Suchbegriff eingeben"><br>

            <label for="sollEnthalten">Soll enthalten:</label>
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
