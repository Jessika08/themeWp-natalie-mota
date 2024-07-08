function setupModal() {
    // Obtenir la modal
    var modal = document.getElementById("myModal");

    // Obtenir tous les éléments qui ouvrent la modal
    var btns = document.querySelectorAll(".open-modal");

    // Obtenir l'élément <span> qui ferme la modal
    var span = document.createElement("span");
    span.innerHTML = "Fermer";
    span.classList.add("close-button");
    modal.appendChild(span);

    // Ajouter un événement de clic à chaque bouton
    btns.forEach(function(btn) {
        btn.onclick = function(event) {
            event.preventDefault();

            // Récupérer la référence à partir de l'attribut data-reference
            var reference = this.getAttribute('data-reference');
            // Préremplir le champ de référence dans la modal
            var referenceField = document.getElementById('reference-field'); // À adapter selon votre modal
            if (referenceField) {
                referenceField.value = reference;
            }

            modal.style.display = "block";
        }
    });

    // Fermer la modal lorsque l'utilisateur clique sur <span> (Fermer)
    span.onclick = function() {
        modal.style.display = "none";
    }

    // Fermer la modal lorsque l'utilisateur clique en dehors de celle-ci
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
}

// Appeler la fonction setupModal lorsque le DOM est entièrement chargé
document.addEventListener('DOMContentLoaded', function() {
    setupModal();
});