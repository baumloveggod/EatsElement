<?php
require_once '.-/../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['anzahlPersonen'], $_POST['datum'])) {
    $anzahlPersonen = $_POST['anzahlPersonen'];
    $datum = $_POST['datum'];
    $userId = $_SESSION['userId'];

    $sql = "UPDATE essenplan SET anzahl_personen = ? WHERE user_id = ? AND datum = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt->bind_param("iis", $anzahlPersonen, $userId, $datum) && $stmt->execute()) {
        // Erfolg
        header("Location: rezept_detail.php?datum=" . urlencode($datum));
    } else {
        // Fehler
        echo "Fehler beim Aktualisieren der Anzahl der Personen.";
    }
    $stmt->close();
    $conn->close();
}
?>
