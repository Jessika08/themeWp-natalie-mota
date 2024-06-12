<?php 
// Fonction pour enregistrer et enqueuer les styles et scripts
function mon_theme_enqueue_scripts() {
    // Enqueue le fichier CSS principal
    wp_enqueue_style( 'mon-theme-style', get_template_directory_uri() . '/style.css' );

    // Enqueue le fichier JS principal
    wp_enqueue_script( 'mon-theme-script', get_template_directory_uri() . '/js/main.js', array('jquery'), '1.0', true );

    // Enqueue le fichier modal.js
    wp_enqueue_script( 'modal-script', get_template_directory_uri() . '/js/modal.js', array(), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'mon_theme_enqueue_scripts' );

//Fonction pour ajouter le menu
function enregistrer_menus() {
    register_nav_menus(
        array(
            'principal' => __( 'Menu Principal' ),
        )
    );
}
add_action( 'init', 'enregistrer_menus' );

function add_open_modal_class_to_contact_menu_item($classes, $item, $args) {
    if ($args->theme_location == 'principal' && $item->title == 'Contact') {
        $classes[] = 'open-modal';
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'add_open_modal_class_to_contact_menu_item', 10, 3);

?>