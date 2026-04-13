<?php

add_action( 'wp_enqueue_scripts', 'custom_theme_scripts' );

function custom_theme_scripts() {
	$theme = wp_get_theme( get_stylesheet() );

	wp_register_script( $theme->stylesheet, get_stylesheet_directory_uri() . '/custom.js', [], $theme->version, ['defer'] );

	wp_enqueue_script( $theme->stylesheet );
}

add_action( 'phpmailer_init', 'custom_theme_mailer_init' );

function custom_theme_mailer_init( WP_PHPMailer $mailer ) {
	$mailer->Host = getenv_docker('SMTP_HOST', 'mail');
	$mailer->Port = (int) getenv_docker('SMTP_PORT', 1025);
	$mailer->Username = getenv_docker('SMTP_USER', '');
	$mailer->Password = getenv_docker('SMTP_PASS', '');

	$mailer->isSMTP();
}
