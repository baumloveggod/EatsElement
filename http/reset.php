<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'Utils/SessionManager.php';
require_once 'Utils/db_connect.php';
checkUserAuthentication();
$userId = $_SESSION['userId'];

if (isset($_SESSION['is_temp_user'])) {
    echo "Die Freunde-Seite ist für temporäre Profile nicht verfügbar.";
    exit; // Stoppt die Ausführung weiteren Codes
}
// Leere alle Tabellen außer 'users'
$tablesToReset = ['konventionen','zutaten_namen','rezept_zutaten', 'essenplan', 'einkaufsliste', 'vorratsschrank', 'rezepte', 'zutaten', 'kategorien'];
foreach ($tablesToReset as $table) {
    $conn->query("DELETE FROM `$table`"); // Verwende DELETE statt TRUNCATE
    $conn->query("ALTER TABLE `$table` AUTO_INCREMENT = 1"); // Setze den Auto-Increment-Wert zurück, falls gewünscht
}

// Weiterleitung zu test.php
header("Location: /test.php");
exit;
?>
