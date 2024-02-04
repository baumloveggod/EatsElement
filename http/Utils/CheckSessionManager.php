<?php
// Starten der Session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Überprüfen, ob der Benutzer eingeloggt ist
function isUserLoggedIn() {
    // Überprüft, ob das Session-Flag gesetzt ist oder ob ein gültiges Auth-Cookie vorhanden ist
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        return true;
    }

    if (isset($_COOKIE['auth'])) {
        // Verbindung zur Datenbank herstellen (möglicherweise benötigen Sie den globalen $conn)
        require_once 'db_connect.php';

        $cookieToken = $_COOKIE['auth'];
        $sql = "SELECT id FROM users WHERE cookie_auth_token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $cookieToken);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Wenn das Cookie gültig ist, wird die Session entsprechend gesetzt
            $user = $result->fetch_assoc();
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['id'] = $user['id'];

            return true;
        }
    }

    return false;
}

// Überprüfen, ob der Benutzer auf eine Seite zugreifen darf
function checkAccess() {
    $allowedPagesForGuests = ['/index.php', '/login.html', '/register.html'];
    $currentPage = basename($_SERVER['PHP_SELF']);

    if (!isUserLoggedIn() && !in_array($currentPage, $allowedPagesForGuests)) {
        header("Location: /index.php"); // Umleitung zur Index-Seite
        exit;
    }
}

checkAccess();
?>