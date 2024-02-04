<?php
session_start();
require_once 'db_connect.php'; // Pfad zur Datenbankverbindung

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    exit('Sie müssen sich zuerst anmelden');
}

// Überprüfen, ob die Freund-ID gesetzt ist
if (!isset($_POST['friendId'])) {
    exit('Keine Freund-ID angegeben');
}

$userId = $_SESSION['id'];
$friendId = $_POST['friendId'];

// Überprüfen, ob bereits eine Freundschaftsanfrage besteht
$stmt = $conn->prepare("SELECT * FROM freundschaftsanfragen WHERE sender_id = ? AND empfaenger_id = ?");
$stmt->bind_param("ii", $userId, $friendId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    exit('Eine Freundschaftsanfrage besteht bereits.');
}

// Einfügen der Freundschaftsanfrage in die Datenbank
$insertStmt = $conn->prepare("INSERT INTO freundschaftsanfragen (sender_id, empfaenger_id, status) VALUES (?, ?, 'pending')");
$insertStmt->bind_param("ii", $userId, $friendId);

if ($insertStmt->execute()) {
    echo "Freundschaftsanfrage erfolgreich gesendet!";
} else {
    echo "Fehler: " . $conn->error;
}

$conn->close();
?>
