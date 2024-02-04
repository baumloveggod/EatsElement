// Modified login.js
document.addEventListener('DOMContentLoaded', function() {
    var loginForm = document.getElementById('loginForm');

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();

        var username = document.getElementById('username').value;

        var password = document.getElementById('password').value;

        // Einfache Clientseitige Validierung
        if (!username || !password) {
            alert("Bitte Benutzername und Passwort eingeben.");
            return;
        }

        // Anmeldedaten an Server senden
        var xhr = new XMLHttpRequest();xhr.open('POST', 'Controllers/login.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            // Antwort vom Server verarbeiten
            if (this.status == 200) {
                if (this.responseText.includes("Falsches Passwort") || this.responseText.includes("Benutzername existiert nicht")) {
                    alert(this.responseText);
                } else {
                    window.location.href = 'index.php'; // Weiterleitung zur Hauptseite
                }
            } else {
                alert("Es gab einen Fehler beim Senden der Anfrage.");
            }
        };

        xhr.send('username=' + encodeURIComponent(username) + '&password=' + encodeURIComponent(password));
    });
});

