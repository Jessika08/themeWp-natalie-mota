<?php
get_header(); // Inclut le fichier header.php

?>

<!-- Le contenu principal ici -->
<main>
    <h1>Bienvenue sur mon site</h1>
    <p>Voici le contenu principal de la page d'accueil.</p>

    <label for="category-filter">Filtrer par Catégorie :</label>
    <select id="category-filter">
        <option value="all">Toutes les catégories</option>
        <!-- Les options seront ajoutées dynamiquement via JavaScript -->
    </select>

    <label for="format-filter">Filtrer par Format :</label>
    <select id="format-filter">
        <option value="all">Tout les formats</option>
        <!-- Les options seront ajoutées dynamiquement via JavaScript -->
    </select>

    <div id="photo-container"></div>
    <div class="load-more-div">
    <button id="load-more-button">Charger plus</button>
    </div>
</main>

<?php
get_footer(); // Inclut le fichier footer.php
?>