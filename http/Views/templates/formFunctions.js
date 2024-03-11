function toggleForm(checkbox) {
    var isChecked = checkbox.checked;
    var alternativerNameContainer = document.getElementById('alternativerNameContainer');
    var restDesFormulars = document.getElementById('restDesFormulars');
    
    // Sichtbarkeit umschalten
    alternativerNameContainer.style.display = isChecked ? 'block' : 'none';
    restDesFormulars.style.display = isChecked ? 'none' : 'block';

    // Setze oder entferne das 'required' Attribut basierend auf dem Zustand des Kontrollkästchens
    var inputsAlternative = alternativerNameContainer.getElementsByTagName('input');
    for (var i = 0; i < inputsAlternative.length; i++) {
inputsAlternative[i].required = isChecked; // Diese Felder sind nur erforderlich, wenn der Container sichtbar ist
    }

    var inputsRest = restDesFormulars.getElementsByTagName('input');
    for (var i = 0; i < inputsRest.length; i++) {
// Überprüfe, ob das Eingabefeld sichtbar ist, bevor du es als required markierst
if (inputsRest[i].type !== 'submit' && inputsRest[i].id !== 'volumen') { // 'volumen' wird separat behandelt
    inputsRest[i].required = !isChecked; // Diese Felder sind nur erforderlich, wenn der Container sichtbar ist
}
    }

    // Spezialfall für 'volumen', das nur erforderlich ist, wenn es sichtbar ist
    var volumenInput = document.getElementById('volumen');
    if (volumenInput.style.display !== 'none') {
volumenInput.required = true;
    } else {
volumenInput.required = false;
    }
    if (!checkbox){
checkNeueEinheit(document.getElementById('einheit_id').value);
    }
}
window.onload = function() {
    toggleForm(document.getElementById('existiertUnterAnderemNamen'));
}
document.addEventListener("DOMContentLoaded", function() {
    // Bind change event listener to the units dropdown
    var einheitDropdown = document.getElementById('einheit_id');
    if (einheitDropdown) {
        einheitDropdown.addEventListener('change', handleEinheitChange);
    }

// Initial check to set the correct state when the page loads
    if (einheitDropdown) {
        handleEinheitChange({target: einheitDropdown});
    }
    
    var basisEinheitDropdown = document.getElementById('basisEinheit');
    if (basisEinheitDropdown) {
        basisEinheitDropdown.addEventListener('change', handleBasisEinheitChange);
    }

// Initial check for basisEinheitDropdown
    if (basisEinheitDropdown) {
        handleBasisEinheitChange({target: basisEinheitDropdown});
    }
});

function handleEinheitChange(event) {
    var selectedOption = event.target.options[event.target.selectedIndex];
    var umrechnungsfaktorField = document.getElementById('umrechnungsfaktorFeld');
    var umrechnungsfaktorInput = document.getElementById('umrechnungsfaktor');
    
    // Check if the selected unit requires a conversion factor
    if (selectedOption.dataset.spezifischerUmrechnungsfaktor === '1') {
umrechnungsfaktorField.style.display = 'block';
umrechnungsfaktorInput.required = true;
    } else {
umrechnungsfaktorField.style.display = 'none';
umrechnungsfaktorInput.required = false;
umrechnungsfaktorInput.value = ''; // Clear the input if it's not required
    }
}

function handleBasisEinheitChange(event) {
    var selectedValue = event.target.value;
    var volumenBlock = document.getElementById('volumen_block');
    var volumenInput = document.getElementById('volumen');
    
    // Adjust visibility and required attribute for the volume input
    if (selectedValue === 'Liter') {
volumenBlock.style.display = 'block';
volumenInput.required = true;
    } else {
volumenBlock.style.display = 'none';
volumenInput.required = false;
volumenInput.value = ''; // Clear the input if it's hidden
    }
}// This function toggles the visibility of the new unit form and its inputs' required status
function checkNeueEinheit(value) {
    var isNewUnitSelected = value === "neuHinzufuegen";
    var neueEinheitFormular = document.getElementById('neueEinheitFormular');
    var volumenBlock = document.getElementById('volumen_block');
    var volumenInput = document.getElementById('volumen');

    // Toggle the new unit form visibility
    neueEinheitFormular.style.display = isNewUnitSelected ? 'block' : 'none';

    // Set the required attribute for inputs in the new unit form based on its visibility
    var inputs = neueEinheitFormular.getElementsByTagName('input');
    for (var i = 0; i < inputs.length; i++) {
        inputs[i].required = isNewUnitSelected;
    }      

    // Adjust visibility and required attribute for the volumen input
    var selectedOption = document.querySelector('#einheit_id option:checked')
    var displayVolumen = 'none';
    if (value === '2' || 
        (isNewUnitSelected && document.getElementById('basisEinheit').value === 'Liter')|| 
        (selectedOption.getAttribute('data-basis') === '2')) {
            displayVolumen = 'block';
    }

    volumenBlock.style.display = displayVolumen;
    volumenInput.required = displayVolumen === 'block';

    if (isNewUnitSelected)
    checkBasisEinheit(document.getElementById('basisEinheit').value);

    // Neuer Teil: Überprüfen, ob die ausgewählte Einheit einen speziellen Umrechnungsfaktor benötigt
    var selectedOption = document.querySelector('#einheit_id option:checked');
    if (selectedOption !== null) { // Check if selectedOption is not null
        var hatSpezifischenUmrechnungsfaktor = selectedOption.getAttribute('data-spezifischer-umrechnungsfaktor') === '1'; // Annahme: '1' bedeutet wahr
        // Sichtbarkeit und Required-Status für das Umrechnungsfaktor-Feld anpassen
        var umrechnungsfaktorFeld = document.getElementById('umrechnungsfaktorFeld'); // Stellen Sie sicher, dass Sie ein entsprechendes Feld im HTML-Markup haben
        umrechnungsfaktorFeld.style.display = hatSpezifischenUmrechnungsfaktor ? 'block' : 'none';
        umrechnungsfaktorFeld.required = hatSpezifischenUmrechnungsfaktor;
    } else {
        // Handle the case where no option is selected or exists
        document.getElementById('umrechnungsfaktorFeld').style.display = 'none';
        document.getElementById('umrechnungsfaktorFeld').required = false;
        }
}
// Event-Listener für die Auswahländerung hinzufügen
document.getElementById('einheit_id').addEventListener('change', function() {
    checkNeueEinheit(this.value);
});
// Initialen Check ausführen
checkNeueEinheit(document.getElementById('einheit_id').value);
// This function updates the visibility of the volumen input based on the selected base unit

function checkBasisEinheit(value) {
    var volumenBlock = document.getElementById('volumen_block');
    var volumenInput = document.getElementById('volumen');

    var displayVolumen = value === 'Liter' ? 'block' : 'none';
    volumenBlock.style.display = displayVolumen;
    volumenInput.required = displayVolumen === 'block';

    var info_speziel = document.getElementById('info_speziel');
    info_speziel.style.display = value === 'speziell' ?  "block" : 'none';            
    }