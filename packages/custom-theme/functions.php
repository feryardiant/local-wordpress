<?php

add_action( 'wp_enqueue_scripts', 'custom_theme_scripts' );

function custom_theme_scripts() {
	$theme = wp_get_theme( get_stylesheet() );

	wp_register_script( $theme->stylesheet, get_stylesheet_directory_uri() . '/custom.js', [], $theme->version, ['defer'] );

	wp_enqueue_script( $theme->stylesheet );
}

add_action( 'switch_theme', 'custom_theme_deactivation', 10, 0 );
add_action( 'after_switch_theme', 'custom_theme_activation', 10, 0 );

/**
 * Trigger custom theme activation hook.
 */
function custom_theme_activation() {
	do_action( 'ct_activation' );
}

/**
 * Trigger custom theme deactivation hook.
 */
function custom_theme_deactivation() {
	do_action( 'ct_deactivation' );
}
