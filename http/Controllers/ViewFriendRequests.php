<?php
require_once 'Utils/SessionManager.php'; // Stellen Sie sicher, dass dies zu Ihrer Session-Verwaltung passt

if (!isUserLoggedIn()) {
    header("Location: login.html"); // Umleitung zum Login, falls nicht eingeloggt
    exit;
}
$userId = $_SESSION['id']; // Angenommen, die Benutzer-ID wird in der Session gespeichert

require_once 'Utils/db_connect.php'; // Passen Sie dies an Ihren Pfad zur Datenbankverbindung an

$sql = "SELECT id, sender_id FROM freundschaftsanfragen WHERE empfaenger_id = ? AND status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$anfragen = [];
while ($row = $result->fetch_assoc()) {
    $anfragen[] = $row;
}

?>