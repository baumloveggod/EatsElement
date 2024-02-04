<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/CheckSessionManager.php';
require_once '../../Utils/db_connect.php';

$userId = $_SESSION['id'];

$sql = "SELECT vs.id, zn.name, vs.menge, vs.verbrauchsdatum 
        FROM vorratsschrank vs
        JOIN zutaten_namen zn ON vs.zutat_id = zn.zutat_id
        WHERE vs.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Vorratsschrank</title>
    <link rel="stylesheet" href="../../css/styles.css">
</head>
<body>
    <header>
        <!-- Navigation usw. -->
    </header>
    <main>
        <h2>Vorratsschrank</h2>
        <table>
            <tr>
                <th>Zutat</th>
                <th>Menge</th>
                <th>geplantes Verbauchs datum</th>
                <th>Aktion</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['menge']) ?></td>
                <td><?= $row['verbrauchsdatum'] ? htmlspecialchars($row['verbrauchsdatum']) : 'N/A' ?></td>
                <td>
                    <?php if (!$row['verbrauchsdatum']): ?>
                        <form method="post" action="Controllers/PlanIntelligently.php">
                            <input type="hidden" name="zutat_id" value="<?= $row['zutat_id'] ?>">
                            <button type="submit">Intelligent einplanen</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </main>
    <footer>
        <!-- Footer-Inhalt -->
    </footer>
</body>
</html>