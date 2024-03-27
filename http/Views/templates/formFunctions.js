function toggleVisibility(container, show) {
    container.style.display = show ? 'block' : 'none';
    var inputs = container.querySelectorAll('input:not([type="submit"]), select');
    inputs.forEach(input => {
        input.required = show && !input.hasAttribute('data-optional');
        if (!show) input.value = ''; // Reset the value if hidden
    });
}

function toggleForm(checkbox) {
    var isChecked = checkbox.checked;
    var alternativerNameContainer = document.querySelector('.alternativerNameContainer');
    var restDesFormulars = document.querySelector('.restDesFormulars');

    toggleVisibility(alternativerNameContainer, isChecked);
    toggleVisibility(restDesFormulars, !isChecked);

    checkNeueEinheit(document.querySelector('.einheit_id').value);
}

document.addEventListener("DOMContentLoaded", function() {
    var checkbox = document.querySelector('.existiertUnterAnderemNamen');
    if (checkbox) {
        toggleForm(checkbox); // Initial state setup
    }
    
    var einheitDropdown = document.querySelector('.einheit_id');
    if (einheitDropdown) {
        einheitDropdown.addEventListener('change', function() {
            checkNeueEinheit(this.value);
        });
    }
});

function checkNeueEinheit(value) {
    var neueEinheitFormular = document.querySelector('.neueEinheitFormular');
    var umrechnungsfaktorFeld = document.querySelector('.umrechnungsfaktorFeld');
    var volumenBlock = document.querySelector('.volumen_block');

    toggleVisibility(neueEinheitFormular, value === "neuHinzufuegen");
    toggleVisibility(umrechnungsfaktorFeld, value === "speziell" || value === "neuHinzufuegen");
    toggleVisibility(volumenBlock, value === '2' || value === "neuHinzufuegen");
}

// No need to call checkBasisEinheit on DOMContentLoaded since it's not used in the initial toggleForm call.
function checkBasisEinheit(value) {
    var volumenBlock = document.querySelector('.volumen_block');
    toggleVisibility(volumenBlock, value === 'Liter');
}
