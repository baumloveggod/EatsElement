<?php
require_once 'Utils/SessionManager.php';
require_once 'Utils/db_connect.php';

if (!isUserLoggedIn()) {
    header("Location: login.html");
    exit;
}

$userId = $_SESSION['id'];
$friendId = $_POST['friendId'] ?? null; // Ersetzen Sie dies durch die tatsächliche Benutzer-ID des Freundes

if ($friendId) {
    // Freund-Beziehung entfernen
    $sql = "DELETE FROM freunde WHERE (user_id_1 = ? AND user_id_2 = ?) OR (user_id_1 = ? AND user_id_2 = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $userId, $friendId, $friendId, $userId);

    if ($stmt->execute()) {
        echo "Freund erfolgreich entfernt.";
    } else {
        echo "Fehler beim Entfernen des Freundes.";
    }
    $stmt->close();
} else {
    echo "Keine Freund-ID angegeben.";
}

$conn->close();
header("Location: freunde.php"); // Weiterleitung zur Freundeseite
exit;
?>
