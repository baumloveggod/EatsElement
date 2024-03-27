<?php
// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include necessary files
require_once __DIR__ . '/../Utils/db_connect.php';
require_once __DIR__ . '/../Utils/SessionManager.php';

// Ensure user authentication
checkUserAuthentication();

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $zutatenName = isset($_GET['zutatenName']) ? $_GET['zutatenName'] : null;
    $einheiten = [];

    // Execute only if zutatenName is provided
    if ($zutatenName) {
        // Prepare and execute query to fetch zutat info
        $stmt = $conn->prepare("SELECT z.einheit_id, e.name, e.basis_einheit_id 
                                FROM zutaten_namen zn 
                                JOIN zutaten z ON zn.zutat_id = z.id 
                                JOIN einheiten e ON z.einheit_id = e.id 
                                WHERE zn.name = ?");
        $stmt->bind_param("s", $zutatenName);
        $stmt->execute();
        $zutatInfo = $stmt->get_result()->fetch_assoc();

        if ($zutatInfo) {
            // Handle known zutat cases
            $whereClause = $zutatInfo['basis_einheit_id'] ? "WHERE id = ? OR basis_einheit_id = ?" : "WHERE basis_einheit_id = ?";
            $sql = "SELECT id, name FROM einheiten $whereClause";
            $stmt = $conn->prepare($sql);

            if ($zutatInfo['basis_einheit_id']) {
                $stmt->bind_param("ii", $zutatInfo['einheit_id'], $zutatInfo['basis_einheit_id']);
            } else {
                $stmt->bind_param("i", $zutatInfo['einheit_id']);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $einheiten[] = $row;
            }

            if (!$zutatInfo['basis_einheit_id'] && in_array($zutatInfo['einheit_id'], [0, 1])) {
                // Add specific handling for einheit_id 0 and 1 if needed
                $einheiten[] = ['id' => $zutatInfo['einheit_id'], 'name' => $zutatInfo['name']];
            }
        } else {
            // Zutat is unknown: Load all einheiten
            $einheiten = loadAllEinheiten($conn);
        }
    } else {
        // Zutat is not provided: Load all einheiten
        $einheiten = loadAllEinheiten($conn);
    }

    echo json_encode($einheiten);
}

// Function to load all einheiten from the database
function loadAllEinheiten($conn) {
    $einheiten = [];
    $result = $conn->query("SELECT id, name FROM einheiten");
    while ($row = $result->fetch_assoc()) {
        $einheiten[] = $row;
    }
    return $einheiten;
}
?>
