<Script> function toggleVisibility(container, show) {
    container.style.display = show ? 'block' : 'none';
    const inputs = container.querySelectorAll('input:not([type="submit"]), select');
    inputs.forEach(input => {
        input.required = show && !input.hasAttribute('data-optional');
        if (!show) input.value = ''; // Reset the value if hidden
    });
}

function toggleForm(checkbox) {
    const isChecked = checkbox.checked;
    const alternativerNameContainer = document.querySelector('.alternativerNameContainer');
    const restDesFormulars = document.querySelector('.restDesFormulars');

    toggleVisibility(alternativerNameContainer, isChecked);
    toggleVisibility(restDesFormulars, !isChecked);

    handleEinheitenChange();
}

function checkNeueEinheit(value) {
    const neueEinheitFormular = document.querySelector('.neueEinheitFormular');
    const umrechnungsfaktorFeld = document.querySelector('.umrechnungsfaktorFeld');
    const volumenBlock = document.querySelector('.volumen_block');

    toggleVisibility(neueEinheitFormular, value === "neuHinzufuegen");
    toggleVisibility(umrechnungsfaktorFeld, value === "speziell" || value === "neuHinzufuegen");
    toggleVisibility(volumenBlock, value === '2' || value === "neuHinzufuegen");
}

function handleEinheitenChange() {
    const einheitenDropdown = document.querySelector('.einheit_id');
    const selectedOption = einheitenDropdown.options[einheitenDropdown.selectedIndex];
    const basis = selectedOption.getAttribute('data-basis');
    const volumenField = document.querySelector('.volumen');
    const umrechnungsfaktorField = document.querySelector('.umrechnungsfaktor');

    volumenField.required = basis === '2'; // '2' entspricht der ID für Liter
    volumenField.style.display = volumenField.required ? '' : 'none';
    umrechnungsfaktorField.required = einheitenDropdown.value === "speziell";
    umrechnungsfaktorField.style.display = umrechnungsfaktorField.required ? '' : 'none';
}

function handleBasisEinheitChange() {
    const basisEinheitDropdown = document.querySelector('#basisEinheit');
    const volumenField = document.querySelector('.volumen');
    const umrechnungsfaktorField = document.querySelector('.umrechnungsfaktor');

    volumenField.required = basisEinheitDropdown.value === 'Liter';
    volumenField.style.display = volumenField.required ? '' : 'none';
    umrechnungsfaktorField.required = basisEinheitDropdown.value === 'speziell';
    umrechnungsfaktorField.style.display = umrechnungsfaktorField.required ? '' : 'none';
}

document.addEventListener("DOMContentLoaded", function() {
    const checkbox = document.querySelector('.existiertUnterAnderemNamen');
    if (checkbox) {
        toggleForm(checkbox); // Initial state setup
    }

    const einheitDropdown = document.querySelector('.einheit_id');
    if (einheitDropdown) {
        einheitDropdown.addEventListener('change', handleEinheitenChange);
    }

    const basisEinheitDropdown = document.querySelector('#basisEinheit');
    if (basisEinheitDropdown) {
        basisEinheitDropdown.addEventListener('change', handleBasisEinheitChange);
    }
});
</Script>