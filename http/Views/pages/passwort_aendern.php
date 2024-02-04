<?php
require_once '../../Utils/SessionManager.php';
checkAccess();

// Stelle sicher, dass der Benutzer eingeloggt ist
if (!isUserLoggedIn()) {
    header("Location: /login.html");
    exit;
}

// Passwort-Änderungslogik wird hier behandelt

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <?php include '../templates/header.php'; ?>
    <title>Passwort ändern</title>
</head>
<body>
    <header>
        <?php include '../templates/navigation.php'; ?>
    </header>
    <main>
        <h2>Passwort ändern</h2>
        <form action="/Controllers/passwort_aendern_handler.php" method="post">
            <label for="currentPassword">Aktuelles Passwort:</label>
            <input type="password" id="currentPassword" name="currentPassword" required>
            <label for="newPassword">Neues Passwort:</label>
            <input type="password" id="newPassword" name="newPassword" required>
            <button type="submit">Ändern</button>
        </form>
    </main>
    <footer>
        <p>&copy; 2024 Transforamtions-Design</p>
    </footer>
</body>
</html>
