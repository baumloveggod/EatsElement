<?php
require 'templates/headerGuest.php'; // Kopfzeile für Gäste
// Inhalt für Gäste
echo "<div class='welcome-container'>
        <h1>Willkommen bei Transformations-Design</h1>
        <p>Bitte melden Sie sich an, um fortzufahren.</p>
        <a href='login.html'>Anmelden</a>
        <p>Noch kein Konto? <a href='register.html'>Registrieren Sie sich hier</a></p>
      </div>";
require 'templates/footer.php'; // Gemeinsamer Fußbereich
