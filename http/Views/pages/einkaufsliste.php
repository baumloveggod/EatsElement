<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/SessionManager.php';
require_once '../../Utils/db_connect.php';
checkUserAuthentication();

$userId = $_SESSION['userId'];

$heute = date("Y-m-d");

$sql = "SELECT e.id, zn.name, e.menge, e.verbrauchsdatum, zn.oberkategorie
        FROM einkaufsliste e
        JOIN zutaten_namen zn ON e.zutat_id = zn.zutat_id
        WHERE e.user_id = ? AND DATE_ADD(CURDATE(), INTERVAL zn.uebliche_haltbarkeit DAY) >= e.verbrauchsdatum
        ORDER BY zn.oberkategorie ASC, e.verbrauchsdatum ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$einkaufsliste = [];
while ($row = $result->fetch_assoc()) {
    $einkaufsliste[] = [
        'name' => $row['name'],
        'menge' => $row['menge'],
        'verbrauchsdatum' => $row['verbrauchsdatum'],
        'oberkategorie' => $row['oberkategorie'],
        'id' => $row['id']
    ];
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <?php include '../templates/header.php'; ?>
    <title>Einkaufsliste</title>
</head>
<body>
    <header>
        <h1>main navigator page</h1>
        <?php include '../templates/navigation.php'; ?>
    </header>
    <main>
        <h2>Einkaufsliste</h2>
        <table>
            <tr>
                <th>Name</th>
                <th>Menge</th>
                <th>Geplantes Datum</th>
                <th>Aktion</th>
            </tr>
            <?php foreach ($einkaufsliste as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= htmlspecialchars($item['menge']) ?></td>
                <td>
                    <?php if ($item['verbrauchsdatum']): ?>
                        <a href="rezept_detail.php?datum=<?= urlencode($item['verbrauchsdatum']) ?>">
                            <?= htmlspecialchars($item['verbrauchsdatum']) ?>
                        </a>
                    <?php else: ?>
                        <form method="post" action="Controllers/RemoveFromEinkaufsliste.php">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit">Entfernen</button>
                        </form>
                    <?php endif; ?>
                </td>
                <td>
                    <form method="post" action="/Controllers/MoveToPantry.php">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <button type="submit">In Vorratsschrank verschieben</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h3>Neuen Eintrag hinzufügen</h3>
        <form method="post" action="/Controllers/AddToEinkaufsliste.php">
            <input type="text" name="zutatenName" placeholder="Zutatenname" required>
            <input type="text" name="menge" placeholder="Menge" required>
            <button type="submit">Zutat hinzufügen</button>
        </form>

    </main>
    <footer>
        <p>&copy; 2024 Transforamtions-Design</p>
    </footer>
</body>
</html>
