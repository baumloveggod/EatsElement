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
    // Schritt 2: Keine Rezepte vorhanden, füge Beispielrezepte ein
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
