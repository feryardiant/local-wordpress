<?php
/**
 * Entry Manager for Contact Form 7
 *
 * @package feryardiant/cf7-entry-manager
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * @wordpress-plugin
 * Plugin Name: Entry Manager for Contact Form 7
 * Description: Never lose a lead again. Save, manage, and convert every Contact Form 7 submission directly in your WordPress dashboard.
 * Text Domain: cf7-entry-manager
 * Version: 0.1.0
 * Tested up to: 6.9
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Author: Fery Wardiyanto
 * Author URI: https://feryardiant.id
 * License: GPLv3 or later
 * Requires Plugins: contact-form-7
 */

defined( 'ABSPATH' ) || exit;

define( 'CF7EM_VERSION', '0.1.0' );
define( 'CF7EM_DEBUG', defined( 'WP_DEBUG' ) && boolval( WP_DEBUG ) );
define( 'CF7EM__MINIMUM_WP_VERSION', '6.0' );
define( 'CF7EM__MINIMUM_PHP_VERSION', '8.1' );

/**
 * Check if the version of WordPress in use on the site is supported by Entry Manager for Contact Form 7.
 */
if ( version_compare( PHP_VERSION, CF7EM__MINIMUM_PHP_VERSION, '<' ) ) {
	if ( CF7EM_DEBUG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log(
			sprintf(
				/* translators: Placeholders are numbers, versions of PHP in use on the site, and required by Entry Manager for Contact Form 7 plugin. */
				esc_html__( 'Your version of PHP (%1$s) is lower than the version required by Entry Manager for Contact Form 7 (%2$s). Please update PHP to continue enjoying Entry Manager for Contact Form 7.', 'cf7-entry-manager' ),
				PHP_VERSION,
				CF7EM__MINIMUM_PHP_VERSION
			)
		);
	}

	add_action(
		'admin_notices',
		static function () {
			?>
		<div class="notice notice-error is-dismissible">
			<p>
			<?php
			esc_html_e(
				'Entry Manager for Contact Form 7 requires a more recent version of PHP and has been paused. Please update PHP to continue enjoying Entry Manager for Contact Form 7.',
				'cf7-entry-manager'
			);
			?>
			</p>
		</div>
			<?php
		}
	);

	return;
}

/**
 * Check if the version of WordPress in use on the site is supported by Entry Manager for Contact Form 7.
 */
if ( version_compare( $GLOBALS['wp_version'], CF7EM__MINIMUM_WP_VERSION, '<' ) ) {
	if ( CF7EM_DEBUG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log(
			sprintf(
				/* translators: Placeholders are numbers, versions of WordPress in use on the site, and required by Entry Manager for Contact Form 7 plugin. */
				esc_html__( 'Your version of WordPress (%1$s) is lower than the version required by Entry Manager for Contact Form 7 (%2$s). Please update WordPress to continue enjoying Entry Manager for Contact Form 7.', 'cf7-entry-manager' ),
				$GLOBALS['wp_version'],
				CF7EM__MINIMUM_WP_VERSION
			)
		);
	}

	add_action(
		'admin_notices',
		static function () {
			?>
		<div class="notice notice-error is-dismissible">
			<p>
			<?php
			esc_html_e(
				'Entry Manager for Contact Form 7 requires a more recent version of WordPress and has been paused. Please update WordPress to continue enjoying Entry Manager for Contact Form 7.',
				'cf7-entry-manager'
			);
			?>
				</p>
		</div>
			<?php
		}
	);

	return;
}

register_activation_hook(
	__FILE__,
	static function () {
		// Doing nothing on activation.
	}
);

register_deactivation_hook(
	__FILE__,
	static function () {
		// Doing nothing on deactivation.
	}
);

add_action(
	'admin_enqueue_scripts',
	static function ( string $hook_suffix ) {
		if ( ! in_array( $hook_suffix, array( 'toplevel_page_wpcf7', 'contact_page_cf7-entry-manager' ), true ) ) {
			return;
		}

		wp_enqueue_style( 'cf7-entry-manager-style', plugin_dir_url( __FILE__ ) . 'assets/style.css', array(), CF7EM_VERSION );
	},
	10,
	1
);

add_action(
	'wpcf7_init',
	static function () {
		require_once __DIR__ . '/includes/admin.php';

		require_once __DIR__ . '/includes/class-item.php';
		require_once __DIR__ . '/includes/class-page-element.php';
		require_once __DIR__ . '/includes/class-list-table.php';
		require_once __DIR__ . '/includes/class-option.php';
	}
);

add_action(
	'init',
	static function () {
		$post_type = 'form-submissions';

		$labels = array(
			'name'                  => __( 'Submissions', 'cf7-entry-manager' ),
			'singular_name'         => __( 'Submission', 'cf7-entry-manager' ),
			'view_item'             => __( 'View Submission', 'cf7-entry-manager' ),
			'search_items'          => __( 'Search Submissions', 'cf7-entry-manager' ),
			'not_found'             => __( 'No submissions found.', 'cf7-entry-manager' ),
			'not_found_in_trash'    => __( 'No submissions found in Trash.', 'cf7-entry-manager' ),
			'filter_items_list'     => _x( 'Filter submissions list', 'Screen reader text for the filter links heading on the post type listing screen.', 'cf7-entry-manager' ),
			'items_list_navigation' => _x( 'Submissions list navigation', 'Screen reader text for the pagination heading on the post type listing screen.', 'cf7-entry-manager' ),
			'items_list'            => _x( 'Submissions list', 'Screen reader text for the items list heading on the post type listing screen.', 'cf7-entry-manager' ),
		);

		register_post_type(
			$post_type,
			array(
				'labels'            => $labels,
				'description'       => 'List of form submissions.',
				'public'            => false,
				'show_ui'           => false,
				'show_in_nav_menus' => false,
				'show_in_admin_bar' => false,
				'capability_type'   => 'post',
				'hierarchical'      => false,
				'supports'          => array( 'title', 'excerpt', 'author', 'custom-fields' ),
				'rewrite'           => array( 'slug' => 'submission' ),
				'query_var'         => true,
				'menu_icon'         => 'dashicons-email-alt',
				// 'register_meta_box_cb' => static function( WP_Post $post ) {
				// Doing nothing for now.
				// },
			)
		);

		/**
		 * Override user contact meta properties.
		 */
		add_filter(
			'user_contactmethods',
			static fn ( array $methods ) => array_merge(
				array(
					'user_phone' => __( 'Phone Number', 'cf7-entry-manager' ),
				),
				$methods
			),
			10,
			1
		);
	}
);

/**
 * Configure PHPMailer SMTP driver for local development.
 */
add_action(
	'phpmailer_init',
	static function ( WP_PHPMailer $mailer ) {
		if ( ! function_exists( 'getenv_docker' ) ) {
			return;
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$mailer->Host     = getenv_docker( 'SMTP_HOST', 'mail' );
		$mailer->Port     = (int) getenv_docker( 'SMTP_PORT', 1025 );
		$mailer->Username = getenv_docker( 'SMTP_USER', '' );
		$mailer->Password = getenv_docker( 'SMTP_PASS', '' );
		// phpcs:enable

		$mailer->isSMTP();
	}
);
