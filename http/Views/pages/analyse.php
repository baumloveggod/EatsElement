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

$sql = "SELECT 
            phd.Kategorie,
            SUM(rz.menge) AS verbrauchte_menge,
            phd.Taegliche_Menge_g
        FROM essenplan e 
        JOIN rezept_zutaten rz ON e.rezept_id = rz.rezept_id
        JOIN zutaten z ON rz.zutat_id = z.id
        JOIN Planetary_Health_Diet_Categories phd ON z.phd_kategorie_id = phd.ID
        WHERE e.user_id = ? AND e.datum BETWEEN ? AND ?
        GROUP BY phd.Kategorie, phd.Taegliche_Menge_g";


// Führen Sie die SQL-Abfrage aus
$stmt = $conn->prepare($sql);
if (!$stmt) {
    // Handle error here, for example:
    echo "Error preparing statement: " . $conn->error;
    exit;
}
$stmt->bind_param("iss", $userId, $startDatum, $endDatum);
if (!$stmt->execute()) {
    // Handle error here
    echo "Error executing statement: " . $stmt->error;
    exit;
}
$result = $stmt->get_result();

$konsumDaten = [];
while ($row = $result->fetch_assoc()) {
    $konsumDaten[] = $row;
}
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
    <canvas id="phdChart" width="400" height="400"></canvas>

    <script>
        const ctx = document.getElementById('phdChart').getContext('2d');
        const phdChart = new Chart(ctx, {
            type: 'bar', // oder ein anderer Chart-Typ je nach Vorliebe
            data: {
                labels: <?= json_encode(array_column($konsumDaten, 'Kategorie')) ?>,
                datasets: [{
                    label: 'Verbrauchte Menge',
                    data: <?= json_encode(array_column($konsumDaten, 'verbrauchte_menge')) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Empfohlene tägliche Menge',
                    data: <?= json_encode(array_column($konsumDaten, 'Taegliche_Menge_g')) ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
