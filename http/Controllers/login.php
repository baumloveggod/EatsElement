<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../Utils/db_connect.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = sanitizeInput($_POST['password']);

    // Validate inputs
    if (empty($username) || empty($password)) {
        echo "Bitte Benutzername und Passwort eingeben.";
        exit;
    }

    // Prepare SQL statement to prevent SQL injection
    $sql = "SELECT id, username, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['id'] = $user['id'];
        
            // Generate a random token for the cookie
            $cookieToken = bin2hex(random_bytes(25));
        
            // Set cookie for authentication
            setcookie('auth', $cookieToken, time() + (86400 * 30), "/"); // 86400 = 1 day, adjust as needed
        
            // Update the cookie token in the database
            $updateSql = "UPDATE users SET cookie_auth_token = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("si", $cookieToken, $user['id']);
            $updateStmt->execute();
            $updateStmt->close();
        
            // Redirect to the main page
            header("Location: /index.php");
            exit;
        } else {
            // Incorrect password
            echo "Falscher Benutzername oder Passwort.";
        }
    } else {
        echo "Falscher Benutzername oder Passwort.";
    }

    $stmt->close();
}
$conn->close();
?>
