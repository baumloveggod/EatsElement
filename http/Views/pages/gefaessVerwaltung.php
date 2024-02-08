<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

$userId = $_SESSION['userId'];

// Hier kann Logik zur Verwaltung der Gefäße implementiert werden

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <?php include '../templates/header.php'; ?>
    <title>Gefäßverwaltung</title>
</head>
<body>
    <header>
        <?php include '../templates/navigation.php'; ?>
    </header>
    <main>
        <h2>Gefäßverwaltung</h2>
            <form id="gefaessForm" method="post">
                <label for="gefaessName">Name:</label>
                <input type="text" id="gefaessName" name="gefaessName" required>
                
                <label for="volumen">Volumen (in Litern oder Kilogramm):</label>
                <input type="number" id="volumen" name="volumen" step="0.01" required>
                
                <label for="beschreibung">Beschreibung (optional):</label>
                <textarea id="beschreibung" name="beschreibung"></textarea>
                
                <input type="hidden" id="gefaessId" name="gefaessId">
                <button type="submit">Speichern</button>
            </form>
            <div id="gefaessListe"></div>
    </main>
</body>
</html>
