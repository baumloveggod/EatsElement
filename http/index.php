<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/Utils/db_connect.php';
require_once __DIR__ . '/Utils/SessionManager.php';

// Überprüfen, ob der Benutzer eingeloggt ist und ob ein potenzieller Freund Cookie existiert
if (isUserLoggedIn() && isset($_COOKIE['potenzieller_freund'])) {
    $userId = $_SESSION['id'];
    $freundId = $_COOKIE['potenzieller_freund'];

    // Überprüfen, ob die Freundschaftsanfrage schon besteht oder Freunde bereits
    $checkSql = "SELECT * FROM freunde WHERE (user_id_1 = ? AND user_id_2 = ?) OR (user_id_1 = ? AND user_id_2 = ?)";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("iiii", $userId, $freundId, $freundId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Füge den Freund hinzu, wenn noch keine Beziehung besteht
        $insertSql = "INSERT INTO freunde (user_id_1, user_id_2) VALUES (?, ?)";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("ii", $userId, $freundId);
        $stmt->execute();

        // Bestätigung für den Benutzer
        $freundHinzugefügt = true;
    }

    // Lösche das Cookie, unabhängig davon, ob der Freund hinzugefügt wurde oder nicht
    setcookie('potenzieller_freund', '', time() - 3600, "/");
}
// Function to show logged-in user content
function showLoggedInUserContent($username) {
    if (empty($username)) {
        // Handle the case where the username is not provided or is empty
        echo "Fehler: Benutzername ist nicht angegeben.";
        return;
    }

    // Sanitize the username to prevent XSS attacks
    $safeUsername = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

    // HTML content for logged-in users
    echo "<!DOCTYPE html>
    <html lang='de'>
    <head>";
        include 'templates/header.php';  
    echo "
        <title>Arbeitstitel</title>
    </head>
    <body>
    <header>
        <h1>main navigator page</h1>";
        include 'templates/navigation.php'; // Correctly including the navigation part

            echo "</header>
        <main>
            <p>Willkommen, $safeUsername!</p>
            <!-- Other content for logged-in users -->
        </main>
    </body>
    </html>";
}

// Function to show guest user content
function showGuestUserContent() {
    // HTML content for guests
    echo "<!DOCTYPE html>
    <html>
    <head>
    <?php include 'templates/header.php'; ?>
        <title>Willkommen bei EatsElemens</title>
    </head>
    <body>
        <div class='welcome-container'>
            <h1>Willkommen bei Transforamtions-Desin</h1>
            <p>Bitte melden Sie sich an, um fortzufahren.</p>
            <a href='login.html'>Anmelden</a>
            <p>Noch kein Konto? <a href='register.html'>Registrieren Sie sich hier</a></p>
        </div>
    </body>
    </html>";
}

// Check if a cookie is set
if (isset($_COOKIE['auth'])) {
    $cookieId = $_COOKIE['auth'];
    $sql = "SELECT id, username FROM users WHERE cookie_auth_token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cookieId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['id'] = $user['id'];

        showLoggedInUserContent($user['username']);
    } else {
        showGuestUserContent();
    }
} else {
    showGuestUserContent();
}
?>
