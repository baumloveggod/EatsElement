<?php
require_once 'db_connect.php'; // Stellen Sie sicher, dass der Pfad korrekt ist

// Starten der Session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Überprüfen, ob der Benutzer eingeloggt ist
function isUserLoggedIn() {
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        return true;
    }

    if (isset($_COOKIE['auth'])) {
        global $conn;
        $cookieToken = $_COOKIE['auth'];
        $sql = "SELECT id, username FROM users WHERE cookie_auth_token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $cookieToken);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['id'] = $user['id'];

            return true;
        }
    }

    return false;
}

// Funktion, die aufgerufen wird, um Zugriff ohne Anmeldung zu ermöglichen
function createTemporaryUserIfNeeded() {
    if (!isUserLoggedIn()) {
        global $conn;
        
        // Erstelle einen temporären Benutzernamen
        $tempUsername = "anonym" . rand(1000, 9999);

        // Füge den temporären Benutzer in die Datenbank ein
        $stmt = $conn->prepare("INSERT INTO users (username, is_temporary) VALUES (?, 1)");
        $stmt->bind_param("s", $tempUsername);

        if ($stmt->execute()) {
            $userId = $stmt->insert_id;
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $tempUsername;
            $_SESSION['id'] = $userId;
            $_SESSION['is_temp_user'] = true; // Zusätzliche Session-Variable, um temporäre Benutzer zu kennzeichnen
        } else {
            // Fehlerbehandlung, falls das Einfügen fehlschlägt
            echo "Fehler beim Erstellen eines temporären Benutzerkontos.";
            exit;
        }
    }
}