function toggleForm(checkbox) {
    var isChecked = checkbox ? checkbox.checked : false; // Sicherstellen, dass checkbox existiert
    var alternativerNameContainer = document.querySelector('.alternativerNameContainer');
    var restDesFormulars = document.querySelector('.restDesFormulars');
    
    if (alternativerNameContainer && restDesFormulars) {
        alternativerNameContainer.style.display = isChecked ? 'block' : 'none';
        restDesFormulars.style.display = isChecked ? 'none' : 'block';
    }
    // Setze oder entferne das 'required' Attribut basierend auf dem Zustand des Kontrollkästchens
    var inputsAlternative = alternativerNameContainer.getElementsByTagName('input');
    for (var i = 0; i < inputsAlternative.length; i++) {
        inputsAlternative[i].required = isChecked; // Diese Felder sind nur erforderlich, wenn der Container sichtbar ist
    }

    var inputsRest = restDesFormulars.getElementsByTagName('input');
    for (var i = 0; i < inputsRest.length; i++) {
        if (inputsRest[i].type !== 'submit' && !inputsRest[i].classList.contains('volumen')) { // 'volumen' wird separat behandelt
            inputsRest[i].required = !isChecked; // Diese Felder sind nur erforderlich, wenn der Container sichtbar ist
        }
    }

    // Spezialfall für 'volumen', das nur erforderlich ist, wenn es sichtbar ist
    var volumenInput = document.querySelector('.volumen');
    if (volumenInput && volumenInput.closest('.volumen_block').style.display !== 'none') {
        volumenInput.required = true;
    } else if (volumenInput) {
        volumenInput.required = false;
    }
    if (!checkbox) {
        checkNeueEinheit(document.querySelector('.einheit_id').value);
    }
}

document.addEventListener("DOMContentLoaded", function() {
    // Initial setup
    var checkbox = document.querySelector('.existiertUnterAnderemNamen');
    if (checkbox) {
        toggleForm(checkbox);
    }
    
    // Bind change event listener to the units dropdown
    var einheitDropdown = document.querySelector('.einheit_id');
    if (einheitDropdown) {
        einheitDropdown.addEventListener('change', function() {
            checkNeueEinheit(this.value);
        });
    }

    // Bind change event listener to the base unit dropdown
    var basisEinheitDropdown = document.querySelector('.basisEinheit');
    if (basisEinheitDropdown) {
        basisEinheitDropdown.addEventListener('change', function() {
            checkBasisEinheit(this.value);
        });
    }
});

function checkNeueEinheit(value) {
    var neueEinheitFormular = document.querySelector('.neueEinheitFormular');
    var volumenBlock = document.querySelector('.volumen_block');
    var volumenInput = document.querySelector('.volumen');

    // Toggle the new unit form visibility
    neueEinheitFormular.style.display = value === "neuHinzufuegen" ? 'block' : 'none';

    // Adjust visibility and required attribute for the volume input
    volumenBlock.style.display = value === '2' || value === "neuHinzufuegen" ? 'block' : 'none';
    volumenInput.required = volumenBlock.style.display === 'block';
}

function checkBasisEinheit(value) {
    var volumenBlock = document.querySelector('.volumen_block');
    var volumenInput = document.querySelector('.volumen');

    if (volumenBlock && volumenInput) {
        volumenBlock.style.display = value === 'Liter' ? 'block' : 'none';
        volumenInput.required = value === 'Liter';
    }
}

