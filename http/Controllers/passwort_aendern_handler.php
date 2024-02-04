<?php
require_once '../Utils/db_connect.php';
require_once '../Utils/SessionManager.php';
checkAccess();


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_SESSION['id'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];

    // Überprüfe das aktuelle Passwort
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        if (password_verify($currentPassword, $user['password'])) {
            // Aktualisiere das Passwort
            $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateStmt->bind_param("si", $newHashedPassword, $userId);
            if ($updateStmt->execute()) {
                echo "Passwort erfolgreich geändert.";
            } else {
                echo "Fehler beim Aktualisieren des Passworts.";
            }
        } else {
            echo "Das aktuelle Passwort ist falsch.";
        }
    } else {
        echo "Benutzer nicht gefunden.";
    }
}
?>
