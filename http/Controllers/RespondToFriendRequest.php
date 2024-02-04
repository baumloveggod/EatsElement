<?php
require_once 'Utils/SessionManager.php';
require_once 'Utils/db_connect.php';

// Stellen Sie sicher, dass der Benutzer eingeloggt ist
if (!isUserLoggedIn()) {
    header("Location: /login.html");
    exit;
}

// Überprüfen, ob die erforderlichen Daten übergeben wurden
if (isset($_POST['request_id']) && isset($_POST['action'])) {
    $requestId = intval($_POST['request_id']);
    $action = $_POST['action'];

    // Verbindung zur Datenbank
    $conn = ...; // Ihre bestehende Datenbankverbindung

    if ($action === 'accept') {
        // Akzeptieren der Freundschaftsanfrage
        // Aktualisieren Sie zuerst den Status in der freundschaftsanfragen Tabelle
        $updateStmt = $conn->prepare("UPDATE freundschaftsanfragen SET status = 'accepted' WHERE id = ?");
        $updateStmt->bind_param("i", $requestId);
        $updateStmt->execute();

        // Dann fügen Sie die Freundschaft in die freunde Tabelle ein
        // Angenommen, Sie haben die Sender- und Empfänger-IDs in der Anfrage gespeichert
        $stmt = $conn->prepare("SELECT sender_id, empfaenger_id FROM freundschaftsanfragen WHERE id = ?");
        $stmt->bind_param("i", $requestId);
        $stmt->execute();
        $result = $stmt->fetch_assoc();

        $insertStmt = $conn->prepare("INSERT INTO freunde (user_id_1, user_id_2) VALUES (?, ?)");
        $insertStmt->bind_param("ii", $result['sender_id'], $result['empfaenger_id']);
        $insertStmt->execute();
    } elseif ($action === 'decline') {
        // Ablehnen der Freundschaftsanfrage
        $updateStmt = $conn->prepare("UPDATE freundschaftsanfragen SET status = 'declined' WHERE id = ?");
        $updateStmt->bind_param("i", $requestId);
        $updateStmt->execute();
    }

    // Weiterleitung oder Ausgabe
    header("Location: /freunde.php");
    exit;
} else {
    echo "Fehler: Daten fehlen.";
}
?>
