<?php
get_header(); // Inclut le fichier header.php

?>

<!-- Le contenu principal ici -->
<main>
    
<div class="hero">
    <img class="hero-background" src="<?php echo esc_url($_SERVER['HTTP_HOST'].'/wp-content/uploads/2024/07/hero-img-title-1.png' ); ?>" alt="Image de fond du héros">

</div>

<div class="all-filters">
    <div class="filter-container">
        <div class="tax">
            <label for="category-filter"></label>
            <select id="category-filter">
                <option value="all">Catégories</option>
                <!-- Les options seront ajoutées dynamiquement via JavaScript -->
            </select>

            <label for="format-filter"></label>
            <select id="format-filter">
                <option value="all">Formats</option>
                <!-- Les options seront ajoutées dynamiquement via JavaScript -->
            </select>
        </div>

        <div class="asc-desc">
            <label for="date-filter"></label>
            <select id="date-filter">
                <option value="all">Trier par</option>
                <option value="desc">À partir des plus récentes</option>
                <option value="asc">À partir des plus anciennes</option>
            <!-- Les options seront ajoutées dynamiquement via JavaScript -->
        </select>
        </div>
    </div>
</div>
    <div id="photo-container"></div>
    <div class="load-more-div">
    <button id="load-more-button">Charger plus</button>
    </div>
</main>

<?php
get_footer(); // Inclut le fichier footer.php
?>