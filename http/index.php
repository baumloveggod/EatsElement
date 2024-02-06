<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Utils/init.php'; // Pfad entsprechend Ihrer Struktur anpassen

// Hier könnte weitere Logik stehen, z.B. Laden von Benutzerdaten, falls benötigt
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Willkommen bei Transformations-Design</title>
    <link rel="stylesheet" href="http/style.css"> <!-- Pfad entsprechend Ihrer Struktur anpassen -->
</head>
<body>
    <header>
        <?php include_once './templates/navigation.php'; ?> <!-- Pfad entsprechend Ihrer Struktur anpassen -->
    </header>
    <main>
        <div class="welcome-container">
            <h1>Willkommen bei Transformations-Design</h1>
            <p>Entdecken Sie neue Rezepte, planen Sie Ihre Mahlzeiten und verwalten Sie Ihren Vorratsschrank mit Leichtigkeit.</p>
            
            <?php if (isset($_SESSION['is_temp_user'])): ?>
                <!-- Anzeigen einer Nachricht für Nutzer mit einem temporären Profil -->
                <div>
                    <p><a href="http/login.html">Anmelden</a> für ein vollständiges Erlebnis.</p>
                    <p>Noch kein Konto? <a href="http/register.html">Registrieren Sie sich hier</a>.</p>
                </div>
            <?php else: ?>
                <!-- Standard-Nachricht für angemeldete Nutzer oder wenn die Session nicht als temporäres Profil markiert ist -->
                <p>Bereit, Ihr kulinarisches Abenteuer zu beginnen? Erkunden Sie unsere Rezepte oder planen Sie Ihre nächste Mahlzeit.</p>
            <?php endif; ?>
        </div>
    </main>
    <footer>
        <div class="footer-content">
            <p>&copy; <?= date("Y") ?> Transformations-Design. Alle Rechte vorbehalten.</p>
        </div>
    </footer>
</body>
</html>
