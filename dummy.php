<script>
document.addEventListener("DOMContentLoaded", function() {
    addZutatBlock(); // Initial einen Zutatenblock hinzufügen

    function addZutatBlock() {
        const container = document.getElementById('zutatenContainer');
        const newIndex = container.querySelectorAll('.zutatBlock').length;

        const zutatBlock = document.createElement('div');
        zutatBlock.classList.add('zutatBlock');
        zutatBlock.dataset.index = newIndex;

        zutatBlock.innerHTML = `
            <label>Zutatenname:</label>
            <input type="text" name="zutaten[${newIndex}][name]" class="zutatenName" required>

            <label>Menge:</label>
            <input type="text" name="zutaten[${newIndex}][menge]" required>

            <label>Einheit:</label>
            <select name="zutaten[${newIndex}][einheit_id]" id="einheit_id_${newIndex}">
                <!-- Optionen werden dynamisch geladen -->
            </select>

            <button type="button" class="removeZutat">Entfernen</button>
        `;

        container.appendChild(zutatBlock);

        loadEinheiten(newIndex); // Lädt Einheiten für den aktuellen Zutatenblock

        const removeBtn = zutatBlock.querySelector('.removeZutat');
        removeBtn.addEventListener('click', function() {
            removeZutatBlock(zutatBlock);
        });

        zutatBlock.querySelector('.zutatenName').addEventListener('change', function(event) {
            loadEinheiten(newIndex, event.target.value); // Lädt Einheiten basierend auf dem geänderten Zutatennamen
        });
    }

    function loadEinheiten(index, zutatenName = '') {
        const dropdown = document.getElementById(`einheit_id_${index}`);
        if (!dropdown) return;

        // Hier Ihr fetch-Request, um die Einheiten basierend auf `zutatenName` zu laden,
        // z.B. fetch('/Controllers/ladeEinheiten.php?zutatenName=' + encodeURIComponent(zutatenName))
        // Denken Sie daran, Ihren tatsächlichen Endpunkt und die Logik zum Befüllen des Dropdowns einzufügen
    }

    function removeZutatBlock(block) {
        block.remove();
        // Nach dem Entfernen müssen wir die Indizes der übrigen Zutatenblöcke aktualisieren
        updateZutatenBlockIndices();
    }

    function updateZutatenBlockIndices() {
        const zutatenBlocks = document.querySelectorAll('.zutatBlock');
        zutatenBlocks.forEach((block, newIndex) => {
            block.dataset.index = newIndex;
            block.querySelectorAll('input, select').forEach(element => {
                const name = element.name.replace(/\[\d+\]/, `[${newIndex}]`);
                element.name = name;

                if (element.tagName === 'SELECT') {
                    element.id = `einheit_id_${newIndex}`;
                }
            });
        });
    }
});
</script>
