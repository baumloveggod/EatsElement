<?php
// Füge die erforderlichen Includes ein, um auf die Benutzerdaten zugreifen zu können
require_once '../../Utils/db_connect.php';
require_once '../../Utils/SessionManager.php';

global $conn;

$einkaufsoption = ''; // Initialisiere die Variable

// Überprüfe, ob der Benutzer eingeloggt ist, und hole die Einkaufsoption

$userId = $_SESSION['userId'];
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
        <li><a href="heutiges-gericht.php">Heutiges Gericht</a></li>
        <li><a href="essensplan.php">Essensplan</a></li>
        <li><a href="einkaufsliste.php">Einkaufsliste</a></li>
        <li><a href="vorratsschrank.php">Vorratsschrank</a></li>
        <?php if (!isset($_SESSION['is_temporary'])): ?>
            <li><a href="freunde.php">Freunde</a></li>
        <?php endif; ?>
        <li><a href="settings.php">Einstellungen</a></li>
        <li><a href="profil.php">Profil</a></li>
        <?php if ($einkaufsoption == 'unverpackt'): ?>
            <li><a href="gefaessVerwaltung.php">Gefäßverwaltung</a></li>
        <?php endif; ?>
    </ul>
</nav>

