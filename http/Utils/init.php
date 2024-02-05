<?php 
require_once 'db_connect.php';

session_start();

// Überprüfen, ob der Benutzer bereits eingeloggt ist oder ein temporäres Profil hat
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    // Erstelle einen anonymen Benutzer
    $sql = "INSERT INTO users (username) VALUES ('Anonymer Benutzer')";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = 'Anonymer Benutzer';
        $_SESSION['id'] = $conn->insert_id;
        // Setzen eines Flags, um anzuzeigen, dass es sich um ein temporäres Profil handelt
        $_SESSION['is_temp_user'] = true;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>