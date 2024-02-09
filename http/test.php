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

    $zutatenListe = [
        ['name' => 'Äpfel', 'kategorie' => 'Obst & Gemüse', 'haltbarkeit' => 30],
        ['name' => 'Vollkornbrot', 'kategorie' => 'Bäckerei', 'haltbarkeit' => 7],
        ['name' => 'Hähnchenbrust', 'kategorie' => 'Fleisch & Fisch', 'haltbarkeit' => 10],
        ['name' => 'Joghurt', 'kategorie' => 'Milchprodukte & Eier', 'haltbarkeit' => 15],
        ['name' => 'Tikka Masala Paste', 'kategorie' => 'Konserven & Trockenwaren', 'haltbarkeit' => 180],
        ['name' => 'Tomaten', 'kategorie' => 'Obst & Gemüse', 'haltbarkeit' => 10],
        ['name' => 'Sahne', 'kategorie' => 'Milchprodukte & Eier', 'haltbarkeit' => 10],
        ['name' => 'Ei', 'kategorie' => 'Milchprodukte & Eier', 'haltbarkeit' => 21],
        ['name' => 'Speck', 'kategorie' => 'Fleisch & Fisch', 'haltbarkeit' => 14],
        ['name' => 'Spaghetti', 'kategorie' => 'Konserven & Trockenwaren', 'haltbarkeit' => 365],
        ['name' => 'Parmesan', 'kategorie' => 'Milchprodukte & Eier', 'haltbarkeit' => 60],
        ['name' => 'Kokosmilch', 'kategorie' => 'Konserven & Trockenwaren', 'haltbarkeit' => 180],
        ['name' => 'Currypaste', 'kategorie' => 'Konserven & Trockenwaren', 'haltbarkeit' => 180],
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
            $kategorieId = $row['id'];

            // Füge die Zutat in die Datenbank ein
            $insertStmt = $conn->prepare("INSERT INTO zutaten (name, kategorie_id, haltbarkeit_tage) VALUES (?, ?, ?)");
            $insertStmt->bind_param("sii", $zutat['name'], $kategorieId, $zutat['haltbarkeit']);
            $insertStmt->execute();
        }
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
    $stmtZutaten = $conn->prepare("INSERT INTO rezept_zutaten (rezept_id, zutat_id, menge) VALUES (?, ?, ?)");
    foreach ($beispielRezepte as $rezept) {
        $stmtRezept->bind_param("ssi", $rezept['titel'], $rezept['beschreibung'], $rezept['zubereitungszeit']);
        $stmtRezept->execute();
        $rezeptId = $stmtRezept->insert_id;
    
        foreach ($rezept['zutaten'] as $zutat) {
            // Debug statement
            echo "Debug: Ingredient Name - " . $zutat['name'] . "<br>";
            $stmtCheckZutat->bind_param("s", $zutat['name']);
            $stmtCheckZutat->execute();
            $result = $stmtCheckZutat->get_result();
            if ($result->num_rows > 0) {
                // Zutat exists, get its id
                $row = $result->fetch_assoc();
                $zutatId = $row['zutat_id'];
            } else {
                // Zutat does not exist, insert and get id
                
                $stmtInsertZutat = $conn->prepare("INSERT INTO zutaten () VALUES ()");
                $stmtInsertZutat->execute();
                $zutatId = $conn->insert_id;
            
                // Insert into zutaten_namen
                $stmtInsertZutatName = $conn->prepare("INSERT INTO zutaten_namen (name, zutat_id) VALUES (?, ?)");
                $stmtInsertZutatName->bind_param("si", $zutat['name'], $zutatId);
                $stmtInsertZutatName->execute();
                $zutatenNameId = $conn->insert_id;
            
                // Insert into konventionen (wenn nötig, können Sie hier auch eine plural_name_id hinzufügen)
                $stmtInsertKonvention = $conn->prepare("INSERT INTO konventionen (single_name_id, zutat_id) VALUES (?, ?)");
                $stmtInsertKonvention->bind_param("ii", $zutatenNameId, $zutatId);
                $stmtInsertKonvention->execute();
            }
            // Now insert into rezept_zutaten with the correct zutatId
            $stmtZutaten->bind_param("iis", $rezeptId, $zutatId, $zutat['menge']);
            $stmtZutaten->execute();
        }
    }
    echo "Beispielrezepte und Zutatenverbindungen wurden hinzugefügt.";
} else {
    echo "Es sind bereits Rezepte in der Datenbank vorhanden.";
}

$conn->close();
?>
