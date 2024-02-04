<?php
require_once 'Utils/db_connect.php';
require_once 'Utils/SessionManager.php';

// Sicherstellen, dass nur eingeloggte Benutzer Zugriff haben
if (!isUserLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Benutzerbezogene Logik hier...
$username = $_SESSION['username']; // Beispiel für den Zugriff auf Benutzerdaten

require 'templates/headerLoggedIn.php'; // Kopfzeile für eingeloggte Benutzer
echo "<p>Willkommen, ".htmlspecialchars($username, ENT_QUOTES, 'UTF-8')."!</p>";
// Weitere Inhalte für eingeloggte Benutzer
require 'templates/footer.php'; // Gemeinsamer Fußbereich
