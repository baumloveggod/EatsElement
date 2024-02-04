<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Utils/SessionManager.php'; // Pfad anpassen
require_once 'Utils/db_connect.php'; // Pfad anpassen

// Stellen Sie sicher, dass der Benutzer eingeloggt ist
if (!isUserLoggedIn()) {
    header("Location: login.html");
    exit;
}

$userId = $_SESSION['id'];

// SQL-Abfrage, um Freunde des Benutzers zu finden
$sql = "SELECT u.id, u.username 
        FROM users u
        INNER JOIN freunde f ON u.id = f.user_id_1 OR u.id = f.user_id_2
        WHERE (f.user_id_1 = ? OR f.user_id_2 = ?) AND u.id != ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $userId, $userId, $userId);
$stmt->execute();
$result = $stmt->get_result();

$freunde = [];
while ($row = $result->fetch_assoc()) {
    $freunde[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Freundesliste</title>
    <!-- Fügen Sie hier Ihren CSS-Link ein -->
</head>
<body>
    <header>
        <!-- Navigation und so weiter -->
    </header>
    <main>
        <h2>Meine Freunde</h2>
        <?php if (count($freunde) > 0): ?>
            <ul>
            <?php foreach ($freunde as $freund): ?>
                <li><?= htmlspecialchars($freund['username']) ?></li> <!-- Weitere Informationen anzeigen oder Aktionen ermöglichen -->
            <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Sie haben noch keine Freunde hinzugefügt.</p>
        <?php endif; ?>
    </main>
    <footer>
        <!-- Footer-Inhalt -->
    </footer>
</body>
</html>
