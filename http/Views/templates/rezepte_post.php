<?php
// Stellen Sie sicher, dass die Fehlerberichterstattung für das Debugging aktiviert ist
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../Utils/db_connect.php';


function insert_into_Rezepte() {
    global $conn; // Stelle sicher, dass die Datenbankverbindung verfügbar ist
    
    // Schritt 1: Daten vom Formular empfangen
    $titel = $_POST['titel'] ?? '';
    $untertitel = $_POST['untertitel'] ?? '';
    $zubereitungszeit = $_POST['zubereitungszeit'] ?? '';
    $basis_personenanzahl = $_POST['basis_personenanzahl'] ?? '';
    $bilder = $_FILES['bilder'] ?? null;
    $autor = $_SESSION['user_id'] ?? '1'; // Angenommen, die Benutzer-ID ist in einer Session gespeichert
    $zutaten = $_POST['zutaten'] ?? []; // Erwartet ein Array von Zutaten
    
    // Schritt 2: Bilder speichern und Pfad vorbereiten
    $bildPfade = [];
    if ($bilder) {
        foreach ($bilder['name'] as $key => $name) {
            $zielPfad = "./bilder/" . basename($name);
            if (move_uploaded_file($bilder['tmp_name'][$key], $zielPfad)) {
                $bildPfade[] = $zielPfad;
            }
        }
    }
    $bilderDB = implode(',', $bildPfade); // Umwandlung in einen String für die DB
    
    // Schritt 3: Neuen Rezept-Eintrag erstellen
    $stmt = $conn->prepare("INSERT INTO rezepte (titel, autor, untertitel, bilder, zubereitungszeit, basis_personenanzahl) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssii", $titel, $autor, $untertitel, $bilderDB, $zubereitungszeit, $basis_personenanzahl);
    $stmt->execute();
    $rezeptId = $stmt->insert_id;
    
    // Schritt 4 & 5: Zutaten überprüfen, hinzufügen falls neu und in `rezept_zutaten` einfügen
    foreach ($zutaten as $zutat) {
        // Annahme: $zutat enthält 'name', 'menge', und 'einheit_id'
        $zutatenName = $conn->real_escape_string($zutat['name']);
        $menge = $zutat['menge'];
        $einheitId = $zutat['einheit_id'];
    
        // Überprüfe, ob die Zutat existiert
        $sql = "SELECT id FROM zutaten WHERE name = '$zutatenName' LIMIT 1";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $zutatId = $row['id'];
        } else {
            // Zutat existiert nicht, also füge sie hinzu
            $neueZutatSql = "INSERT INTO zutaten (name, kategorie_id) VALUES ('$zutatenName', 'ID_für_neue_Zutaten')"; // Ersetze 'ID_für_neue_Zutaten' mit der tatsächlichen ID
            if ($conn->query($neueZutatSql) === TRUE) {
                $zutatId = $conn->insert_id;
                // Füge auch einen Eintrag in zutaten_namen hinzu, um den Namen der Zutat zu speichern
                $conn->query("INSERT INTO zutaten_namen (name, zutat_id) VALUES ('$zutatenName', '$zutatId')");
            } else {
                // Fehlerbehandlung, falls das Hinzufügen der Zutat fehlschlägt
                continue; // Überspringe diese Zutat und fahre mit der nächsten fort
            }
        }
    
        // Füge Eintrag in `rezept_zutaten` hinzu
        $stmt = $conn->prepare("INSERT INTO rezept_zutaten (rezept_id, zutat_id, menge, einheit_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iidi", $rezeptId, $zutatId, $menge, $einheitId);
        $stmt->execute();
    }
}

?>

