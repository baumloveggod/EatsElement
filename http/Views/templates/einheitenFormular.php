<label for="name">Name:</label>
    <input type="text" id="name" name="name" ><br><br>
    
    <label for="einheit_umrechnungsfaktor">Umrechnungsfaktor:</label>
    <input type="number" id="einheit_umrechnungsfaktor" name="einheit_umrechnungsfaktor" step="0.01" >
    <div id="info_speziel"> bei "spezieler Bassis ist die referenc immer Gramm</div><br><br>
    
    <label for="basisEinheit">Basis Einheit:</label>
    <select id="basisEinheit" name="basisEinheit">
        <option value="">Bitte wählen</option>     
        <option value="Liter">Liter</option>
        <option value="Gramm">Gramm</option>
        <option value="speziell">speziell</option>
    </select><br><br>