<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <header>
    <div class="site-logo">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo-nathalie.png" alt="<?php bloginfo( 'name' ); ?>">
            </a>
        </div>
        <nav class="menu-principal-container">
            <?php
            wp_nav_menu( array(
                'theme_location' => 'principal',
                'menu_id'        => 'menu-principal',
                'container'      => 'nav',
                
            ) );
            ?>
        </nav>
    </header>