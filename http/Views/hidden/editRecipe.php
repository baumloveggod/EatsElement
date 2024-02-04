<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/CheckSessionManager.php';
require_once '../../Utils/db_connect.php';

$userId = $_SESSION['id'];
$recipeId = isset($_GET['recipeId']) ? intval($_GET['recipeId']) : null;

// Wenn recipeId vorhanden ist, überprüfe, ob sie mit der des Benutzers übereinstimmt
if ($recipeId !== null) {
    $stmt = $conn->prepare("SELECT current_editing_recipe_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($recipeId != $user['current_editing_recipe_id']) {
        deleteRecipe($recipeId, $conn);
    
        // Kopiere das neue Rezept und erstelle eine neue ID
        $newRecipeId = copyRecipe($recipeId, $conn);
    
        // Aktualisiere die current_editing_recipe_id des Benutzers
        $updateStmt = $conn->prepare("UPDATE users SET current_editing_recipe_id = ? WHERE id = ?");
        $updateStmt->bind_param("ii", $newRecipeId, $userId);
        $updateStmt->execute();
    
        // Setze die neue recipeId für die weitere Verarbeitung
        $recipeId = $newRecipeId;
    }
    $zutatenStmt = $conn->prepare("
            SELECT z.name, rz.menge 
            FROM rezept_zutaten AS rz 
            JOIN zutaten AS z ON rz.zutat_id = z.id 
            WHERE rz.rezept_id = ?
        ");
    $zutatenStmt->bind_param("i", $recipeId);
    $zutatenStmt->execute();
        
    $zutatenResult = $zutatenStmt->get_result();
        
    while ($row = $zutatenResult->fetch_assoc()) {
        $zutaten[] = $row; // Füge jede Zutat zum Array hinzu
    }
}
        
// Funktion zum Kopieren eines Rezepts
function copyRecipe($recipeId, $conn) {
    // Schritt 1: Auslesen des originalen Rezepts
    $stmt = $conn->prepare("SELECT titel, beschreibung, zubereitungszeit FROM rezepte WHERE id = ?");
    $stmt->bind_param("i", $recipeId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        // Schritt 2: Kopieren des Rezepts
        $insertStmt = $conn->prepare("INSERT INTO rezepte (titel, beschreibung, zubereitungszeit) VALUES (?, ?, ?)");
        $insertStmt->bind_param("ssi", $row['titel'], $row['beschreibung'], $row['zubereitungszeit']);
        $insertStmt->execute();

        // Schritt 3: Rückgabe der neuen recipeId
        $newRecipeId = $conn->insert_id;
        return $newRecipeId;
    } else {
        // Fehlerbehandlung, falls das Originalrezept nicht gefunden wurde
        echo "Originalrezept nicht gefunden.";
        exit;
    }
}

function deleteRecipe($recipeId, $conn) {
    // Lösche das Rezept aus der rezepte-Tabelle
    $deleteStmt = $conn->prepare("DELETE FROM rezepte WHERE id = ?");
    $deleteStmt->bind_param("i", $recipeId);

    if ($deleteStmt->execute()) {
        echo "Rezept erfolgreich gelöscht.";
    } else {
        echo "Fehler beim Löschen des Rezepts: " . $conn->error;
    }

    // Zusätzliche Schritte, um alle verknüpften Daten zu bereinigen
    // Beispiel: Löschen von Einträgen aus der rezept_zutaten-Tabelle
    $deleteIngredientsStmt = $conn->prepare("DELETE FROM rezept_zutaten WHERE rezept_id = ?");
    $deleteIngredientsStmt->bind_param("i", $recipeId);
    $deleteIngredientsStmt->execute();

    // Hier können weitere Bereinigungen für andere verknüpfte Tabellen hinzugefügt werden
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Rezept Bearbeiten</title>
    <link rel="stylesheet" href="../../css/styles.css">
</head>
<body>
    <header>
        <?php include '../templates/navigation.php'; ?>
    </header>
    <main>
        <h2>Rezept Bearbeiten</h2>
        <form action="../../Controllers/UpdateRecipe.php" method="post">
            <input type="hidden" name="recipeId" value="<?= $recipeId ?>">
            <label for="titel">Titel:</label>
            <input type="text" id="titel" name="titel" value="<?= htmlspecialchars($rezept['titel']) ?>" required>

            <label for="beschreibung">Beschreibung:</label>
            <textarea id="beschreibung" name="beschreibung" required><?= htmlspecialchars($rezept['beschreibung']) ?></textarea>

            <label for="zeit">Zubereitungszeit (in Minuten):</label>
            <input type="number" id="zeit" name="zeit" value="<?= $rezept['zubereitungszeit'] ?>" required>

            <h3>Zutaten:</h3>
            <table id="zutatenTable">
                <tr>
                    <th>Name</th>
                    <th>Menge</th>
                    <th>Aktion</th>
                </tr>
                <?php 
                $zutaten = []; // Initialize as an empty array
                foreach ($zutaten as $zutat): 
                ?>
                <tr>
                    <td><input type="text" name="zutatenName[]" value="<?= htmlspecialchars($zutat['name']) ?>"></td>
                    <td><input type="text" name="zutatenMenge[]" value="<?= htmlspecialchars($zutat['menge']) ?>"></td>
                    <td><button type="button" class="removeRow">Entfernen</button></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td><input type="text" name="zutatenName[]"></td>
                    <td><input type="text" name="zutatenMenge[]"></td>
                    <td><button type="button" class="removeRow">Entfernen</button></td>
                </tr>
            </table>
            <button type="button" id="addRow">Zutat hinzufügen</button>

            <button type="submit" name="save">Speichern</button>
            <button type="button" name="discard" onclick="window.location.href='heutiges-gericht.php'">Verwerfen</button>
        </form>
    </main>
    <footer>
        <p>&copy; 2024 Transforamtions-Design</p>
    </footer>

    <script>
        document.getElementById('addRow').addEventListener('click', function() {
            var table = document.getElementById('zutatenTable');
            var newRow = table.insertRow();
            var cell1 = newRow.insertCell(0);
            var cell2 = newRow.insertCell(1);
            var cell3 = newRow.insertCell(2);
            cell1.innerHTML = '<input type="text" name="zutatenName[]">';
            cell2.innerHTML = '<input type="text" name="zutatenMenge[]">';
            cell3.innerHTML = '<button type="button" class="removeRow">Entfernen</button>';
        });

        document.getElementById('zutatenTable').addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('removeRow')) {
                var row = e.target.closest('tr');
                row.parentNode.removeChild(row);
            }
        });
    </script>
</body>
</html>
