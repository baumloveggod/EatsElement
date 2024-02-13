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
            <section>
                <h3>Vor dem Kochen</h3>
                <p><?= htmlspecialchars($rezept['beschreibung']); ?></p>
                <!-- Weitere Inhalte wie Zutatenliste und Personenanzahl aktualisieren -->
            </section>
            <!-- Weitere Abschnitte für "Während des Kochens" und "Nach dem Essen" -->
        <?php else: ?>
            <h2>Kein Rezept für das gewählte Datum gefunden</h2>
            <?php if (!empty($zufallsRezepte)): ?>
                <h3>Vielleicht interessieren Sie sich für:</h3>
                <ul>
                    <?php foreach ($zufallsRezepte as $rezeptZufall): ?>
                        <li><a href='rezept_detail.php?rezeptId=<?= $rezeptZufall['id'] ?>'><?= htmlspecialchars($rezeptZufall['titel']) ?></a></li>
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
