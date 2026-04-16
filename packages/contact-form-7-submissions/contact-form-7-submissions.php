<?php
/**
 * Plugin Name: Contact Form 7 Submissions
 * Plugin URI: https://feryardiant.id
 * Text Domain: wpcf7-submissions
 * Description: Save and manage Contact Form 7 submissions. Never lose important data. Contact Form 7 Submissions plugin is an add-on for the Contact Form 7 plugin.
 * Author: Fery Wardiyanto
 * Version: 0.0.0
 * Author URI: https://feryardiant.id
 * License: GPLv3 or later
 * Requires at least: 6.8
 * Requires PHP: 7.4
 * Requires Plugins: contact-form-7
 *
 * @package feryardiant/contact-form-7-submissions
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 */

define( 'WPCF7S_VERSION', '0.0.0' );
define( 'WPCF7S__MINIMUM_WP_VERSION', '6.8' );
define( 'WPCF7S__MINIMUM_PHP_VERSION', '7.4' );

/**
 * Check if the version of WordPress in use on the site is supported by Jetpack.
 */
if ( version_compare( $GLOBALS['wp_version'], WPCF7S__MINIMUM_WP_VERSION, '<' ) ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			sprintf(
				/* translators: Placeholders are numbers, versions of WordPress in use on the site, and required by WordPress. */
				esc_html__( 'Your version of WordPress (%1$s) is lower than the version required by Contact Form 7 Submissions (%2$s). Please update WordPress to continue enjoying Contact Form 7 Submissions.', 'wpcf7-submissions' ),
				$GLOBALS['wp_version'],
				WPCF7S__MINIMUM_WP_VERSION
			)
		);
	}

	add_action( 'admin_notices', static function () {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'Contact Form 7 Submissions requires a more recent version of WordPress and has been paused. Please update WordPress to continue enjoying Contact Form 7 Submissions.', 'wpcf7-submissions' ); ?></p>
		</div>
		<?php
	} );

	return;
}

register_activation_hook( __FILE__, static function() {
	// Doing nothing on activation
} );

register_deactivation_hook( __FILE__, static function() {
	// Doing nothing on deactivation
} );

add_action( 'wpcf7_init', static function() {
	require_once __DIR__ . '/includes/admin.php';

	require_once __DIR__ . '/includes/class-item.php';
	require_once __DIR__ . '/includes/class-list_table.php';
	require_once __DIR__ . '/includes/class-option.php';
} );

add_action( 'init', static function() {
	$post_type = 'form-submissions';

	$labels = array(
		'name' => __( 'Submissions', 'wpcf7-submissions' ),
		'singular_name' => __( 'Submission', 'wpcf7-submissions' ),
		'view_item' => __( 'View Submission', 'wpcf7-submissions' ),
		'search_items' => __( 'Search Submissions', 'wpcf7-submissions' ),
		'not_found' => __( 'No submissions found.', 'wpcf7-submissions' ),
		'not_found_in_trash' => __( 'No submissions found in Trash.', 'wpcf7-submissions' ),
		'filter_items_list' => _x( 'Filter submissions list', 'Screen reader text for the filter links heading on the post type listing screen.', 'wpcf7-submissions' ),
		'items_list_navigation' => _x( 'Submissions list navigation', 'Screen reader text for the pagination heading on the post type listing screen.', 'wpcf7-submissions' ),
		'items_list' => _x( 'Submissions list', 'Screen reader text for the items list heading on the post type listing screen.', 'wpcf7-submissions' ),
	);

	register_post_type( $post_type, array(
		'labels' => $labels,
		'description' => 'List of form submissions.',
		'public' => false,
		'show_ui' => false,
		'show_in_nav_menus' => false,
		'show_in_admin_bar' => false,
		'capability_type' => 'post',
		'hierarchical' => false,
		'supports' => [ 'title', 'excerpt', 'author', 'custom-fields' ],
		'rewrite' => array( 'slug' => 'submission' ),
		'query_var' => true,
		'menu_icon' => 'dashicons-email-alt',
		'register_meta_box_cb' => static function( WP_Post $post ) {
			// Doing nothing for now.
		},
	) );

	/**
	 * Override user contact meta properties.
	 */
	add_filter(
		'user_contactmethods',
		static fn ( array $methods ) => array_merge( array(
			'user_phone' => __( 'Phone Number', 'wpcf7-submissions' )
		), $methods ),
		10, 1
	);
} );

/**
 * Configure PHPMailer SMTP driver for local development.
 */
add_action(
	'phpmailer_init',
	static function ( WP_PHPMailer $mailer ) {
		if ( ! function_exists( 'getenv_docker' ) ) {
			return;
		}

		$mailer->Host = getenv_docker('SMTP_HOST', 'mail');
		$mailer->Port = (int) getenv_docker('SMTP_PORT', 1025);
		$mailer->Username = getenv_docker('SMTP_USER', '');
		$mailer->Password = getenv_docker('SMTP_PASS', '');

		$mailer->isSMTP();
	}
);
