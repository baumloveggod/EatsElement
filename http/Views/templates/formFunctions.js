document.addEventListener("DOMContentLoaded", function() {
    var checkbox = document.querySelector('.existiertUnterAnderemNamen');
    if (checkbox) {
        toggleForm(checkbox); // Initial state setup
    }
    
    var einheitDropdown = document.querySelector('.einheit_id');
    if (einheitDropdown) {  
        einheitDropdown.addEventListener('change', function() {
            checkNeueEinheit(this.value, this.options[this.selectedIndex].getAttribute('data-basis'));
        });
    }
    var BasiseinheitDropdown = document.querySelector('#basisEinheit');
    if (BasiseinheitDropdown) {
        BasiseinheitDropdown.addEventListener('change', function() {
            checkBasisEinheit(this.value);
        });
    }
});
function toggleForm(checkbox) {
    var isChecked = checkbox.checked;
    var alternativerNameContainer = document.querySelector('.alternativerNameContainer');
    var restDesFormulars = document.querySelector('.restDesFormulars');

    toggleVisibility(alternativerNameContainer, isChecked);
    toggleVisibility(restDesFormulars, !isChecked);
    if (!isChecked){
        checkNeueEinheit(document.querySelector('.einheit_id').value, document.querySelector('.einheit_id').getAttribute('data-basis'));
    }
}

function checkNeueEinheit(value,baseineheit) {
    var neueEinheitFormular = document.querySelector('.neueEinheitFormular');
    var umrechnungsfaktorFeld = document.querySelector('.umrechnungsfaktorFeld');
    var volumenBlock = document.querySelector('.volumen_block');

    toggleVisibility(umrechnungsfaktorFeld, value === "speziell");
    toggleVisibility(volumenBlock, value === '2' || baseineheit === '2');
    toggleVisibility(neueEinheitFormular, value === "neuHinzufuegen");
    if (value === "neuHinzufuegen"){
        checkBasisEinheit(document.querySelector('#basisEinheit').value)
    }
}

// No need to call checkBasisEinheit on DOMContentLoaded since it's not used in the initial toggleForm call.
function checkBasisEinheit(value) {
    var volumenBlock = document.querySelector('.volumen_block');
    toggleVisibility(volumenBlock, value === 'Liter');
}

function toggleVisibility(container, show) {
    if (container !== null) {
        container.style.display = show ? 'block' : 'none';
        var inputs = container.querySelectorAll('input:not([type="submit"]), select');
        inputs.forEach(input => {
            input.required = show && !input.hasAttribute('data-optional');
            if (!show) input.value = ''; // Reset the value if hidden
        });
    } else {
        console.warn('Attempted to toggle visibility of an element that does not exist.');
    }
}
