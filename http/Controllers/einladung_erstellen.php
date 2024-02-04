<?php
require_once '../Utils/CheckSessionManager.php';
require_once '../Utils/db_connect.php';

$userId = $_SESSION['id'];

// Token des eingeloggten Benutzers abrufen
$sql = "SELECT freundes_token FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $freundesToken = $row['freundes_token'];
    $einladungsLink = "https://noadscollective.de/freunde.php?token=" . $freundesToken;
} else {
    echo "Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.";
    exit;
}
?>
