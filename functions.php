<?php 
// Fonction pour enregistrer et enqueuer les styles et scripts
function mon_theme_enqueue_scripts() {
    // Enqueue le fichier CSS principal
    wp_enqueue_style( 'mon-theme-style', get_template_directory_uri() . '/style.css' );

    // Enqueue le fichier JS principal
   // wp_enqueue_script( 'mon-theme-script', get_template_directory_uri() . '/js/main.js', array('jquery'), '1.0', true );

    // Enqueue le fichier modal.js
    wp_enqueue_script( 'modal-script', get_template_directory_uri() . '/js/modal.js', array(), '1.0', true );

    // Enqueue le fichier singlePicture.js
    wp_enqueue_script( 'singlePicture-script', get_template_directory_uri() . '/js/singlePicture.js', array(), '1.0', true );

    //Enqueue le fichier motaphoto.js
    wp_enqueue_script( 'motaphoto-script', get_template_directory_uri() . '/js/motaphoto.js', array(), '1.0', true );

    // wp_localize_script est une fonction WordPress utilisée pour rendre les variables JavaScript accessibles dans les scripts front-end.
    wp_localize_script( 'motaphoto-script', 'motaphoto_js', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'site_url' => get_site_url() // Ajout de site_url ici
    ));

}
add_action( 'wp_enqueue_scripts', 'mon_theme_enqueue_scripts' );

//AJOUT DE FONTAWESOME
//function enqueue_fontawesome() {
 //   wp_enqueue_style( 'fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css' );
//}
//add_action( 'wp_enqueue_scripts', 'enqueue_fontawesome' );


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
                'slug' => get_post_field('post_name', get_the_ID()), // Assurez-vous d'ajouter le slug ici
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

//enregistrement du template de la page d'infos des photos
add_filter('template_include', 'custom_single_picture_template');

function custom_single_picture_template($template) {
    if (is_singular('picture')) {
        $new_template = locate_template(array('template-parts/single-picture.php'));
        if (!empty($new_template)) {
            return $new_template;
        }
    }
    return $template;
}
// Fonction pour créer le type de publication personnalisé 'picture'
function create_picture_post_type() {
    register_post_type('picture',
        array(
            'labels' => array(
                'name' => __('Photos'),
                'singular_name' => __('Photo')
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor', 'thumbnail'),
            'rewrite' => array('slug' => 'pictures'),
        )
    );
}
add_action('init', 'create_picture_post_type');

/***********Fonction pour la Page d'infos d'une photo **********/

// Fonction pour gérer la requête AJAX et récupérer les informations de la photo
function motaphoto_request_single_picture() {
    // Récupère l'ID du post depuis la requête AJAX
    $post_id = intval($_GET['post_id']);
    // Récupère le post à partir de l'ID
    $post = get_post($post_id);

    if ($post) {
        // Récupération des formats associés à la photo
        $formats = get_the_terms($post_id, 'format');
        $format_list = $formats ? join(', ', wp_list_pluck($formats, 'name')) : 'Aucun format';

        // Récupération des catégories associées à la photo
        $categories = get_the_terms($post_id, 'categorie');
        $category_list = $categories ? join(', ', wp_list_pluck($categories, 'name')) : 'Aucune catégorie';

        // Récupération de la référence de la photo (champ personnalisé)
        $reference = get_post_meta($post_id, 'reference', true);

        // Récupération du type de photo (champ personnalisé)
        $type = get_post_meta($post_id, 'type', true);

        // Récupération de l'année de publication de la photo
        $year = get_the_date('Y', $post_id);

        // Contenu HTML à retourner
        $html = '<h1>' . esc_html($post->post_title) . '</h1>';
        $html .= '<img src="' . get_the_post_thumbnail_url($post_id, 'full') . '" alt="' . esc_attr($post->post_title) . '">';
        $html .= '<p>' . esc_html($post->post_content) . '</p>';
        $html .= '<p><strong>Format :</strong> ' . esc_html($format_list) . '</p>';
        $html .= '<p><strong>Catégorie :</strong> ' . esc_html($category_list) . '</p>';
        $html .= '<p><strong>Référence :</strong> ' . esc_html($reference) . '</p>';
        $html .= '<p><strong>Type :</strong> ' . esc_html($type) . '</p>';
        $html .= '<p><strong>Année :</strong> ' . esc_html($year) . '</p>';

        echo $html;
    }

    wp_die(); // Termine l'exécution du script proprement
}

// Enregistre les actions AJAX pour les utilisateurs connectés et non connectés
add_action('wp_ajax_request_single_picture', 'motaphoto_request_single_picture');
add_action('wp_ajax_nopriv_request_single_picture', 'motaphoto_request_single_picture');
?>