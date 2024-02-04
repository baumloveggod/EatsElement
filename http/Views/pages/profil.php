<?php
require_once '../../Utils/SessionManager.php';
checkAccess();
require_once '../../Utils/db_connect.php';

$userId = $_SESSION['id'];
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
            <button onclick="location.href='/http/Controllers/logout.php'">Ausloggen</button>
            <button onclick="location.href='passwort_aendern.php'">Passwort ändern</button>
            <!-- Weitere Profilaktionen hier hinzufügen -->
        </div>
    </main>
    <footer>
        <p>&copy; 2024 Transforamtions-Design</p>
    </footer>
</body>
</html>
