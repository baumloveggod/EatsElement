<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/CheckSessionManager.php';
require_once '../../Utils/db_connect.php';

// Stelle sicher, dass der Benutzer eingeloggt ist
if (!isUserLoggedIn()) {
    header("Location: /login.html");
    exit;
}

$userId = $_SESSION['id'];
$today = date("Y-m-d");
$twoWeeksLater = date("Y-m-d", strtotime("+14 days"));

// Modified SQL query to include recipe title
$sql = "SELECT e.datum, e.anzahl_personen, COALESCE(r.titel, 'Nicht geplant') AS titel
        FROM essenplan e
        LEFT JOIN rezepte r ON e.rezept_id = r.id
        WHERE e.user_id = ? AND e.datum BETWEEN ? AND ?
        ORDER BY e.datum ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $userId, $today, $twoWeeksLater);
$stmt->execute();
$result = $stmt->get_result();

$essenspläne = [];  
while ($row = $result->fetch_assoc()) {
    $essenspläne[$row['datum']] = [
        'anzahl_personen' => $row['anzahl_personen'],
        'rezept' => $row['titel']
    ];
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <?php include '../templates/header.php'; ?>
    <title>Essensplan</title>
</head>
<body>
    <header>
        <?php include '../templates/navigation.php'; ?>
    </header>
    <main>
        <h2>Essensplan für die nächsten 14 Tage</h2>
        <ul>
            <?php
            $startDate = new DateTime($today);
            $endDate = new DateTime($twoWeeksLater);
            $endDate = $endDate->modify('+1 day'); // Inkludiert das Enddatum

            $datumRange = new DatePeriod($startDate, new DateInterval('P1D'), $endDate);

            foreach ($datumRange as $datum) {
                $formattedDatum = $datum->format("Y-m-d");
                if (array_key_exists($formattedDatum, $essenspläne)) {
                    $plan = $essenspläne[$formattedDatum];
                    $anzahlPersonen = $plan['anzahl_personen'];
                    $rezeptTitel = $plan['rezept'];
                } else {
                    $anzahlPersonen = "Unbekannt";
                    $rezeptTitel = "Nicht geplant";
                }
                $link = "rezept_detail.php?datum=" . urlencode($formattedDatum);
                echo "<li><a href='" . htmlspecialchars($link) . "'>" . htmlspecialchars($formattedDatum) . ": " . htmlspecialchars($anzahlPersonen) . " Personen - " . htmlspecialchars($rezeptTitel) . "</a></li>";
            }
            ?>
        </ul>
    </main>
    <footer>
        <p>&copy; 2024 Transforamtions-Design</p>
    </footer>
</body>
</html>
