<?php
// Fehlerberichterstattung einschalten
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verbindung zur Datenbank herstellen
require_once './Utils/db_connect.php';
require_once __DIR__ . '/../config/config.php';

$apiToken = apiToken; // Angenommen, Ihre Konfigurationsdatei definiert den API-Token

// Daten aus dem JSON-Body der POST-Anfrage lesen
$content = file_get_contents("php://input");
$request = json_decode($content, true);

$providedToken = isset($request['token']) ? $request['token'] : '';

// Authentifizierung: Vergleich des bereitgestellten Tokens mit dem festgelegten API-Token
if($providedToken !== $apiToken) {
    // Token ist ungültig oder nicht vorhanden
    http_response_code(401); // Nicht autorisiert
    echo json_encode(array("message" => "Invalid or missing API token."));
    exit;
}

if (isset($request['sql'])){
    $sql = $request['sql'];
    // Versuch, das SQL-Statement auszuführen
    if($result = $conn->query($sql)) {
        if(is_bool($result)) {
            // Für Anweisungen, die kein Resultset zurückgeben (z.B. INSERT, UPDATE, DELETE)
            echo json_encode(array("success" => true, "affected_rows" => $conn->affected_rows));
        } else {
            // Für SELECT-Anweisungen
            $data = array();
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode($data);
        }
    } else {
        // SQL-Fehler
        http_response_code(400); // Bad Request
        echo json_encode(array("error" => $conn->error));
    }
} else {
    // Kein SQL-Statement empfangen
    http_response_code(400); // Bad Request
    echo json_encode(array("message" => "No SQL statement provided."));
}
?>
