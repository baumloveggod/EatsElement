<?php
require_once 'db_connect.php'; // Stellen Sie sicher, dass der Pfad korrekt ist

$userId = 0;
$username = "";
$is_temporary = 0;
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
            $userId = $result->fetch_assoc();
            $_SESSION['id'] = $userId['id'];

            return true;
        }
    }

    return false;
}

// Funktion, die aufgerufen wird, um Zugriff ohne Anmeldung zu ermöglichen
function createTemporaryUserAndRedirect() {
    global $conn;

    // Erstelle einen temporären Benutzernamen
    $tempUsername = "anonym" . rand(1000, 9999);
    $isTemporary = 1; // Markierung als temporärer Benutzer

    // Generiere ein zufälliges Token für die Authentifizierung
    $authToken = bin2hex(random_bytes(16));

    // Füge den temporären Benutzer in die Datenbank ein
    $stmt = $conn->prepare("INSERT INTO users (username, is_temporary, auth_token) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $tempUsername, $isTemporary, $authToken);

    if ($stmt->execute()) {
        $userId = $stmt->insert_id;

        // Setze die Authentifizierungs-Cookies
        setcookie('userId', $userId, time() + (86400 * 30), "/"); // 30 Tage Gültigkeit
        setcookie('authToken', $authToken, time() + (86400 * 30), "/"); // 30 Tage Gültigkeit

        // Leite zum Index weiter
        header("Location: /index.php");
        exit;
    } else {
        // Fehlerbehandlung, falls das Einfügen fehlschlägt
        echo "Fehler beim Erstellen eines temporären Benutzerkontos.";
        exit;
    }
}