<?php
/**
 * The template for displaying a single picture in a modal
 *
 * Template Name: Single Picture Template
 *
 */

get_header(); // Inclut le fichier header.php

if (have_posts()) : 
    while (have_posts()) : the_post(); 
        $post_id = get_the_ID();
        $formats = get_the_terms($post_id, 'format');
        $format_list = $formats ? join(', ', wp_list_pluck($formats, 'name')) : 'Aucun format';

        $categories = get_the_terms($post_id, 'categorie');
        $category_list = $categories ? join(', ', wp_list_pluck($categories, 'name')) : 'Aucune catégorie';

        $reference = get_post_meta($post_id, 'reference', true);
        $type = get_post_meta($post_id, 'type', true);
        $year = get_the_date('Y', $post_id);
        ?>
        
        <div class="photo-details">       
            <div class="left-bloc">
            <h1><?php echo esc_html(get_the_title()); ?></h1>
            <p class="unset"><?php echo esc_html(get_the_content()); ?></p>
            <p>Référence : <?php echo esc_html($reference); ?></p>
            <p>Catégorie : <?php echo esc_html($category_list); ?></p>
            <p>Format : <?php echo esc_html($format_list); ?></p>
            <p>Type : <?php echo esc_html($type); ?></p>
            <p>Année : <?php echo esc_html($year); ?></p>
            </div>

            <div class="right-bloc">
            <img src="<?php echo esc_url(get_the_post_thumbnail_url($post_id, 'full')); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
            </div>

        </div>

        <div class="navigation-wrapper">
                <div class="contact-single">
            <?php
                // Ajout du texte avant le bouton de contact
                echo '<p>Cette photo vous intéresse ?</p>';
            ?>

            <button id="contact-button" class="open-modal" data-reference="<?php echo esc_attr($reference); ?>">Contact</button>
            </div>

            <div class="nav-thumbnail">
                <div class="thumbnail">
                    <?php
                    $next_post = get_next_post();
                    if ($next_post) {
                        echo get_the_post_thumbnail($next_post->ID, 'thumbnail', ['class' => 'next-post-thumbnail', 'style' => 'width:100px;height:90px;object-fit:cover;overflow:hidden;']);
                    }
                    ?>
                </div>

                <div class="arrows">
                    <div class="prev"><?php previous_post_link('%link', '<i class="fas fa-arrow-left-long"></i>'); ?></div>
                    <div class="next"><?php next_post_link('%link', '<i class="fas fa-arrow-right-long"></i>'); ?></div>
                </div>
            </div>
        </div>
        
        

        <?php
        // Ajout des photos apparentées
        $current_categories = wp_get_post_terms($post_id, 'categorie', ['fields' => 'ids']);
        if ($current_categories) {
            $related_args = [
                'post_type' => 'picture',
                'posts_per_page' => 2,
                'orderby' => 'rand',
                'tax_query' => [
                    [
                        'taxonomy' => 'categorie',
                        'field' => 'term_id',
                        'terms' => $current_categories,
                    ],
                ],
                'post__not_in' => [$post_id],
            ];

            $related_query = new WP_Query($related_args);

            if ($related_query->have_posts()) {
                echo '<div class="container-related-photo">';
                echo '<p>Vous aimerez aussi</p>';
                echo '<div class="related-photo">';
                while ($related_query->have_posts()) {
                    $related_query->the_post();
                    ?>
                    <div class="photo-div">
                        <a href="<?php the_permalink(); ?>">
                            <img src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'large')); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                           
                        </a>
                    </div>
                    <?php
                }
                echo '</div>';
                echo '</div>';
                wp_reset_postdata();
            }
        }
    endwhile; 
endif;

get_footer(); // Inclut le fichier footer.php
?>

