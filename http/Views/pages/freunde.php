<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/CheckSessionManager.php';
require_once '../../Utils/db_connect.php';

$userId = $_SESSION['id'];
$eigenerToken = "";

// Hole den eigenen Freundes-Token
$sqlEigen = "SELECT freundes_token FROM users WHERE id = ?";
$stmtEigen = $conn->prepare($sqlEigen);
$stmtEigen->bind_param("i", $userId);
$stmtEigen->execute();
$resultEigen = $stmtEigen->get_result();
if ($eigenRow = $resultEigen->fetch_assoc()) {
    $eigenerToken = $eigenRow['freundes_token'];
}

// Generiere den Einladungslink und den QR-Code mit dem eigenen Token
$einladungsLink = "https://noadscollective.de/Views/pages/freunde.php?token=" . $eigenerToken;

// Überprüfe, ob ein Token-Parameter gesetzt und nicht der eigene Token ist
if (isset($_GET['token']) && $_GET['token'] !== $eigenerToken) {
    $freundesToken = $_GET['token'];

    // Finde den Benutzer mit dem gegebenen Freundes-Token
    $userSql = "SELECT id FROM users WHERE freundes_token = ?";
    $userStmt = $conn->prepare($userSql);
    $userStmt->bind_param("s", $freundesToken);
    $userStmt->execute();
    $userResult = $userStmt->get_result();

    if ($userResult->num_rows === 1) {
        $freund = $userResult->fetch_assoc();
        $freundId = $freund['id'];

        // Füge den Freund hinzu, wenn noch keine Beziehung besteht
        $checkSql = "SELECT id FROM freunde WHERE (user_id_1 = ? AND user_id_2 = ?) OR (user_id_1 = ? AND user_id_2 = ?)";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("iiii", $userId, $freundId, $freundId, $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows === 0) {
            $insertFreundeSql = "INSERT INTO freunde (user_id_1, user_id_2) VALUES (?, ?), (?, ?)";
            $insertFreundeStmt = $conn->prepare($insertFreundeSql);
            $insertFreundeStmt->bind_param("iiii", $userId, $freundId, $freundId, $userId);
            $insertFreundeStmt->execute();
            echo "Freundschaft erfolgreich hinzugefügt.";
        } else {
            echo "Ihr seid bereits Freunde.";
        }
    } else {
        echo "Ungültiger Freundes-Token.";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>

    <meta charset="UTF-8">
    <title>Freunde einladen</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script> <!-- QR Code JS Bibliothek -->
    <script>
        // Funktion zum Kopieren des Einladungslinks in die Zwischenablage
        function kopiereLink() {
            // Erstelle ein temporäres Input-Element
            var tempInput = document.createElement("input");
            tempInput.value = "Melde dich auf der Webseite an, dann können wir zusammen kochen  " + "<?= $einladungsLink; ?>"; // Setze den Einladungslink als Wert
            document.body.appendChild(tempInput); // Füge das Input-Element zum DOM hinzu
            tempInput.select(); // Wähle den Text im Input-Element aus
            document.execCommand("copy"); // Kopiere den ausgewählten Text in die Zwischenablage
            document.body.removeChild(tempInput); // Entferne das temporäre Input-Element
        }
    </script>
</head>
<body>
    
    <?php include '../templates/navigation.php'; ?>
    <h2>Freunde einladen</h2>
    <p>Teile diesen Link, um einen Freund einzuladen:</p>
    <button onclick="kopiereLink()">link kopieren</button>
    <div id="qrcode" style="margin-top: 20px;"></div>
    <script>
        new QRCode(document.getElementById("qrcode"), "<?= $einladungsLink; ?>");
    </script>
</body>
</html>
