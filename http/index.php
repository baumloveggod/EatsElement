<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Utils/init.php'; // Stellt sicher, dass die Session und ggf. ein anonymes Profil initialisiert werden

// Hier könnte weitere Logik folgen, z.B. das Laden spezifischer Inhalte für die Startseite
?>


<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Willkommen bei Transformations-Design</title>
    <link rel="stylesheet" href="style.css"> <!-- Pfad zu Ihrer CSS-Datei anpassen -->
</head>
<body>
    <header>
        <?php include 'Views/templates/navigation.php'; ?> <!-- Navigationsmenü einbinden -->
    </header>
    <main>
        <h1>Willkommen bei Transformations-Design</h1>
        <p>Entdecken Sie neue Rezepte, planen Sie Ihre Mahlzeiten und verwalten Sie Ihren Vorratsschrank.</p>
        <?php if (isset($_SESSION['is_temp_user'])): ?>
            <!-- Hinweis für Benutzer mit einem temporären Profil -->
            <div class="alert">
                <p><a href="login.html">Anmelden</a> für ein vollständiges Erlebnis oder <a href="register.html">registrieren</a> Sie sich, um Ihre Einstellungen zu speichern.</p>
            </div>
        <?php endif; ?>
    </main>
    <footer>
        <p>&copy; <?= date("Y") ?> Transformations-Design. Alle Rechte vorbehalten.</p>
    </footer>
</body>
</html>

