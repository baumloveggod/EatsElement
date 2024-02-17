<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Require necessary utility files
require_once __DIR__ . '/../Utils/db_connect.php';
require_once __DIR__ . '/../Utils/SessionManager.php';

// Check user authentication
checkUserAuthentication();

// Check if the request method is POST and the required 'id' field is set
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $itemId = intval($_POST['id']);
    $userId = $_SESSION['userId']; // Assuming session starts in checkUserAuthentication()

    // Prepare SQL to delete the item from the einkaufsliste where it matches the itemId and belongs to the logged-in user
    $sql = "DELETE FROM einkaufsliste WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);

    // Bind parameters and execute statement
    if ($stmt->bind_param("ii", $itemId, $userId)) {
        if ($stmt->execute()) {
            // On successful execution, redirect back to the einkaufsliste page or wherever is appropriate
            header("Location: /Views/pages/einkaufsliste.php?status=removed");
            exit;
        } else {
            // Handle error in execution
            echo "Fehler beim Entfernen des Eintrags.";
        }
    } else {
        // Handle error in preparing statement
        echo "Fehler bei der Vorbereitung der Anfrage.";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    // Redirect or error message if not accessed correctly
    header("Location: /Views/pages/einkaufsliste.php?error=invalidaccess");
    exit;
}
?>
