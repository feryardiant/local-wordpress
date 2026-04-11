<?php

add_action( 'wp_enqueue_scripts', 'custom_theme_scripts' );

function custom_theme_scripts() {
    $theme = wp_get_theme( get_stylesheet() );

    wp_register_script( $theme->stylesheet, get_stylesheet_directory_uri() . '/custom.js', [], $theme->version, ['defer'] );

    wp_enqueue_script( $theme->stylesheet );
}
