<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once './Utils/db_connect.php';
require_once './Utils/SessionManager.php';
checkUserAuthentication();

// Überprüfe, ob der Benutzer ein temporäres Profil hat
$sql = "SELECT is_temporary FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['is_temporary']) {
    // Der Nutzer hat ein temporäres Profil
    $istTemporaer = true;
} else {
    $istTemporaer = false;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <?php include './templates/header.php'; ?>
    <title>Willkommen bei EatsElements</title>
</head>
<body>
    <header>
        <?php include_once './templates/navigation.php'; ?> <!-- Pfad entsprechend Ihrer Struktur anpassen -->
    </header>
    <main>
        <div class="welcome-container">
            <h1>Willkommen bei Transformations-Design</h1>
            <p>Entdecken Sie neue Rezepte, planen Sie Ihre Mahlzeiten und verwalten Sie Ihren Vorratsschrank mit Leichtigkeit.</p>
                <?php if ($istTemporaer): ?>
                <div>
                    <p><a href="http/login.html">Anmelden</a> für ein vollständiges Erlebnis.</p>
                    <p>Noch kein Konto? <a href="http/register.html">Registrieren Sie sich hier</a>.</p>
                </div>
            <?php else: ?>
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
