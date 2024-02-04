// Datei: heutigesGericht.js
var likeBtn = document.getElementById('likeBtn');
if (likeBtn) {
    likeBtn.addEventListener('click', function() {
        // Implementieren Sie Logik, um ein "Like" zu senden
    alert('Geliked!');
    });
}
var dislikeBtn = document.getElementById('dislikeBtn');
if (dislikeBtn) {
    dislikeBtn.addEventListener('click', function() {
     // Implementieren Sie Logik, um ein "Dislike" zu senden
     alert('Disliked!');
    });
}

function discardRecipe() {
    // Implementieren Sie Logik, um das Rezept zu verwerfen
    alert('Rezept verworfen');
}
