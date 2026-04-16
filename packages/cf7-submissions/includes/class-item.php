<?php
/**
 * @package feryardiant/contact-form-7-submissions
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 */

namespace WPCF7S;

use DateTimeImmutable;
use WP_Error;
use WP_Post;
use WP_User;
use WPCF7_ContactForm;

final class Item {
	/**
	 * Submission Item ID.
	 */
	public readonly ?int $id;

	/**
	 * Submission Form ID.
	 */
	public readonly ?int $parent_id;

	/**
	 * Submission Author ID.
	 */
	public readonly ?int $author_id;

	/**
	 * Submission read status.
	 * @var 0|1
	 */
	public readonly int $read_status;

	/**
	 * Submission title.
	 */
	public readonly ?string $title;

	/**
	 * Submission message.
	 */
	public readonly ?string $message;

	/**
	 * Submission datetime.
	 */
	public readonly ?DateTimeImmutable $datetime;

	/**
	 * Set the read status for a submission item.
	 *
	 * @param int $id
	 * @param bool $read
	 */
	public static function set_read_status( ?int $id, bool $read ): int|false {
		return \update_post_meta( $id, '_wpcf7s_read_status', $read ? 1 : 0 );
	}

	/**
	 * Store a submission for the given form and submission option.
	 *
	 * @param WPCF7_ContactForm $form
	 * @param Option $option
	 */
	public static function store( WPCF7_ContactForm $form, Option $option ) {
		$form_data = $option->form_data();

		$returned_id = \wp_insert_post( array(
			'post_type' => 'form-submissions',
			'post_status' => 'publish',
			'post_title' => $option->subject ?? sprintf(
				/* translators: %s: Contact form title */
				__( 'Submission for "%s"', 'wpcf7-submissions' ),
				$form->title()
			),
			'post_parent' => $form->id(),
			'post_author' => self::store_author( $option ),
			'post_excerpt' => $option->message,
			// 'post_content' => null,
		) );

		if ( ! \is_wp_error( $returned_id ) ) {
			foreach ( $form_data as $field => $value ) {
				\add_post_meta( $returned_id, $field, $value );
			}

			\add_post_meta( $returned_id, '_wpcf7s_read_status', 0 );
		}

		return $returned_id;
	}

	private static function store_author( Option $option ): int {
		$could_store = ( $option->email && \is_email( $option->email ) ) && $option->name;

		if ( ! $option->store_author || ! $could_store ) {
			return 0;
		}

		if ( \email_exists( $option->email ) ) {
			$user = WP_User::get_data_by( 'email', $option->email );

			\update_user_meta( $user->ID, 'user_phone', $option->phone ?? '' );

			return (int) $user->ID;
		}

		list( $login ) = explode( '@', $option->email );

		$login = \sanitize_user( $login );

		if ( \username_exists( $login ) ) {
			$user = WP_User::get_data_by( 'login', $login );

			\update_user_meta( $user->ID, 'user_phone', $option->phone ?? '' );

			return (int) $user->ID;
		}

		$user_data = array(
			'user_login' => $login,
			'user_email' => $option->email,
			'display_name' => $option->name,
			'user_pass' => \wp_generate_password( 12, true ),
		);

		$name_parts = explode( ' ', $option->name );

		if ( count( $name_parts ) > 1 ) {
			$user_data['first_name'] = $name_parts[0];
			$user_data['last_name'] = implode( ' ', array_slice( $name_parts, 1 ) );
		}

		$user_id = \wp_insert_user( $user_data );

		if ( ! $user_id || \is_wp_error( $user_id ) ) {
			return 0;
		}

		\update_user_meta( $user_id, 'user_phone', $option->phone ?? '' );

		return (int) $user_id;
	}

	public function __construct( WP_Post|int $item ) {
		if ( is_int( $item ) ) {
			$item = \get_post( $item );
		}

		$this->id = $item?->ID;
		$this->title = $item?->post_title;
		$this->parent_id = $item?->post_parent;
		$this->author_id = $item?->post_author;
		$this->message = $item?->post_excerpt;
		$this->datetime = \get_post_datetime( $item?->ID ?? null ) ?: null;
		$this->read_status = (int) \get_post_meta( $this->id, '_wpcf7s_read_status', true );
	}

	/**
	 * Get the form post for this submission item.
	 */
	public function form(): ?WP_Post {
		return $this->parent_id ? \get_post( $this->parent_id ) : null;
	}

	/**
	 * Get the author for this submission item.
	 */
	public function author(): ?WP_User {
		if ( ! $this->author_id ) {
			return null;
		}

		return \get_userdata( $this->author_id ) ?: null;
	}

	/**
	 * Mark this submission item as read.
	 */
	public function mark_read() {
		return Item::set_read_status( $this->id, true );
	}

	/**
	 * Mark this submission item as unread.
	 */
	public function mark_unread() {
		return Item::set_read_status( $this->id, false );
	}

	/**
	 * Check if this submission item is read.
	 */
	public function is_read(): bool {
		return $this->read_status === 1;
	}

	/**
	 * Check if this submission item is unread.
	 */
	public function is_unread(): bool {
		return $this->read_status === 0;
	}

	/**
	 * @param 'view'|'read' $action
	 * @return string
	 */
	public function url( string $action = 'view', ?string $nonce_key = null ) {
		$link = admin_menu_url( array(
			'post' => $this->id,
			'action' => $action,
		) );

		if ( $nonce_key ) {
			return \wp_nonce_url( $link, $nonce_key . $this->id );
		}

		return \esc_url( $link );
	}
}
