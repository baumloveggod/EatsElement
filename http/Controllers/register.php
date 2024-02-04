<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../Utils/db_connect.php';

session_start();

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = sanitizeInput($_POST['password']);

    // Überprüfe, ob der Benutzername bereits existiert
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Benutzername bereits vergeben
        echo "Benutzername existiert bereits.";
    } else {
        // Benutzername ist verfügbar, fahre mit der Registrierung fort

        // Passwort hashen
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $freundesToken = bin2hex(random_bytes(16)); // Generiert einen eindeutigen Token

        // Neuen Benutzer einfügen
        $insertStmt = $conn->prepare("INSERT INTO users (username, password, freundes_token) VALUES (?, ?, ?)");
        $insertStmt->bind_param("sss", $username, $hashedPassword, $freundesToken);

        if ($insertStmt->execute()) {
            // Registrierung erfolgreich, Benutzer-ID des neu erstellten Benutzers holen
            $userId = $conn->insert_id;
            
            // Generiere ein einzigartiges Authentifizierungs-Token
            $authToken = bin2hex(random_bytes(50)); // Erhöhe die Byte-Anzahl für mehr Einzigartigkeit
            
            // Speichere das Token im Benutzerdatensatz
            $tokenStmt = $conn->prepare("UPDATE users SET cookie_auth_token = ? WHERE id = ?");
            $tokenStmt->bind_param("si", $authToken, $userId);
            $tokenStmt->execute();
            
            // Setze ein Cookie mit dem Authentifizierungs-Token
            setcookie("auth", $authToken, time() + (86400 * 30), "/"); // Gültig für 30 Tage
            
            // Setze Session-Variablen
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['id'] = $userId;
            
            // Registrierung erfolgreich, sende eine spezifische Nachricht zurück
            echo "Registrierung erfolgreich!";
        } else {
            // Fehler beim Einfügen in die Datenbank
            echo "Fehler bei der Registrierung: " . $conn->error;
        }
    }

    $stmt->close();
    $conn->close();
}
?>
