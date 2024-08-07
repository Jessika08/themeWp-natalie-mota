<?php

// Fonction pour enregistrer et enqueuer les styles et scripts
function mon_theme_enqueue_scripts() {
    // Enqueue le fichier CSS principal
    wp_enqueue_style( 'mon-theme-style', get_template_directory_uri() . '/style.css' );

    // Enqueue le fichier JS principal
    // wp_enqueue_script( 'mon-theme-script', get_template_directory_uri() . '/js/main.js', array('jquery'), '1.0', true );

    // Enqueue le fichier modal.js
    wp_enqueue_script( 'modal-script', get_template_directory_uri() . '/js/modal.js', array(), '1.0', true );

    // Enqueue le fichier motaphoto.js
    wp_enqueue_script( 'motaphoto-script', get_template_directory_uri() . '/js/motaphoto.js', array(), '1.0', true );

    // Enqueue FontAwesome
    wp_enqueue_style( 'fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css' );

    // Enqueue les typos personnalisées
    wp_enqueue_style('custom-fonts', get_template_directory_uri() . '/fonts/custom-fonts.css');

    // wp_localize_script est une fonction WordPress utilisée pour rendre les variables JavaScript accessibles dans les scripts front-end.
    wp_localize_script( 'motaphoto-script', 'motaphoto_js', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'site_url' => get_site_url() // Ajout de site_url ici
    ));
}
add_action( 'wp_enqueue_scripts', 'mon_theme_enqueue_scripts' );

// Enregistrement du menu principal
function enregistrer_menus() {
    register_nav_menus(
        array(
            'principal' => __( 'Menu Principal' ),
        )
    );
}
add_action( 'init', 'enregistrer_menus' );

// Ajout de la classe 'open-modal' au lien de menu 'Contact'
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

    // Enregistrement de la taxonomie personnalisée 'format' pour le type de publication 'picture'
    register_taxonomy(
        'format',  // Nom de la taxonomie
        'picture', // Nom du type de publication personnalisé associé
        array(
            'label' => __( 'Formats' ),
            'rewrite' => array( 'slug' => 'photo-format' ), // Slug de la taxonomie
            'hierarchical' => false,  // Taxonomie non hiérarchique comme les tags
        )
    );
}
add_action( 'init', 'register_custom_taxonomy' );

/********* Fonction pour afficher les photos **********/
function motaphoto_request_picture() {
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : 'all'; // Récupère la catégorie sélectionnée
    $format = isset($_GET['format']) ? sanitize_text_field($_GET['format']) : 'all'; // Récupère le format sélectionné

    $args = array(
        'post_type' => 'picture',
        'posts_per_page' => 8,
        'offset' => $offset,
    );

    // Ajouter le filtre par catégorie si ce n'est pas "all"
    if ($category !== 'all') {
        $args['tax_query'][] = array(
            'taxonomy' => 'categorie',
            'field' => 'slug',
            'terms' => $category,
        );
    }

    // Ajouter le filtre par format si ce n'est pas "all"
    if ($format !== 'all') {
        $args['tax_query'][] = array(
            'taxonomy' => 'format',
            'field' => 'slug',
            'terms' => $format,
        );
    }

    // Ajout de l'ordre de tri par titre ou date
    $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'date';
    $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

    if ($orderby === 'title') {
        $args['orderby'] = 'title';
    } else {
        $args['orderby'] = 'date';
    }

    $args['order'] = strtoupper($order); // Convertit en majuscules pour DESC ou ASC

    $query = new WP_Query($args);

    $posts = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            // Récupération des catégories associées à la photo
            $categories = get_the_terms(get_the_ID(), 'categorie');
            $category_names = $categories ? wp_list_pluck($categories, 'name') : ['Unknown Category'];

            // Récupération des formats associés à la photo
            $formats = get_the_terms(get_the_ID(), 'format');
            $format_names = $formats ? wp_list_pluck($formats, 'name') : ['Unknown Format'];

            // Récupération des champs ACF
            $reference = get_field('reference', get_the_ID());

            $posts[] = [
                'title' => get_the_title(),
                'slug' => get_post_field('post_name', get_the_ID()),
                'featured_image' => get_the_post_thumbnail_url(get_the_ID(), 'full'),
                'category' => join(', ', $category_names),
                'format' => join(', ', $format_names),
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

// Enregistrement du template de la page d'infos des photos
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

/*********** Fonction pour la Page d'infos d'une photo **********/

// Fonction pour gérer la requête AJAX et récupérer les informations de la photo
function motaphoto_request_single_picture() {
    // Récupère l'ID du post depuis la requête AJAX
    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

    if ($post_id > 0) {
        // Récupère les informations de la photo
        $title = get_the_title($post_id);
        $content = apply_filters('the_content', get_post_field('post_content', $post_id));
        $image = get_the_post_thumbnail_url($post_id, 'full');

        // Récupère les catégories de la photo
        $categories = get_the_terms($post_id, 'categorie');
        $category_names = $categories ? wp_list_pluck($categories, 'name') : ['Unknown Category'];

        // Récupère les formats de la photo
        $formats = get_the_terms($post_id, 'format');
        $format_names = $formats ? wp_list_pluck($formats, 'name') : ['Unknown Format'];

        // Récupère le champ ACF 'reference' de la photo
        $reference = get_field('reference', $post_id);

        // Construit le tableau de données à renvoyer
        $data = [
            'title' => $title,
            'content' => $content,
            'image' => $image,
            'categories' => join(', ', $category_names),
            'formats' => join(', ', $format_names),
            'reference' => $reference,
        ];

        wp_send_json($data);
    } else {
        wp_send_json(false);
    }

    wp_die();
}
add_action('wp_ajax_single_picture_info', 'motaphoto_request_single_picture');
add_action('wp_ajax_nopriv_single_picture_info', 'motaphoto_request_single_picture');

// Ajouter une action pour gérer la requête AJAX des catégories
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
add_action('wp_ajax_load_categories', 'load_categories_callback');
add_action('wp_ajax_nopriv_load_categories', 'load_categories_callback'); // Si l'utilisateur n'est pas connecté

// Ajouter une action pour gérer la requête AJAX des formats
function load_formats_callback() {
    $formats = get_terms(array(
        'taxonomy' => 'format', // Remplace 'format' par le nom de ta taxonomie
        'hide_empty' => true, // Cacher les formats sans articles
    ));

    $response = array();
    foreach ($formats as $format) {
        $response[] = array(
            'slug' => $format->slug,
            'name' => $format->name,
        );
    }

    wp_send_json($response);
}
add_action('wp_ajax_load_formats', 'load_formats_callback');
add_action('wp_ajax_nopriv_load_formats', 'load_formats_callback');

// Ajouter une action pour gérer la requête AJAX des photos
function request_picture_callback() {
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : 'all';
    $format = isset($_GET['format']) ? sanitize_text_field($_GET['format']) : 'all';
    $per_page = 8; // Nombre de photos à charger à chaque fois

    $args = array(
        'post_type' => 'picture', // Correction de 'photo' à 'picture'
        'posts_per_page' => $per_page,
        'offset' => $offset,
    );

    if ($category !== 'all') {
        $args['tax_query'][] = array(
            'taxonomy' => 'categorie',
            'field'    => 'slug',
            'terms'    => $category,
        );
    }

    if ($format !== 'all') {
        $args['tax_query'][] = array(
            'taxonomy' => 'format',
            'field'    => 'slug',
            'terms'    => $format,
        );
    }

    $query = new WP_Query($args);

    $posts = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $posts[] = array(
                'title'         => get_the_title(),
                'slug'          => get_post_field('post_name', get_post()),
                'featured_image'=> get_the_post_thumbnail_url(),
                'category'      => wp_get_post_terms(get_the_ID(), 'categorie', array("fields" => "names"))[0],
                'format'        => wp_get_post_terms(get_the_ID(), 'format', array("fields" => "names"))[0],
                'reference'     => get_post_meta(get_the_ID(), 'reference', true), // Assuming you have a custom field 'reference'
            );
        }
        wp_reset_postdata();
    }

    wp_send_json(array('posts' => $posts));
}
add_action('wp_ajax_request_picture', 'request_picture_callback');
add_action('wp_ajax_nopriv_request_picture', 'request_picture_callback'); // Si l'utilisateur n'est pas connecté

?>
