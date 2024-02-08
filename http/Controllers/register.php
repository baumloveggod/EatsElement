<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../Utils/db_connect.php';

session_start();

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = sanitizeInput($_POST['password']);

    // Überprüfe, ob der Benutzername bereits existiert
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0 && $_SESSION['is_temporary'] == False) {
        // Benutzername bereits vergeben
        echo "Benutzername existiert bereits. oder du bist bereits einglogt";
    } else {
        // Benutzername ist verfügbar, fahre mit der Registrierung fort

        // Passwort hashen
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $freundesToken = bin2hex(random_bytes(16)); // Generiert einen eindeutigen Token
        $userId = $_SESSION['userId'];
        // Update the existing user record
        $updateStmt = $conn->prepare("UPDATE users SET username = ?, password = ?, is_temporary = 0 WHERE id = ?");
        $updateStmt->bind_param("ssi", $username, $hashedPassword, $userId);
        
        if ($updateStmt->execute()) {
            // Update session variables
            $_SESSION['username'] = $username;
            $_SESSION['is_temp_user'] = false; // Remove the temporary user flag
            
            // Registrierung erfolgreich, sende eine spezifische Nachricht zurück
            echo "Registrierung erfolgreich!";
        } else {
            // Fehler beim Einfügen in die Datenbank
            echo "Fehler bei der Registrierung: " . $conn->error;
        } 
    }

    $stmt->close();
    $conn->close();
}
?>



