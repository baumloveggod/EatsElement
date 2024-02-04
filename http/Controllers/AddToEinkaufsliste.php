<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    require_once __DIR__ . '/../Utils/db_connect.php';
    require_once __DIR__ . '/../Utils/SessionManager.php';
checkAccess();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (empty($_POST['zutatenName']) || empty($_POST['menge'])) {
            exit('Fehler: Zutatenname oder Menge fehlt.');
        }
        $zutatenName = $_POST['zutatenName'];
        $menge = $_POST['menge'];
        $userId = $_SESSION['id']; // Stellen Sie sicher, dass die Session gestartet wurde

        // Überprüfen, ob die Zutat bereits existiert
        $stmt = $conn->prepare("SELECT id FROM zutaten_namen WHERE name = ?");
        $stmt->bind_param("s", $zutatenName);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            // Zutat existiert nicht, also füge sie ein
            $stmt = $conn->prepare("INSERT INTO zutaten (beschreibung) VALUES ('Neue Zutat')");
            $stmt->execute();
            $zutatId = $conn->insert_id;
            
            // Füge den Namen in zutaten_namen ein
            $stmt = $conn->prepare("INSERT INTO zutaten_namen (name, zutatId) VALUES (?, ?)");
            $stmt->bind_param("si", $zutatenName, $zutatId);
            $stmt->execute();
        } else {
            // Zutat existiert, hole die zutat_id
            $zutat = $result->fetch_assoc();
            $zutatId = $zutat['id'];
        }

        // Füge die Zutat in die Einkaufsliste ein
        $stmt = $conn->prepare("INSERT INTO einkaufsliste (user_id, zutat_id, menge) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $userId, $zutatId, $menge);
        $stmt->execute();
        header("Location: /Views/pages/einkaufsliste.php");
    exit;
    }
?>
