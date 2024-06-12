<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body>
    <footer>
        <div class="footer-content">
            <ul class="footer-links">
            <li><a href="<?php echo esc_url( get_permalink( 'ID_Mentions_Légales' ) ); ?>">Mentions Légales</a></li>
            <li><a href="<?php echo esc_url( get_permalink( 'ID_Vie_Privée' ) ); ?>">Vie Privée</a></li>
            <li>&copy; <?php echo date('Y'); ?> VotreSite.com - Tous droits réservés</li>
            </ul>
        </div>
    </footer>
    
       <!-- Inclusion de la modale -->
       <?php get_template_part( 'template-parts/modal' ); ?>

    <?php wp_footer(); ?>
</body>
</html>
