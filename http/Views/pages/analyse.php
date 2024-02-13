<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

$userId = $_SESSION['userId'];

// Setzen Sie den Zeitraum für die Analyse
$startDatum = $_GET['start'] ?? date('Y-m-01'); // Standardmäßig der erste Tag des aktuellen Monats
$endDatum = $_GET['end'] ?? date('Y-m-t'); // Standardmäßig der letzte Tag des aktuellen Monats

// SQL-Abfrage, um die Daten zu holen
$sql = "SELECT 
            e.datum,
            phd.Kategorie,
            SUM(rz.menge) AS tatsaechliche_menge,
            phd.Taegliche_Menge_g AS empfohlene_menge
        FROM essenplan e
        INNER JOIN rezept_zutaten rz ON e.rezept_id = rz.rezept_id
        INNER JOIN zutaten z ON rz.zutat_id = z.id
        INNER JOIN Planetary_Health_Diet_Categories phd ON z.phd_kategorie_id = phd.ID
        WHERE e.user_id = ? AND e.datum BETWEEN ? AND ?
        GROUP BY e.datum, phd.Kategorie
        ORDER BY e.datum, phd.Kategorie;";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $userId, $startDatum, $endDatum);
$stmt->execute();
$result = $stmt->get_result();

// Initialisieren eines Arrays für jede Kategorie mit tatsächlicher und empfohlener Menge
$konsumDaten = [];

while ($row = $result->fetch_assoc()) {
    $kategorie = $row['Kategorie'];
    $tatsaechlicheMenge = $row['tatsaechliche_menge'];
    $empfohleneMenge = $row['empfohlene_menge'];

    // Berechnung des Verhältnisses für jede Kategorie
    if (!isset($konsumDaten[$kategorie])) {
        $konsumDaten[$kategorie] = [
            'tatsaechlich' => 0,
            'empfohlen' => $empfohleneMenge,
            'verhaeltnis' => 0 // Initialwert
        ];
    }

    $konsumDaten[$kategorie]['tatsaechlich'] += $tatsaechlicheMenge;
}

// Berechnung des Verhältnisses der tatsächlichen zur empfohlenen Menge in Prozent
foreach ($konsumDaten as $kategorie => $daten) {
    $konsumDaten[$kategorie]['verhaeltnis'] = ($daten['tatsaechlich'] / $daten['empfohlen']) * 100;
}

// Vorbereitung der Daten für den Chart
$chartLabels = array_keys($konsumDaten);
$chartData = array_column($konsumDaten, 'verhaeltnis');

// Nachdem $chartLabels und $chartData definiert wurden
var_dump($chartLabels);
var_dump($chartData);
    
?>


<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>PHD-Analyse</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Mein Planetary Health Diet Konsum</h1>
    <canvas id="phdChart" width="800" height="400"></canvas>

    <script>
        const chartLabels = <?= json_encode($chartLabels) ?>; // Annahme: Dies sind die Daten
        const chartData = <?= json_encode($chartData) ?>; // Annahme: Dies sind die Prozentwerte

        const ctx = document.getElementById('phdChart').getContext('2d');
        const phdChart = new Chart(ctx, {
            type: 'line', // Änderung zu 'line' für ein Liniendiagramm
            data: {
                labels: chartLabels, // Zeitachse
                datasets: [{
                    label: 'Prozentualer Verbrauch im Verhältnis zur Empfehlung',
                    data: chartData, // Prozentwerte
                    fill: false,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    tension: 0.1 // Macht die Linie ein wenig glatter
                }]
            },
            options: {
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            tooltipFormat: 'DD.MM.YYYY'
                        },
                        title: {
                            display: true,
                            text: 'Datum'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        suggestedMax: 200 // Passt das Y-Achsen-Maximum an
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
</body>
</html>
