<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verbindung zur Datenbank herstellen
require_once __DIR__ . '/../Utils/db_connect.php';
require_once __DIR__ . '/../Utils/SessionManager.php';
checkUserAuthentication();

echo $_SESSION['userId'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hash das Passwort für die Speicherung
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    if (isset($_SESSION['userId']) && $_SESSION['is_temporary'] == true) {
        // Das bestehende temporäre Profil wird aktualisiert
        $userId = $_SESSION['userId'];
        $sql = "UPDATE users SET username=?, password=?, is_temporary=0 WHERE id=?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute([$username, $hashedPassword, $userId])) {
            
            // Aktualisiere Session-Informationen
            $_SESSION['is_temporary'] = false;
            $_SESSION['username'] = $username;
            // Optional: Setze ein erfolgreiches Login-Flag oder führe eine Weiterleitung durch
            echo "Registrierung erfolgreich!";
            header('Location: /index.php'); // Weiterleitung zur Startseite
            exit;
        } else {
            echo "Fehler beim Aktualisieren des Profils.";
        }
    } else {
        echo "Keine gültige Benutzer-ID gefunden. Registrierung fehlgeschlagen.";
    }
}
?>

