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
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'site_url' => get_site_url() // Ajout de site_url ici
    ));

}
add_action( 'wp_enqueue_scripts', 'mon_theme_enqueue_scripts' );

//AJOUT DE FONTAWESOME
function enqueue_fontawesome() {
    wp_enqueue_style( 'fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_fontawesome' );


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

// Enregistrement de la taxonomie personnalisée 'categorie' pour le type de publication 'picture'
function register_custom_taxonomy() {
    register_taxonomy(
        'categorie',  // Nom de la taxonomie (doit correspondre au slug de la taxonomie créée avec CPT UI)
        'picture',    // Nom du type de publication personnalisé associé
        array(
            'label' => __( 'Catégories' ),
            'rewrite' => array( 'slug' => 'photo-category' ), // Slug de la taxonomie
            'hierarchical' => true,  // Taxonomie hiérarchique comme les catégories normales de WordPress
        )
    );
}
add_action( 'init', 'register_custom_taxonomy' );

/*********Fonction pour afficher les photos **********/ 
function motaphoto_request_picture() {
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : 'all'; // Récupère la catégorie sélectionnée

    $args = array(
        'post_type' => 'picture',
        'posts_per_page' => 8,
        'offset' => $offset,
    );

    // Ajouter le filtre par catégorie si ce n'est pas "all"
    if ($category !== 'all') {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'categorie',
                'field' => 'slug',
                'terms' => $category,
            ),
        );
    }

    $query = new WP_Query($args);

    $posts = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            // Récupération des catégories associées à la photo
            $categories = get_the_terms(get_the_ID(), 'categorie');
            $category_names = $categories ? wp_list_pluck($categories, 'name') : ['Unknown Category'];

            // Récupération des champs ACF
            $reference = get_field('reference', get_the_ID());

            $posts[] = [
                'title' => get_the_title(),
                'slug' => get_post_field('post_name', get_the_ID()),
                'featured_image' => get_the_post_thumbnail_url(get_the_ID(), 'full'),
                'category' => join(', ', $category_names),
                'reference' => $reference,
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


// Ajouter une action pour gérer la requête AJAX
add_action('wp_ajax_load_categories', 'load_categories_callback');
add_action('wp_ajax_nopriv_load_categories', 'load_categories_callback'); // Si l'utilisateur n'est pas connecté

function load_categories_callback() {
    $categories = get_terms(array(
        'taxonomy' => 'categorie', // Remplace 'categorie' par le nom de ta taxonomie
        'hide_empty' => true, // Cacher les catégories sans articles
    ));

    $response = array();
    foreach ($categories as $category) {
        $response[] = array(
            'slug' => $category->slug,
            'name' => $category->name,
        );
    }

    wp_send_json($response);
}

//mots clé de recherche:  wordpress chargement ajax taxionomie/acf
//pour l'affichage:je créer mes 3 champs en html , ensuite je les rempli dynamiquement comme pour les catégories le formats mais pour la date je l'écrit directement dans le html
//ordre ascendant ou descendant ajax
//ensuite je recupere la valeur de ses champs quand je les modifie ( par defaut tous) faire un console.log pour verifier que les categories ou formats sont bien récupérées
//je rempli mes critère par mes mots clés
?>

