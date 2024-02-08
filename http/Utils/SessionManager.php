<?php
require_once 'db_connect.php'; // Stellen Sie sicher, dass der Pfad korrekt ist

$userId = 0;
$username = "";
$is_temporary = False;
// Starten der Session
function checkUserAuthentication() {
    global $conn; // Stellen Sie sicher, dass $conn auf Ihre Datenbankverbindung verweist

    if (isset($_COOKIE['authToken'])) {
        $cookieToken = $_COOKIE['authToken'];
        $sql = "SELECT id,is_temporary,username FROM users WHERE cookie_auth_token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $cookieToken);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $userId = $user['id'];
            $username = $user['username'];
            $is_temporary = $user['is_temporary'];
            return true;
        }else{
            
        // Kein gültiger Authentifizierungs-Cookie gefunden, erstelle einen temporären Benutzer
        createTemporaryUserAndRedirect();
        return false;
        }
    }else{
        // Kein gültiger Authentifizierungs-Cookie gefunden, erstelle einen temporären Benutzer
        createTemporaryUserAndRedirect();
        return false;
    }

}

// Funktion, die aufgerufen wird, um Zugriff ohne Anmeldung zu ermöglichen
function createTemporaryUserAndRedirect() {
    global $conn;

    // Erstelle einen temporären Benutzernamen
    $username = "anonym" . rand(1000, 9999);
    $isTemporary = True; // Markierung als temporärer Benutzer

    // Generiere ein zufälliges Token für die Authentifizierung
    $authToken = bin2hex(random_bytes(16));

    // Füge den temporären Benutzer in die Datenbank ein
    $stmt = $conn->prepare("INSERT INTO users (username, is_temporary, cookie_auth_token) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $username, $isTemporary, $authToken);

    if ($stmt->execute()) {
        $userId = $stmt->insert_id;

        // Setze die Authentifizierungs-Cookies
        setcookie('authToken', $authToken, time() + (86400 * 30*365), "/"); // 1 jahr Gültigkeit

        // Leite zum Index weiter
        header("Location: /index.php");
        exit;
    } else {
        // Fehlerbehandlung, falls das Einfügen fehlschlägt
        echo "Fehler beim Erstellen eines temporären Benutzerkontos.";
        exit;
    }
}