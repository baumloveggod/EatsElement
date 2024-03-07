<?php
// Fehlerberichterstattung einschalten
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verbindung zur Datenbank herstellen
require_once '../../Utils/db_connect.php';

include 'einheiten_post.php';

// Überprüfen, ob das Formular gesendet wurde
function insert_into_Zutaten() {
    // Überprüfen, ob das Formular gesendet wurde und die Aktion "Zutat Unter Anderem Namen Hinzufügen" ist
    if (isset($_POST['aktion_name']) && $_POST['aktion_name'] === "Zutat Unter Anderem Namen Hinzufügen") {
 // Daten aus dem Formular holen
 $alternativerName = $_POST['alternativerName'];

 // Suche nach einer Zutat mit dem alternativen Namen
 $stmt = $conn->prepare("SELECT zutat_id FROM zutaten_namen WHERE name = ?");
 $stmt->bind_param("s", $alternativerName);
 $stmt->execute();
 $result = $stmt->get_result();

 if ($result->num_rows > 0) {
     // Zutat existiert, also füge den neuen Namen hinzu
     $row = $result->fetch_assoc();
     $zutatId = $row['zutat_id'];

     // Neuen Namen zur zutaten_namen Tabelle hinzufügen
     $zutaten_name = $_POST['zutaten_name']; // Der "neue" Name der Zutat
     $stmt = $conn->prepare("INSERT INTO zutaten_namen (name, zutat_id) VALUES (?, ?)");
     $stmt->bind_param("si", $zutaten_name, $zutatId);
     if ($stmt->execute()) {
  echo "<p>Neuer Name erfolgreich hinzugefügt.</p>";
     } else {
  echo "<p>Fehler beim Hinzufügen des neuen Namens: " . $stmt->error . "</p>";
     }
 } else {
     // Zutat nicht gefunden
     echo "<p>Die Zutat unter dem Namen '$alternativerName' wurde nicht gefunden. Bitte überprüfen Sie den Namen und versuchen Sie es erneut.</p>";
 }
    
 $stmt->close();
    }else{
 //zutaten_name : alternativerName : haltbarkeit : kategorie_id : phd_kategorie_id : einheit_id : name : einheit_umrechnungsfaktor : basisEinheit : umrechnungsfaktor : volumen
 foreach ($_POST as $a => $value) {
     echo $a . " : ";
 }
 
 if ($_POST['einheit_id'] === 'neuHinzufuegen') {
  
     // Führe die Funktion zum Hinzufügen der neuen Einheit aus und erhalte die neue Einheits-ID
     $einheit_id = insert_into_Eineheiten();
 }
 else{
     $einheit_id = $_POST['einheit_id'];
 }
 $hatSpezifischenUmrechnungsfaktor = $_POST['basisEinheit'] === 'speziell';
 $haltbarkeit = $_POST['haltbarkeit'] ?? null;
 $volumen = $_POST['volumen'] ?? null;
 $kategorie_id = $_POST['kategorie_id'] ?? null;
 $phd_kategorie_id = $_POST['phd_kategorie_id'] ?? null;
 $spezifischer_umrechnungsfaktor = ($hatSpezifischenUmrechnungsfaktor) ? $_POST['einheit_id'] === 'neuHinzufuegen' ? $_POST['einheit_umrechnungsfaktor']:$_POST['umrechnungsfaktor'] : null;

 // Prepared Statement zum Hinzufügen der Zutat vorbereiten
 $stmt = $conn->prepare("INSERT INTO zutaten (uebliche_haltbarkeit, volumen, kategorie_id, phd_kategorie_id, einheit_id, spezifischer_umrechnungsfaktor) VALUES (?, ?, ?, ?, ?, ?)");
 echo $haltbarkeit . " : " .  $volumen . " : " . $kategorie_id . " : " . $phd_kategorie_id . " : " . $einheit_id . " : " . $spezifischer_umrechnungsfaktor;
 // Parameter binden
 $stmt->bind_param("idiiid", $haltbarkeit, $volumen, $kategorie_id, $phd_kategorie_id, $einheit_id, $spezifischer_umrechnungsfaktor);

     // Versuchen, die Prepared Statement auszuführen
     if ($stmt->execute()) {
  if (empty($_POST['zutaten_name'])) {
      echo "<p>Name is required.</p>";
      // Handle the error appropriately - perhaps by not proceeding with the DB insert
  }
  $zutaten_name = $_POST['zutaten_name'];

  // Assuming $stmt->execute() was successful and $name is the name of the ingredient
  $zutatId = $conn->insert_id; // Retrieves the ID of the last inserted row
  $stmt = $conn->prepare("INSERT INTO zutaten_namen (name, zutat_id) VALUES (?, ?)");
  $stmt->bind_param("si", $zutaten_name, $zutatId);
  if (!$stmt->execute()) {
      echo "<p>Fehler beim Hinzufügen des Namens der Zutat: " . $stmt->error . "</p>";
  }

  echo "<p>Zutat erfolgreich hinzugefügt!</p>";
     } else {
  echo "<p>Fehler beim Hinzufügen der Zutat: " . $stmt->error . "</p>";
     }

     // Prepared Statement schließen
     $stmt->close();
 }
    }
?>