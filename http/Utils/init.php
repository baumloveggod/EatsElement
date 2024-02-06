<?php
require_once 'db_connect.php'; // Stellen Sie sicher, dass der Pfad zur Datenbankverbindungsdatei korrekt ist.

session_start();

// Überprüfen, ob der Benutzer eingeloggt ist oder ein temporäres Profil hat
if (!isset($_SESSION['loggedin'])) {
    // Erstelle einen anonymen Benutzer, falls nicht bereits geschehen
    if (!isset($_SESSION['temp_user_id'])) {
        // Einen temporären Benutzer in der Datenbank erstellen
        // Generiere einen einzigartigen Benutzernamen für das temporäre Profil
        $tempUsername = 'Anonymer Benutzer ' . uniqid();
        
        // Verwenden Sie Prepared Statements, um die SQL-Anweisung sicher auszuführen
        $stmt = $conn->prepare("INSERT INTO users (username, is_temporary) VALUES (?, 1)");
        $stmt->bind_param("s", $tempUsername); // "s" steht für den Datentyp "string"
        
        if ($stmt->execute()) {
            $_SESSION['temp_user_id'] = $stmt->insert_id; // Speichere die ID des temporären Benutzers in der Session
            $_SESSION['is_temp_user'] = true; // Markiere die Session als temporäres Profil
            $_SESSION['loggedin'] = true; // Optional, abhängig von der Logik Ihrer Anwendung
        } else {
            die("Fehler beim Erstellen eines anonymen Profils: " . $stmt->error);
        }
        
        $stmt->close(); // Schließe das Prepared Statement
    }
}

// Funktion zum Überprüfen, ob der Benutzer eingeloggt ist
function isUserLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}
?>   