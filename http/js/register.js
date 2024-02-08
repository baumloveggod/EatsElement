document.addEventListener('DOMContentLoaded', function() {
    var registerForm = document.getElementById('registerForm');

    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validierung
        var username = document.getElementById('newUsername').value;
        var password = document.getElementById('newPassword').value;    
        var errorMessages = [];

        if (!username || username.length < 5) {
            errorMessages.push("Benutzername muss mindestens 5 Zeichen lang sein.");
        }

        if (!password || password.length < 8) {
            errorMessages.push("Passwort muss mindestens 8 Zeichen lang sein.");
        }

        // Zeige Fehlermeldungen an, falls vorhanden
        if (errorMessages.length > 0) {
            alert(errorMessages.join('\n'));
            return;
        }

        // Daten an Server senden
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'Controllers/register.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (this.responseText.includes("Registrierung erfolgreich!") || this.responseText.includes("Benutzername existiert nicht")) {
                window.location.href = 'index.php'; // Weiterleitung zur Hauptseite
                $_SESSION['is_temporary'] = False;
            } else {
                alert(this.responseText);
            }
        };

        xhr.send('username=' + encodeURIComponent(username) + '&password=' + encodeURIComponent(password));
    });
});
