<?php
require_once __DIR__ . '/../Utils/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $itemId = $_POST['id'];

    // Artikel aus der Einkaufsliste abrufen
    $getItemStmt = $conn->prepare("SELECT user_id, zutat_id, menge, verbrauchsdatum FROM einkaufsliste WHERE id = ?");
    $getItemStmt->bind_param("i", $itemId);
    $getItemStmt->execute();
    $itemResult = $getItemStmt->get_result();
    $item = $itemResult->fetch_assoc();

    // Artikel in den Vorratsschrank einfügen
    $insertStmt = $conn->prepare("INSERT INTO vorratsschrank (user_id, zutat_id, menge, verbrauchsdatum) VALUES (?, ?, ?, ?)");
    $insertStmt->bind_param("iiss", $item['user_id'], $item['zutat_id'], $item['menge'], $item['verbrauchsdatum']);
    $insertStmt->execute();

    // Artikel aus der Einkaufsliste entfernen
    $deleteStmt = $conn->prepare("DELETE FROM einkaufsliste WHERE id = ?");
    $deleteStmt->bind_param("i", $itemId);
    $deleteStmt->execute();

    header("Location: /Views/pages/einkaufsliste.php?status=moved");
    exit;
}
?>
