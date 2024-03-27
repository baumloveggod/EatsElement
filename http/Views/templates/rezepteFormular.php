<?php
    // Fehlerberichterstattung einschalten
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Verbindung zur Datenbank herstellen
    require_once '../../Utils/db_connect.php';

?>
    <form action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post" enctype="multipart/form-data">
        <label for="titel">Titel:</label><br>
        <input type="text" id="titel" name="titel" required><br>

        <label for="untertitel">Untertitel:</label><br>
        <input type="text" id="untertitel" name="untertitel"><br>

        <label for="zubereitungszeit">Zubereitungszeit (in Minuten):</label><br>
        <input type="number" id="zubereitungszeit" name="zubereitungszeit" required><br>

        <label for="basis_personenanzahl">Basis Personenanzahl:</label><br>
        <input type="number" id="basis_personenanzahl" name="basis_personenanzahl" required><br>

        <label for="bilder">Bilder:</label><br>
        <input type="file" id="bilder" name="bilder"><br>

        <div id="zutatenContainer">
        </div>
        <br>

        <input type="submit" value="Rezept Hinzufügen">
    </form>

<Script src="../templates/formFunctions.js" ></Script>  
    <script defer>
    
    document.addEventListener('input', function(event) {
        if (event.target && event.target.name.match(/^zutaten\[\d+\]\[name\]$/)) {
            const zutatenName = event.target.value;
            const zutatBlockIndex = event.target.closest('.zutatBlock').dataset.index;
            const einheitenDropdown = document.getElementById(`einheit_id_${zutatBlockIndex}`);
            if (einheitenDropdown) {
                loadEinheiten(einheitenDropdown, zutatenName);
            }
        }
    }); 
    function addZutatBlock() {
        const container = document.getElementById('zutatenContainer');
        const newIndex = container.querySelectorAll('.zutatBlock').length;

        const zutatBlock = document.createElement('div');
        zutatBlock.classList.add('zutatBlock');
        zutatBlock.dataset.index = newIndex;

        zutatBlock.innerHTML = `
            <label>Zutatenname:</label>
            <input type="text" name="zutaten[${newIndex}][name]">

            <label>Menge:</label>
            <input type="text" name="zutaten[${newIndex}][menge]">

            <label>Einheit:</label>
            <select id="einheit_id_${newIndex}" name="zutaten[${newIndex}][einheit_id]">
                <!-- Optionen werden dynamisch geladen -->
            </select>


            <button type="button" class="removeZutat" style="display: none;">Entfernen</button>
        `;

        container.appendChild(zutatBlock);

        const inputs = zutatBlock.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', handleInput);
        });

        const removeBtn = zutatBlock.querySelector('.removeZutat');
        removeBtn.addEventListener('click', function() {
            removeZutatBlock(zutatBlock);
        });
        // Hier, nachdem der zutatBlock dem DOM hinzugefügt wurde:
        loadEinheiten(document.getElementById(`einheit_id_${newIndex}`)); // Laden der Einheiten für das neu erstellte Dropdown
        updateRequiredAttributes();
    }
    function handleInput(event) {
        const zutatBlock = event.target.closest('.zutatBlock');
        const index = parseInt(zutatBlock.dataset.index);
        const container = document.getElementById('zutatenContainer');
        const totalBlocks = container.querySelectorAll('.zutatBlock').length;
        const nextIndex = index + 1;

        if (index === totalBlocks - 1) {
            addZutatBlock(); // Fügt einen neuen Block hinzu, wenn im letzten Block getippt wird
        }

        // Entfernen-Button anzeigen, wenn ein Feld ausgefüllt wird
        const removeBtn = zutatBlock.querySelector('.removeZutat');
        if (event.target.value.trim() !== '') {
            removeBtn.style.display = 'inline';
        } else {
            // Prüfen, ob alle Felder im Block leer sind, bevor der Entfernen-Button versteckt wird
            const inputsFilled = Array.from(zutatBlock.querySelectorAll('input')).some(input => input.value.trim() !== '');
            if (!inputsFilled) {
                removeBtn.style.display = 'none';
            }
        }
        updateRequiredAttributes();
    }
    function updateRequiredAttributes() {
        const container = document.getElementById('zutatenContainer');
        const zutatBlocks = container.querySelectorAll('.zutatBlock');

        zutatBlocks.forEach((block, index) => {
            const isLastBlock = index === zutatBlocks.length - 1;
            const inputs = block.querySelectorAll('input, select');
            
            inputs.forEach(input => {
                // Setzen oder Entfernen des required-Attributs basierend auf der Position des Blocks
                input.required = !isLastBlock;
            });

            // Verwalten der Anzeige des Entfernen-Buttons
            const removeBtn = block.querySelector('.removeZutat');
            if(removeBtn) {
                removeBtn.style.display = isLastBlock && zutatBlocks.length > 1 ? 'inline' : 'none';
            }
        });
    }
    function removeZutatBlock(block) {
        const container = document.getElementById('zutatenContainer');
        block.remove();

        // Neuzuordnung der Indizes und Aktualisierung der name-Attribute für verbleibende Blöcke
        const remainingBlocks = container.querySelectorAll('.zutatBlock');
        remainingBlocks.forEach((block, newIndex) => {
            block.dataset.index = newIndex;
            const inputs = block.querySelectorAll('input');
            const select = block.querySelector('select');

            inputs.forEach(input => {
                const name = input.name;
                const newName = name.replace(/\[\d+\]/, `[${newIndex}]`); // Ersetzen des Index im Namen
                input.name = newName;
            });

            if (select) {
                const name = select.name;
                const newName = name.replace(/\[\d+\]/, `[${newIndex}]`);
                select.name = newName;
                select.id = `einheit_id_${newIndex}`; // Aktualisiere auch die ID des select-Elements
            }
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        document.body.addEventListener('change', function(event) {
            if (event.target && event.target.matches("#zutatenName")) {
                const zutatenName = event.target.value;
                
                // Assuming your fetch logic here is correct and 'Controllers\ladeEinheiten.php' is accessible,
                // you might need to adjust the path to match your project structure.
                fetch('/Controllers/ladeEinheiten.php?zutatenName=' + encodeURIComponent(zutatenName))
                    .then(response => response.json())
                    .then(data => {
                        const einheitenDropdown = document.getElementById('einheit_id');
                        if (einheitenDropdown) { // Ensure the dropdown exists
                            einheitenDropdown.innerHTML = ''; // Clear existing options
                            data.forEach(einheit => {
                                const option = document.createElement('option');
                                option.value = einheit.id;
                                option.textContent = einheit.name;
                                einheitenDropdown.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    });
    document.addEventListener('DOMContentLoaded', function() {
        const addZutatBtn = document.getElementById('addZutat');
        addZutatBtn.addEventListener('click', function() {
            const newIndex = document.querySelectorAll('.zutatBlock').length - 1; // Holen des aktuellen Index
            const einheitenDropdown = document.getElementById(`einheit_id_${newIndex}`);
            loadEinheiten(einheitenDropdown); // Laden der Einheiten für das neue Dropdown
        });
    });
    document.addEventListener("DOMContentLoaded", function() {
        addZutatBlock(); // Initial ein Zutatenblock hinzufügen
    });
    function loadEinheiten(dropdown, zutatenName) {
        if (!dropdown || !zutatenName) return; // Sicherstellen, dass Dropdown und Zutatenname vorhanden sind

        fetch('/Controllers/ladeEinheiten.php?zutatenName=' + encodeURIComponent(zutatenName))
            .then(response => response.json())
            .then(data => {
                dropdown.innerHTML = ''; // Leeren der bestehenden Optionen
                data.forEach(einheit => {
                    const option = document.createElement('option');
                    option.value = einheit.id;
                    option.textContent = einheit.name;
                    dropdown.appendChild(option);
                });
            })
            .catch(error => console.error('Error:', error));
    }
    document.addEventListener("DOMContentLoaded", function() {
        // Verweise auf die relevanten DOM-Elemente
        const einheitDropdown = document.querySelector('.einheit_id');
        const umrechnungsfaktorFeld = document.querySelector('.umrechnungsfaktorFeld');
        const neueEinheitFormular = document.querySelector('.neueEinheitFormular');

        // Event-Listener für Änderungen an der Einheitsauswahl
        einheitDropdown.addEventListener('change', function() {
            const istGramm = einheitDropdown.selectedOptions[0].text === 'Gramm';
            
            // Anzeigen/Verbergen der relevanten Felder basierend auf der Auswahl
            umrechnungsfaktorFeld.style.display = istGramm ? 'none' : 'block';
            neueEinheitFormular.style.display = istGramm ? 'none' : 'block';

            // Anpassen der 'required'-Attribute basierend auf der Auswahl
            umrechnungsfaktorFeld.querySelectorAll('input').forEach(input => {
                input.required = !istGramm;
            });
        });
    });
    // Initial das Laden der Einheiten für den ersten Block auslösen
    document.addEventListener('DOMContentLoaded', function() {
        const initialDropdown = document.getElementById('einheit_id_0');
        loadEinheiten(initialDropdown);
    });

</script>