<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

$userId = $_SESSION['userId'];
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <?php include '../templates/header.php'; ?>
    <title>Profil</title>
</head>
<body>
    <header>
        <?php include '../templates/navigation.php'; ?>
    </header>
    <main>
        <h2>Profil</h2>
        <div>
            <?php if (isset($_SESSION['is_temp_user'])): ?>
                <p>Dies ist ein temporäres Profil. <a href="register.html">Registrieren</a> Sie sich, um Ihre Daten zu speichern und auf alle Funktionen zugreifen zu können.</p>
            <?php endif; ?>
            <button onclick="location.href='passwort_aendern.php'">Passwort ändern</button>
            <button onclick="location.href='/http/Controllers/logout.php'">Ausloggen</button>
            <!-- Weitere Profilaktionen hier hinzufügen -->
        </div>
    </main>
    <footer>
        <p>&copy; 2024 Transforamtions-Design</p>
    </footer>
</body>
</html>
