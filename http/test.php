<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Utils/db_connect.php';

// Schritt 1: Überprüfen, ob Rezepte vorhanden sind
$sql = "SELECT COUNT(*) FROM rezepte";
$result = $conn->query($sql);
$row = $result->fetch_row();

if ($row[0] == 0) {
    // Schritt 2: Füge Kategorien hinzu, falls noch nicht vorhanden (Optional, falls nicht bereits manuell hinzugefügt)
    $kategorienSql = "INSERT INTO kategorien (name, sortierreihenfolge) VALUES
    ('unbekannte zutat', 11),
    ('Obst & Gemüse', 1),
    ('Bäckerei', 2),
    ('Fleisch & Fisch', 3),
    ('Milchprodukte & Eier', 4),
    ('Tiefkühlkost', 5),
    ('Konserven & Trockenwaren', 6),
    ('Getränke', 7),
    ('Süßwaren & Snacks', 8),
    ('Gesundheit & Schönheit', 9),
    ('Haushaltswaren', 10)
    ON DUPLICATE KEY UPDATE name=VALUES(name);"; // Verhindert Duplikate, wenn Skript erneut ausgeführt wird.
    $conn->query($kategorienSql);

    // Verbindung zur Datenbank herstellen

    $zutatenListe = [[
        'name' => 'Äpfel',
        'kategorie' => 'Obst & Gemüse',
        'haltbarkeit' => 30,
        'naehrstoffe' => [
            'kalorien' => 52,
            'proteine' => 0.26,
            'fette' => 0.17,
            'kohlenhydrate' => 13.81,
            'ballaststoffe' => 2.4,
            'zucker' => 10.39
        ]
    ],
    [
        'name' => 'Vollkornbrot',
        'kategorie' => 'Bäckerei',
        'haltbarkeit' => 7,
        'naehrstoffe' => [
            'kalorien' => 247,
            'proteine' => 13,
            'fette' => 3.4,
            'kohlenhydrate' => 41,
            'ballaststoffe' => 7,
            'zucker' => 6
        ]
    ],
    [
        'name' => 'Hühnchenbrust',
        'kategorie' => 'Fleisch & Fisch',
        'haltbarkeit' => 10,
        'naehrstoffe' => [
            'kalorien' => 165,
            'proteine' => 31,
            'fette' => 3.6,
            'kohlenhydrate' => 0,
            'ballaststoffe' => 0,
            'zucker' => 0
        ]
    ],
    [
        'name' => 'Joghurt',
        'kategorie' => 'Milchprodukte & Eier',
        'haltbarkeit' => 15,
        'naehrstoffe' => [
            'kalorien' => 59,
            'proteine' => 10,
            'fette' => 0.4,
            'kohlenhydrate' => 3.6,
            'ballaststoffe' => 0,
            'zucker' => 3.6
        ]
    ],
    [
        'name' => 'Tikka Masala Paste',
        'kategorie' => 'Konserven & Trockenwaren',
        'haltbarkeit' => 180,
        'naehrstoffe' => [
            'kalorien' => 150,
            'proteine' => 2,
            'fette' => 8,
            'kohlenhydrate' => 18,
            'ballaststoffe' => 2,
            'zucker' => 12
        ]
    ],[
        'name' => 'Tomaten',
        'kategorie' => 'Obst & Gemüse',
        'haltbarkeit' => 10,
        'naehrstoffe' => [
            'kalorien' => 18,
            'proteine' => 0.88,
            'fette' => 0.2,
            'kohlenhydrate' => 3.89,
            'ballaststoffe' => 1.2,
            'zucker' => 2.63
        ]
    ],
    [
        'name' => 'Sahne',
        'kategorie' => 'Milchprodukte & Eier',
        'haltbarkeit' => 10,
        'naehrstoffe' => [
            'kalorien' => 342,
            'proteine' => 2.9,
            'fette' => 36,
            'kohlenhydrate' => 2.7,
            'ballaststoffe' => 0,
            'zucker' => 2.7
        ]
    ],
    [
        'name' => 'Ei',
        'kategorie' => 'Milchprodukte & Eier',
        'haltbarkeit' => 21,
        'naehrstoffe' => [
            'kalorien' => 155,
            'proteine' => 13,
            'fette' => 11,
            'kohlenhydrate' => 1.1,
            'ballaststoffe' => 0,
            'zucker' => 1.1
        ]
    ],
    [
        'name' => 'Speck',
        'kategorie' => 'Fleisch & Fisch',
        'haltbarkeit' => 14,
        'naehrstoffe' => [
            'kalorien' => 541,
            'proteine' => 37,
            'fette' => 42,
            'kohlenhydrate' => 1.4,
            'ballaststoffe' => 0,
            'zucker' => 0
        ]
    ],
    [
        'name' => 'Spaghetti',
        'kategorie' => 'Konserven & Trockenwaren',
        'haltbarkeit' => 365,
        'naehrstoffe' => [
            'kalorien' => 158,
            'proteine' => 5.8,
            'fette' => 0.9,
            'kohlenhydrate' => 31,
            'ballaststoffe' => 1.8,
            'zucker' => 0.8
        ]
    ],
    [
        'name' => 'Parmesan',
        'kategorie' => 'Milchprodukte & Eier',
        'haltbarkeit' => 60,
        'naehrstoffe' => [
            'kalorien' => 431,
            'proteine' => 38,
            'fette' => 29,
            'kohlenhydrate' => 4.1,
            'ballaststoffe' => 0,
            'zucker' => 0.9
        ]
    ],
    [
        'name' => 'Kokosmilch',
        'kategorie' => 'Konserven & Trockenwaren',
        'haltbarkeit' => 180,
        'naehrstoffe' => [
            'kalorien' => 230,
            'proteine' => 2.3,
            'fette' => 24,
            'kohlenhydrate' => 6,
            'ballaststoffe' => 2.2,
            'zucker' => 3.3
        ]
    ],
    [
        'name' => 'Currypaste',
        'kategorie' => 'Konserven & Trockenwaren',
        'haltbarkeit' => 180,
        'naehrstoffe' => [
            'kalorien' => 325,
            'proteine' => 3.8,
            'fette' => 17,
            'kohlenhydrate' => 40,
            'ballaststoffe' => 9.8,
            'zucker' => 29
        ]
    ],
        ['name' => 'Brokkoli', 'kategorie' => 'Obst & Gemüse', 'haltbarkeit' => 15],
        ['name' => 'Karotten', 'kategorie' => 'Obst & Gemüse', 'haltbarkeit' => 30],
        ['name' => 'Römersalat', 'kategorie' => 'Obst & Gemüse', 'haltbarkeit' => 7],
        ['name' => 'Croutons', 'kategorie' => 'Bäckerei', 'haltbarkeit' => 60],
        ['name' => 'Caesar-Dressing', 'kategorie' => 'Konserven & Trockenwaren', 'haltbarkeit' => 90],
        ['name' => 'Linsen', 'kategorie' => 'Konserven & Trockenwaren', 'haltbarkeit' => 365],
        ['name' => 'Zwiebel', 'kategorie' => 'Obst & Gemüse', 'haltbarkeit' => 30],
        ['name' => 'Sellerie', 'kategorie' => 'Obst & Gemüse', 'haltbarkeit' => 21],
        ['name' => 'Gemüsebrühe', 'kategorie' => 'Konserven & Trockenwaren', 'haltbarkeit' => 365],
        ['name' => 'Tofu', 'kategorie' => 'Gesundheit & Schönheit', 'haltbarkeit' => 60], // Tofu könnte auch unter "Kühlgut" fallen, je nach Datenbankdesign
        ['name' => 'Tomatensoße', 'kategorie' => 'Konserven & Trockenwaren', 'haltbarkeit' => 365],
        ['name' => 'Knoblauch', 'kategorie' => 'Obst & Gemüse', 'haltbarkeit' => 60],
        ['name' => 'Pasta', 'kategorie' => 'Konserven & Trockenwaren', 'haltbarkeit' => 365],
        ['name' => 'Zucchini', 'kategorie' => 'Obst & Gemüse', 'haltbarkeit' => 30],
        ['name' => 'Aubergine', 'kategorie' => 'Obst & Gemüse', 'haltbarkeit' => 30],
        ['name' => 'Paprika', 'kategorie' => 'Obst & Gemüse', 'haltbarkeit' => 30],
        ['name' => 'Thymian', 'kategorie' => 'Konserven & Trockenwaren', 'haltbarkeit' => 180],
        ['name' => 'Mehl', 'kategorie' => 'Bäckerei', 'haltbarkeit' => 365],
        ['name' => 'Milch', 'kategorie' => 'Milchprodukte & Eier', 'haltbarkeit' => 10],
        ['name' => 'Butter', 'kategorie' => 'Milchprodukte & Eier', 'haltbarkeit' => 30],
        ['name' => 'Salz', 'kategorie' => 'Konserven & Trockenwaren', 'haltbarkeit' => 1095] // Salz ist praktisch unbegrenzt haltbar, aber hier als 3 Jahre angegeben
    ];
    
    
    foreach ($zutatenListe as $zutat) {
        // Ermittle die kategorie_id basierend auf dem Kategorienamen
        $kategorieStmt = $conn->prepare("SELECT id FROM kategorien WHERE name = ?");
        $kategorieStmt->bind_param("s", $zutat['kategorie']);
        $kategorieStmt->execute();
        $result = $kategorieStmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $kategorie_id = $row['id'];

            $insertStmtZutaten = $conn->prepare("INSERT INTO zutaten (kategorie_id, uebliche_haltbarkeit) VALUES (?, ?)");
            $insertStmtZutaten->bind_param("ii", $kategorie_id, $zutat['haltbarkeit']);
            $insertStmtZutaten->execute();
            $zutatId = $conn->insert_id; // Holt die ID der gerade eingefügten Zutat

            $insertStmtNamen = $conn->prepare("INSERT INTO zutaten_namen (name, zutat_id) VALUES (?, ?)");
            $insertStmtNamen->bind_param("si", $zutat['name'], $zutatId);
            $insertStmtNamen->execute();

        }
        // Neuer Code zum Einfügen der Nährstoffdaten
        $insertStmtNaehrstoffe = $conn->prepare("INSERT INTO naehrstoffe (zutat_id, kalorien, proteine, fette, kohlenhydrate, ballaststoffe, zucker) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertStmtNaehrstoffe->bind_param("idddddd", $zutatId, $zutat['naehrstoffe']['kalorien'], $zutat['naehrstoffe']['proteine'], $zutat['naehrstoffe']['fette'], $zutat['naehrstoffe']['kohlenhydrate'], $zutat['naehrstoffe']['ballaststoffe'], $zutat['naehrstoffe']['zucker']);
        $insertStmtNaehrstoffe->execute();
    }


    // Schritt 2.1: Keine Rezepte vorhanden, füge Beispielrezepte ein
    $beispielRezepte = [
        [
        "titel" => "Chicken Tikka Masala",
        "beschreibung" => "Beliebtes indisches Gericht mit cremiger Tomatensoße.",
        "zubereitungszeit" => 40,
        "zutaten" => [
            ["name" => "Hühnerbrust","menge" =>  "500g"],
            ["name" => "Joghurt", "menge" =>  "100ml"],
            ["name" => "Tikka Masala Paste", "menge" =>  "3 EL"],
            ["name" => "Tomaten", "menge" =>  "400g"],
            ["name" => "Sahne", "menge" =>  "100ml"]
        ]
        ],[
            "titel" => "Spaghetti Carbonara",
            "beschreibung" => "Klassisches italienisches Pasta-Gericht mit Speck und Sahnesoße.",
            "zubereitungszeit" => 20,
            "zutaten" => [
                ["name" => "Ei", "menge" =>  "2 Stück"],
                ["name" => "Speck", "menge" =>  "100g"],
                ["name" => "Spaghetti","menge" =>   "200g"],
                ["name" => "Parmesan","menge" =>   "50g"]
            ]
        ],
        [
            "titel" => "Gemüsecurry",
            "beschreibung" => "Würziges und gesundes Gemüsecurry mit Reis.",
            "zubereitungszeit" => 30,
            "zutaten" => [
                ["name" => "Kokosmilch","menge" =>   "250ml"],
                ["name" => "Currypaste","menge" =>   "2 EL"],
                ["name" => "Brokkoli","menge" =>   "1 Kopf"],
                ["name" => "Karotten","menge" =>   "2 Stück"]
            ]
        ],
        [
            "titel" => "Caesar Salad",
            "beschreibung" => "Klassischer Caesar Salad mit knusprigen Croutons und Parmesan.",
            "zubereitungszeit" => 15,
            "zutaten" => [
                ["name" => "Römersalat","menge" =>   "1 Kopf"],
                ["name" => "Croutons","menge" =>   "100g"],
                ["name" => "Parmesan","menge" =>   "30g"],
                ["name" => "Caesar-Dressing","menge" =>   "50ml"]
            ]
        ],[
        
        "titel" => "Linsensuppe",
        "beschreibung" => "Herzhafte Linsensuppe, perfekt für kalte Tage.",
        "zubereitungszeit" => 45,
        "zutaten" => [
            ["name" => "Linsen","menge" =>   "200g"],
            ["name" => "Zwiebel","menge" =>   "1 Stück"],
            ["name" => "Karotten","menge" =>   "2 Stück"],
            ["name" => "Sellerie","menge" =>   "1 Stange"],
            ["name" => "Gemüsebrühe","menge" =>   "1 Liter"]
        ]
    ],[
        "titel" => "Vegane Bolognese",
        "beschreibung" => "Pflanzenbasierte Version des italienischen Klassikers.",
        "zubereitungszeit" => 30,
        "zutaten" => [
            ["name" => "Tofu","menge" =>   "200g"],
            ["name" => "Tomatensoße","menge" =>   "500ml"],
            ["name" => "Zwiebel","menge" =>   "1 Stück"],
            ["name" => "Knoblauch","menge" =>   "2 Zehen"],
            ["name" => "Pasta","menge" =>   "300g"]
        ]
    ],[
        "titel" => "Ratatouille",
        "beschreibung" => "Französisches Gemüsegericht, bunt und aromatisch.",
        "zubereitungszeit" => 60,
        "zutaten" => [
            ["name" => "Zucchini","menge" =>   "2 Stück"],
            ["name" => "Aubergine","menge" =>   "1 Stück"],
            ["name" => "Paprika","menge" =>   "2 Stück"],
            ["name" => "Tomaten","menge" =>   "3 Stück"],
            ["name" => "Thymian","menge" =>   "1 Zweig"]
        ]
    ],[
        "titel" => "Pfannkuchen",
        "beschreibung" => "Einfache und leckere Pfannkuchen, süß oder herzhaft.",
        "zubereitungszeit" => 20,
        "zutaten" => ["name" => 
            ["name" => "Mehl","menge" =>   "250g"],
            ["name" => "Eier","menge" =>   "2 Stück"],
            ["name" => "Milch","menge" =>   "500ml"],
            ["name" => "Butter","menge" =>   "Zum Braten"],
            ["name" => "Salz","menge" =>   "1 Prise"]
        ]
    ]
    ];

    $stmtRezept = $conn->prepare("INSERT INTO rezepte (titel, beschreibung, zubereitungszeit) VALUES (?, ?, ?)");
    $stmtCheckZutat = $conn->prepare("SELECT zutat_id FROM zutaten_namen WHERE name = ?");
    $stmtZutaten = $conn->prepare("INSERT INTO rezept_zutaten (rezept_id, zutat_id, menge, einheit_id) VALUES (?, ?, ?, ?)");
    $stmtCheckExisting = $conn->prepare("SELECT COUNT(*) FROM rezept_zutaten WHERE rezept_id = ? AND zutat_id = ?");
    
    foreach ($beispielRezepte as $rezept) {
        $stmtRezept->bind_param("ssi", $rezept['titel'], $rezept['beschreibung'], $rezept['zubereitungszeit']);
        $stmtRezept->execute();
        $rezeptId = $stmtRezept->insert_id;
    
        foreach ($rezept['zutaten'] as $zutat) {
            $stmtCheckZutat->bind_param("s", $zutat['name']);
            $stmtCheckZutat->execute();
            $result = $stmtCheckZutat->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $zutatId = $row['zutat_id'];
                
                echo $zutat['menge'] . "  : ";
                $eingabe = $zutat['menge'];
                // Überprüfen, ob die Eingabe nur aus Zahlen besteht
                if (is_numeric($eingabe)) {
                    $quantity  = $eingabe;
                    $einheitName  = "";
                } elseif (is_string($eingabe)) { // Überprüfen, ob die Eingabe nur aus Buchstaben besteht
                    // Hier könnte zusätzlich geprüft werden, ob es sich tatsächlich nur um Wörter handelt,
                    // z.B. durch einen regulären Ausdruck, der sicherstellt, dass keine Zahlen enthalten sind.
                    if (preg_match('/^[a-zA-ZäöüÄÖÜß\s]+$/', $eingabe)) {
                        $quantity  = -1;
                        $einheitName  = $eingabe;
                    } else {
                        // Falls die Eingabe sowohl Buchstaben als auch Zahlen enthält
                        echo "Die Eingabe enthält sowohl Buchstaben als auch Zahlen.\n";
                        preg_match('/^(\d+)\s*(.*)$/', $zutat['menge'], $matches);
                        $quantity = (int)$matches[1];
                        $einheitName = trim($matches[2]);// Oder eine Fehlerbehandlung nach Bedarf
                    }
                } else {
                    // Falls die Eingabe weder reine Zahlen noch reine Wörter enthält
                    echo "Ungültige Eingabe.\n";
                    $quantity  = -1;
                    $einheitName  = ""; // Oder eine Fehlerbehandlung nach Bedarf
                }
                
                echo $quantity . " + " . $einheitName. "</br>";
                // SQL-Abfrage, um die einheit_id zu ermitteln
                $stmtEinheit = $conn->prepare("SELECT id FROM einheiten WHERE name = ?");
                $stmtEinheit->bind_param("s", $einheitName);
                $stmtEinheit->execute();
                $resultEinheit = $stmtEinheit->get_result();

                if ($resultEinheit->num_rows > 0) {
                    // Einheit existiert, benutze existierende einheit_id
                    $rowEinheit = $resultEinheit->fetch_assoc();
                    $einheitId = $rowEinheit['id'];
                } else {
                    // Optional: Einheit existiert nicht, füge sie ein und benutze neue einheit_id
                    // Dies hängt von deiner Anforderung ab, ob du neue Einheiten automatisch hinzufügen möchtest
                    $stmtEinheitInsert = $conn->prepare("INSERT INTO einheiten (name, umrechnungsfaktor_zu_basis) VALUES (?, ?)");
                    $umrechnungsfaktorZuBasis = 1; // Standardwert oder berechne basierend auf Einheit
                    $stmtEinheitInsert->bind_param("sd", $einheitName, $umrechnungsfaktorZuBasis);
                    $stmtEinheitInsert->execute();
                    $einheitId = $stmtEinheitInsert->insert_id;
                }
                $stmtCheckExisting->bind_param("ii", $rezeptId, $zutatId);
                $stmtCheckExisting->execute();
                $result = $stmtCheckExisting->get_result(); // Correct variable for fetching result
                $row = $result->fetch_array(); // Fetching the row directly as an array
                if ($row[0] == 0) { // Assuming COUNT(*) returns 0 if not exists
                    $stmtZutaten->bind_param("iiii", $rezeptId, $zutatId, $quantity, $einheitId);
                    $stmtZutaten->execute();
                }
            }
            // It's good practice to free each result set as soon as you're done with it
            if(isset($result) && $result instanceof mysqli_result) {
                $result->free();
                unset($result); // Entfernen Sie die Referenz, um doppelte Freigaben zu verhindern
            }
            
        }
        
    }
} else {
    echo "Es sind bereits Rezepte in der Datenbank vorhanden.";
}

$stmtRezept->close();   
$stmtCheckZutat->close();
$stmtZutaten->close();
$conn->close();
?>
