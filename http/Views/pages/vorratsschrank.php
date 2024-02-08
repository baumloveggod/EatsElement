<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

$userId = $_SESSION['userId'];

$sql = "SELECT vs.id, zn.name, vs.menge, vs.verbrauchsdatum 
        FROM vorratsschrank vs
        JOIN zutaten_namen zn ON vs.zutat_id = zn.zutat_id
        WHERE vs.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$Voratschrank = [];
while ($row = $result->fetch_assoc()) {
    $Voratschrank[] = [
        'name' => $row['name'],
        'menge' => $row['menge'],
        'verbrauchsdatum' => $row['verbrauchsdatum'],
        'id' => $row['id']
    ];
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <?php include '../templates/header.php'; ?>
    <title>Vorratsschrank</title>
</head>
<body>
    <?php include '../templates/navigation.php'; ?>
    <main>
        <h2>Vorratsschrank</h2>
        <table>
            <tr>
                <th>Zutat</th>
                <th>Menge</th>
                <th>geplantes Verbauchs datum</th>
                <th>Aktion</th>
            </tr>
            <?php foreach ( $Voratschrank as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= htmlspecialchars($item['menge']) ?></td>
                <td>
                    <?php if ($item['verbrauchsdatum']): ?>
                        <a href="rezept_detail.php?datum=<?= urlencode($item['verbrauchsdatum']) ?>">
                            <?= htmlspecialchars($item['verbrauchsdatum']) ?>
                        </a>
                    <?php else: ?>
                        <form method="post" action="Controllers/Inteligent_verplanen.php">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit">Inteligent verplanen</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </main>
    <footer>
        <!-- Footer-Inhalt -->
    </footer>
</body>
</html>