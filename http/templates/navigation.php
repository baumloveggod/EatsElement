<?php
// Füge die erforderlichen Includes ein, um auf die Benutzerdaten zugreifen zu können
require_once '../Utils/db_connect.php';
require_once '../Utils/SessionManager.php';

$einkaufsoption = ''; // Initialisiere die Variable

// Überprüfe, ob der Benutzer eingeloggt ist, und hole die Einkaufsoption

$userId = $_SESSION['id'];
    $sql = "SELECT einkaufsoption FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $einkaufsoption = $row['einkaufsoption'];
    }
?>

<nav>
    <ul>
        <li><a href="Views/pages/heutiges-gericht.php">Heutiges Gericht</a></li>
        <li><a href="Views/pages/essensplan.php">Essensplan</a></li>
        <li><a href="Views/pages/einkaufsliste.php">Einkaufsliste</a></li>
        <li><a href="Views/pages/vorratsschrank.php">Vorratsschrank</a></li>
        <li><a href="Views/pages/settings.php">Einstellungen</a></li>
        <?php if (!isset($_SESSION['is_temp_user'])): ?>
            <li><a href="Views/pages/freunde.php">Freunde</a></li>
        <?php endif; ?>
        <li><a href="Views/pages/profil.php">Profil</a></li>
        <?php if ($einkaufsoption == 'unverpackt'): ?>
            <li><a href="Views/pages/gefaessVerwaltung.php">Gefäßverwaltung</a></li>
        <?php endif; ?>
    </ul>
</nav>
