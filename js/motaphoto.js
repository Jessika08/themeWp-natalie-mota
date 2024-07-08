document.addEventListener('DOMContentLoaded', function() {
    var container = document.getElementById('photo-container');
    var loadMoreButton = document.getElementById('load-more-button');
    var categoryFilter = document.getElementById('category-filter');
    var formatFilter = document.getElementById('format-filter');
    var dateFilter = document.getElementById('date-filter'); // Ajout du filtre de date
    var offset = 0;
    var perPage = 8; // Nombre de photos à charger à chaque fois
    var currentCategory = 'all'; // Catégorie actuellement sélectionnée
    var currentFormat = 'all'; // Format actuellement sélectionné
    var currentOrder = 'desc'; // Ordre de tri actuel (descendant par défaut)

    // Fonction pour charger les photos et donc créer la galerie
    function loadPhotos() {
        var url = motaphoto_js.ajax_url + '?action=request_picture&offset=' + offset + '&category=' + currentCategory + '&format=' + currentFormat + '&order=' + currentOrder;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data && data.posts) {
                    data.posts.forEach(post => {
                        // Créer la div qui encadre les photos
                        var maDiv = document.createElement('div');
                        maDiv.classList.add('photo-div');
                        maDiv.dataset.category = post.category;
                        maDiv.dataset.format = post.format;
                        console.log('Format de l\'image :', post.format);
                        // Créer la div de survol des photos
                        var maDivHover = document.createElement('div');
                        maDivHover.classList.add('hover-photo');

                        // Créer le conteneur pour l'icône "plein écran"
                        var fullScreenContainer = document.createElement('div');
                        fullScreenContainer.classList.add('icon-fullscreen-container');
                        var iconFullScreen = document.createElement('i');
                        iconFullScreen.classList.add('fas', 'fa-expand'); // Icône "plein écran"
                        fullScreenContainer.appendChild(iconFullScreen);

                        // Créer le conteneur pour l'icône "œil"
                        var eyeContainer = document.createElement('div');
                        eyeContainer.classList.add('icon-eye-container');
                        var iconEye = document.createElement('i');
                        iconEye.classList.add('fas', 'fa-eye'); // Icône "œil"
                        eyeContainer.appendChild(iconEye);

                        // Ajouter un attribut de données avec l'URL de la page single-photo
                        eyeContainer.dataset.url = motaphoto_js.site_url + '/pictures/' + post.slug;

                        // Ajouter un gestionnaire d'événements click à l'icône d'œil
                        eyeContainer.addEventListener('click', function() {
                            window.location.href = this.dataset.url;
                        });

                        // Créer le conteneur pour les informations de la photo
                        var infoContainer = document.createElement('div');
                        infoContainer.classList.add('info-container');

                        // Ajouter le titre de la photo
                        var photoTitle = document.createElement('p');
                        photoTitle.classList.add('photo-title');
                        photoTitle.textContent = post.title;

                        // Ajouter la catégorie de la photo
                        var photoCategory = document.createElement('p');
                        photoCategory.classList.add('photo-category');
                        photoCategory.textContent = post.category || 'Unknown Category';

                        // Ajouter le format de la photo
                        //var photoFormat = document.createElement('p');
                        //photoFormat.classList.add('photo-format');
                        //photoFormat.textContent = post.format || 'Unknown Format';

                        infoContainer.appendChild(photoTitle);
                        infoContainer.appendChild(photoCategory);
                        //infoContainer.appendChild(photoFormat);

                        // Ajouter les conteneurs à la div de survol
                        maDivHover.appendChild(fullScreenContainer);
                        maDivHover.appendChild(eyeContainer);
                        maDivHover.appendChild(infoContainer);

                        // Créer le lien
                        var link = document.createElement('a');
                        link.href = motaphoto_js.site_url + '/pictures/' + post.slug;

                        // Créer l'image
                        var img = document.createElement('img');
                        img.src = post.featured_image;
                        img.alt = post.title;
                        img.setAttribute('data-reference', post.reference); // Ajouter la référence comme attribut de données
                        img.setAttribute('data-category', post.category); // Ajouter la catégorie comme attribut de données
                        img.setAttribute('data-format', post.format); // Ajouter le format comme attribut de données

                        // Ajouter l'image au lien
                        link.appendChild(img);

                        // Ajouter le lien et la div de survol à la div principale
                        maDiv.appendChild(link);
                        maDiv.appendChild(maDivHover);
    
                        // Ajouter la div au conteneur
                        container.appendChild(maDiv);
                    });
    
                    offset += perPage; // Augmenter l'offset pour la prochaine requête
                    setupLightbox(); // Réinitialiser les lightbox après le chargement des nouvelles photos
                } else {
                    if (offset === 0) {
                        container.innerHTML = 'Aucune photo trouvée.';
                    } else {
                        loadMoreButton.style.display = 'none'; // Masquer le bouton s'il n'y a plus de photos à charger
                    }
                }
            })
            .catch(error => {
                                // Sélectionne l'élément body
                var bodyElement = document.body;

                // Vérifie si l'élément body a la classe 'home'
                if (bodyElement.classList.contains('home')) {
                console.log('Le body a la classe "home".');
                console.error('Erreur lors de la récupération des photos :', error);
                container.innerHTML = 'Erreur lors de la récupération des photos.';
                } 
            });
    }
    
    // Charger les premières photos au chargement de la page
    loadPhotos();
    
    // Gérer le clic sur le bouton "Charger plus"
    // Sélectionne l'élément body
    var bodyElement = document.body;

    // Vérifie si l'élément body a la classe 'home'
    if (bodyElement.classList.contains('home')) {
   loadMoreButton.addEventListener('click', function() {
        loadPhotos();
    });
    } 
    
    // Fonction pour charger les catégories depuis WordPress via AJAX
    function loadCategories() {
        if (categoryFilter) {
            fetch(motaphoto_js.ajax_url + '?action=load_categories')
                .then(response => response.json())
                .then(data => {
                    // Vider le sélecteur déroulant actuel
                    categoryFilter.innerHTML = '';
    
                    // Ajouter une option "Toutes les catégories"
                    var optionAll = document.createElement('option');
                    optionAll.value = 'all';
                    optionAll.textContent = 'Catégories';
                    categoryFilter.appendChild(optionAll);
    
                    // Ajouter les options des catégories dynamiquement
                    data.forEach(category => {
                        var option = document.createElement('option');
                        option.value = category.slug;
                        option.textContent = category.name;
                        categoryFilter.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des catégories :', error);
                });
        }
    }
    
    // Appeler loadCategories une fois que le document est complètement chargé
    loadCategories();
    
    // Fonction pour charger les formats depuis WordPress via AJAX
    function loadFormats() {
        if (formatFilter) {
            fetch(motaphoto_js.ajax_url + '?action=load_formats')
                .then(response => response.json())
                .then(data => {
                    // Vider le sélecteur déroulant actuel
                    formatFilter.innerHTML = '';
    
                    // Ajouter une option "Tous les formats"
                    var optionAll = document.createElement('option');
                    optionAll.value = 'all';
                    optionAll.textContent = 'Formats';
                    formatFilter.appendChild(optionAll);
    
                    // Ajouter les options des formats dynamiquement
                    data.forEach(format => {
                        var option = document.createElement('option');
                        option.value = format.slug;
                        option.textContent = format.name;
                        formatFilter.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des formats :', error);
                });
        }
    }
    
    // Appeler loadFormats une fois que le document est complètement chargé
    loadFormats();
    
        // Vérifie si l'élément body a la classe 'home'
    if (bodyElement.classList.contains('home')) {
        
    // Fonction pour filtrer les photos en fonction de la catégorie ou du format sélectionné
        function filterPhotos() {
            offset = 0; // Réinitialiser l'offset pour le nouveau filtre
            currentCategory = categoryFilter.value;
            currentFormat = formatFilter.value;
            currentOrder = dateFilter.value; // Mise à jour de l'ordre de tri basé sur la sélection
            container.innerHTML = ''; // Vider le conteneur avant de charger les nouvelles photos
            loadPhotos(); // Charger les photos avec les nouvelles catégories et formats
        }
        
            // Ajouter des écouteurs d'événements pour les changements de catégorie, de format et de date
            if (categoryFilter) {
                categoryFilter.addEventListener('change', filterPhotos);
            }
            if (formatFilter) {
                formatFilter.addEventListener('change', filterPhotos);
            }
            if (dateFilter) {
                dateFilter.addEventListener('change', filterPhotos); // Écouteur d'événements pour le filtre de date
            }
        console.log('Le body a la classe "home".');
    } else {
        console.log('Le body n\'a pas la classe "home".');
    }

    
    // Ajout de la fonctionnalité de la lightbox au clic sur l'icône plein écran
    function setupLightbox() {
        // Sélectionner tous les éléments avec la classe 'icon-fullscreen-container'
        var fullscreenIcons = document.querySelectorAll('.icon-fullscreen-container');
    
        // Boucler sur chaque icône plein écran pour ajouter un gestionnaire d'événements
        fullscreenIcons.forEach(iconContainer => {
            iconContainer.addEventListener('click', function() {
                // Vérifier si la lightbox existe déjà et la supprimer
var existingLightbox = document.querySelector('.custom-lightbox');
if (existingLightbox) {
existingLightbox.remove();
}
// Récupérer l'image associée
var img = this.parentElement.parentElement.querySelector('img');
var allImages = document.querySelectorAll('.photo-div img'); // Récupérer toutes les images

            // Créer la lightbox
            var lightbox = document.createElement('div');
            lightbox.classList.add('custom-lightbox');

            // Créer l'image de la lightbox
            var lightboxImg = img.cloneNode(true);
            lightboxImg.dataset.currentIndex = Array.from(allImages).indexOf(img); // NOUVEAU
            lightbox.appendChild(lightboxImg);

            // Créer la croix pour fermer la lightbox
            var closeIcon = document.createElement('i');
            closeIcon.classList.add('fas', 'fa-times', 'close-icon');
            closeIcon.addEventListener('click', function() {
                lightbox.remove(); // Fermer la lightbox au clic sur la croix
                document.body.classList.remove('noscroll');
            });

            // Créer les boutons précédent et suivant avec les flèches
            var prevButton = document.createElement('div');
            prevButton.classList.add('prev-button');
            prevButton.innerHTML = '<i class="fas fa-arrow-left-long"></i> Précédente';

            var nextButton = document.createElement('div');
            nextButton.classList.add('next-button');
            nextButton.innerHTML = 'Suivante <i class="fas fa-arrow-right-long"></i>';

            // Ajouter les boutons à la lightbox
            lightbox.appendChild(closeIcon);
            lightbox.appendChild(prevButton);
            lightbox.appendChild(nextButton);

            // Ajouter les événements pour naviguer entre les images
            prevButton.addEventListener('click', function() {
                navigateLightbox(-1, allImages);
            });
            nextButton.addEventListener('click', function() {
                navigateLightbox(1, allImages);
            });

            // Ajouter le conteneur d'informations
            var infoContainer = document.createElement('div');
            infoContainer.classList.add('info-container');

            // Ajouter la référence et la catégorie
            var reference = document.createElement('p');
            reference.textContent = img.getAttribute('data-reference');//'Référence : ' + 
            var category = document.createElement('p');
            category.textContent =  img.getAttribute('data-category');//'Catégorie : ' +
            //var format = document.createElement('p');
            //format.textContent = 'Format : ' + img.getAttribute('data-format');

            infoContainer.appendChild(reference);
            infoContainer.appendChild(category);
            //infoContainer.appendChild(format); // Ajouter le format à la lightbox

            // Ajouter la lightbox au body
            lightbox.appendChild(infoContainer);
            document.body.appendChild(lightbox);
            document.body.classList.add('noscroll');
        });
    });
}

// Fonction pour naviguer dans la lightbox
function navigateLightbox(direction, allImages) {
    var lightbox = document.querySelector('.custom-lightbox');
    var lightboxImg = lightbox.querySelector('img'); // Sélectionner l'image dans la lightbox
    var currentIndex = parseInt(lightboxImg.dataset.currentIndex); // Utiliser l'index stocké dans l'attribut de données
    var newIndex = currentIndex + direction;

    if (newIndex >= 0 && newIndex < allImages.length) {
        var newImg = allImages[newIndex];

        // Mettre à jour l'image et les informations dans la lightbox
        var reference = lightbox.querySelector('.info-container p:nth-child(1)');
        var category = lightbox.querySelector('.info-container p:nth-child(2)');
        //var format = lightbox.querySelector('.info-container p:nth-child(3)');

        lightboxImg.src = newImg.src;
        lightboxImg.alt = newImg.alt;
        reference.textContent =  newImg.getAttribute('data-reference');//'Référence : ' +
        category.textContent =  newImg.getAttribute('data-category');//'Catégorie : ' +
        //format.textContent = 'Format : ' + newImg.getAttribute('data-format');

        // Mettre à jour l'index de l'image actuelle
        lightboxImg.dataset.currentIndex = newIndex; // NOUVEAU
    }
}

// Appeler la fonction pour initialiser la lightbox au chargement de la page
setupLightbox();
});

document.addEventListener('DOMContentLoaded', function() {
    var menuBurger = document.querySelector('.menu-burger');
    var menuModal = document.querySelector('.menu-modal');
    var closeModal = document.querySelector('.close-modal');
    //const contactLink = document.querySelector('a[href="http://nathalie-mota-photographe.local/contact/"]');

    menuBurger.addEventListener('click', function() {
        menuModal.classList.toggle('active');
        document.body.classList.add('no-scroll'); // Désactiver le scroll
    });

    closeModal.addEventListener('click', function() {
        menuModal.classList.remove('active');
        document.body.classList.remove('no-scroll'); // Activer le scroll
    });

    // Optional: Close the modal when clicking outside of the menu content
    menuModal.addEventListener('click', function(event) {
        if (event.target === menuModal) {
            menuModal.classList.remove('active');
            document.body.classList.remove('no-scroll'); // Activer le scroll
        }   
    });

    //contactLink.addEventListener('click', function() {
       // menuModal.classList.remove('open');
       // document.body.classList.remove('no-scroll'); // Activer le scroll
    //});

});
document.addEventListener('DOMContentLoaded', function() {
        // Sélectionne la div avec l'ID menu-menu-principal
        var menu = document.getElementById('menu-menu-principal');
       
        // Trouve le lien spécifique avec href contenant /contact/
        var contactLink = menu.querySelector('a[href*="/contact/"]');
       
        // Trouve la div avec la classe close-modal
        var closeModalDiv = document.querySelector('.close-modal');
       
        // Vérifie si les éléments existent
        if (contactLink && closeModalDiv) {
            // Ajoute un écouteur d'événement au lien
            contactLink.addEventListener('click', function(event) {
                // Empêche le comportement par défaut du lien
                event.preventDefault();
                // Simule un clic sur la div close-modal
                closeModalDiv.click();
            });
        }
    });

    // Sélectionne l'élément body
const bodyElement = document.body;

// Vérifie si l'élément body a la classe 'home'
if (bodyElement.classList.contains('home')) {
  console.log('Le body a la classe "home".');
} else {
  console.log('Le body n\'a pas la classe "home".');
}
document.addEventListener('DOMContentLoaded', function() {
// Sélectionner toutes les divs avec la classe "photo-div" à l'intérieur de ".related-photo"
var photoDivs = document.querySelectorAll('.related-photo .photo-div');

// Parcourir chaque div
photoDivs.forEach(photoDiv => {
    // Extraire les données des attributs de la div
   
    var format = photoDiv.dataset.format;
   


    // Sélectionner le lien à l'intérieur de la div
    var link = photoDiv.querySelector('a');

    console.log('Format de l\'image :', format);

    // Créer la div de survol des photos
    var maDivHover = document.createElement('div');
    maDivHover.classList.add('hover-photo');

    // Créer le conteneur pour l'icône "plein écran"
    var fullScreenContainer = document.createElement('div');
    fullScreenContainer.classList.add('icon-fullscreen-container');
    var iconFullScreen = document.createElement('i');
    iconFullScreen.classList.add('fas', 'fa-expand'); // Icône "plein écran"
    fullScreenContainer.appendChild(iconFullScreen);

    // Créer le conteneur pour l'icône "œil"
    var eyeContainer = document.createElement('div');
    eyeContainer.classList.add('icon-eye-container');
    var iconEye = document.createElement('i');
    iconEye.classList.add('fas', 'fa-eye'); // Icône "œil"
    eyeContainer.appendChild(iconEye);

    // Ajouter un attribut de données avec l'URL de la page single-photo
    eyeContainer.dataset.url = link.getAttribute('href');
    //= au href queje recupere et qui est dans la photo-div 

    // Ajouter un gestionnaire d'événements click à l'icône d'œil
    eyeContainer.addEventListener('click', function() {
        window.location.href = this.dataset.url;
    });


    // Ajouter les conteneurs à la div de survol
    //maDivHover.appendChild(fullScreenContainer);
    maDivHover.appendChild(eyeContainer);
   
    
    // Ajouter le lien et la div de survol à la div principale
    //photoDiv.appendChild(link);
    photoDiv.appendChild(maDivHover);

    //setupLightbox()
});

});

