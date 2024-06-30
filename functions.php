<?php 
// Fonction pour enregistrer et enqueuer les styles et scripts
function mon_theme_enqueue_scripts() {
    // Enqueue le fichier CSS principal
    wp_enqueue_style( 'mon-theme-style', get_template_directory_uri() . '/style.css' );

    // Enqueue le fichier JS principal
   // wp_enqueue_script( 'mon-theme-script', get_template_directory_uri() . '/js/main.js', array('jquery'), '1.0', true );

    // Enqueue le fichier modal.js
    wp_enqueue_script( 'modal-script', get_template_directory_uri() . '/js/modal.js', array(), '1.0', true );

    //Enqueue le fichier motaphoto.js
    wp_enqueue_script( 'motaphoto-script', get_template_directory_uri() . '/js/motaphoto.js', array(), '1.0', true );

    // wp_localize_script est une fonction WordPress utilisée pour rendre les variables JavaScript accessibles dans les scripts front-end.
    wp_localize_script( 'motaphoto-script', 'motaphoto_js', array(
        'ajax_url' => admin_url( 'admin-ajax.php' )
    ));

}
add_action( 'wp_enqueue_scripts', 'mon_theme_enqueue_scripts' );

//fonction pour ajouter mes typos
function enqueue_custom_fonts() {
    // Enqueue le fichier CSS contenant les déclarations @font-face
    wp_enqueue_style('custom-fonts', get_template_directory_uri() . '/fonts/custom-fonts.css');
}

add_action('wp_enqueue_scripts', 'enqueue_custom_fonts');

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

/*********Fonction pour afficher les photos **********/ 
function motaphoto_request_picture()  {

    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

    $query = new WP_Query([
        'post_type' => 'picture',// Utilisation de 'picture' comme slug du type de publication personnalisé
        'posts_per_page' => 8, // Nombre de photos par page
        'offset' => $offset // Offset pour charger plus de photos
    ]);

    $posts = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $posts[] = [
                'title' => get_the_title(),
                'featured_image' => get_the_post_thumbnail_url(get_the_ID(), 'full')
            ];
        }
        wp_reset_postdata();
        wp_send_json(['posts' => $posts]);
    } else {
        wp_send_json(false);
    }

    wp_die();
}

add_action('wp_ajax_request_picture', 'motaphoto_request_picture');
add_action('wp_ajax_nopriv_request_picture', 'motaphoto_request_picture');
?>