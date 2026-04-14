<?php

add_action( 'init', static function() {
	add_filter( 'user_contactmethods', 'custom_theme_user_contact_methods', 10, 1 );
} );

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

require_once get_stylesheet_directory() . '/integrations/contact-form-7.php';

add_action( 'switch_theme', 'custom_theme_deactivation', 10, 0 );
add_action( 'after_switch_theme', 'custom_theme_activation', 10, 0 );

function custom_theme_activation() {
	do_action( 'ct_activation' );
}

function custom_theme_deactivation() {
	do_action( 'ct_deactivation' );
}

function custom_theme_user_contact_methods( $methods ) {
	unset(
		$methods['dribbble'],
		$methods['pinterest'],
		$methods['vkontakte'],
		$methods['vimeo'],
		$methods['odnoklassniki'],
	);

	return array_merge( array(
		'user_phone' => __( 'Phone Number', 'custom-theme' )
	), $methods );
}
