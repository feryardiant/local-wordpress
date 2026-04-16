<?php

namespace CT_WPCF7;

use DateTimeImmutable;
use WP_Post;
use WP_User;
use WPCF7_ContactForm;

final class Submission_Item {
	public readonly int $id;
	public readonly int $parent_id;
	public readonly int $author_id;
	public readonly int $read_status;
	public readonly string $title;
	public readonly string $message;
	public readonly ?DateTimeImmutable $datetime;

	public static function set_read_status( int $id, bool $read ): int {
		return update_post_meta( $id, '_ct_submission_read', $read ? 1 : 0 );
	}

	public static function store( Submission_Option $option, WPCF7_ContactForm $form ) {
		$form_data = $option->form_data();

		$returned_id = wp_insert_post( array(
			'post_type' => 'form-submissions',
			'post_status' => 'publish',
			'post_title' => $option->subject ?? sprintf(
				/* translators: %s: Contact form title */
				__( 'Submission for "%s"', 'custom-theme' ),
				$form->title()
			),
			'post_parent' => $form->id(),
			'post_author' => self::store_author( $option ),
			'post_excerpt' => $option->message ?? null,
			// 'post_content' => null,
		) );

		if ( ! is_wp_error( $returned_id ) ) {
			foreach ( $form_data as $field => $value ) {
				add_post_meta( $returned_id, $field, $value );
			}

			add_post_meta( $returned_id, '_ct_submission_read', 0 );
		}

		return $returned_id;
	}

	private static function store_author( Submission_Option $option ): int {
		$could_store = ( $option->email && is_email( $option->email ) ) && $option->name;

		if ( ! $option->store_author || ! $could_store ) {
			return 0;
		}

		if ( email_exists( $option->email ) ) {
			$user = WP_User::get_data_by( 'email', $option->email );

			add_user_meta( $user->ID, 'user_phone', $option->phone ?? '' );

			return (int) $user->ID;
		}

		list( $login ) = explode( '@', $option->email );

		$login = sanitize_user( $login );

		if ( username_exists( $login ) ) {
			$user = WP_User::get_data_by( 'login', $login );

			add_user_meta( $user->ID, 'user_phone', $option->phone ?? '' );

			return (int) $user->ID;
		}

		$user_data = array(
			'user_login' => $login,
			'user_email' => $option->email,
			'display_name' => $option->name,
			'user_pass' => wp_generate_password( 12, true ),
		);

		$name_parts = explode( ' ', $option->name );

		if ( count( $name_parts ) > 1 ) {
			$user_data['first_name'] = $name_parts[0];
			$user_data['last_name'] = implode( ' ', array_slice( $name_parts, 1 ) );
		}

		$user_id = wp_insert_user( $user_data );

		if ( ! $user_id || is_wp_error( $user_id ) ) {
			return 0;
		}

		add_user_meta( $user_id, 'user_phone', $option->phone ?? '' );

		return (int) $user_id;
	}

	public function __construct( WP_Post|int $item ) {
		if ( is_int( $item ) ) {
			$item = get_post( $item );
		}

		$this->id = $item->ID;
		$this->title = $item->post_title;
		$this->parent_id = $item->post_parent;
		$this->author_id = $item->post_author;
		$this->message = $item->post_excerpt;
		$this->datetime = get_post_datetime( $item->ID ) ?: null;
		$this->read_status = (int) get_post_meta( $this->id, '_ct_submission_read', true );
	}

	public function form(): ?WP_Post {
		return $this->parent_id ? get_post( $this->parent_id ) : null;
	}

	public function author(): ?WP_User {
		if ( ! $this->author_id ) {
			return null;
		}

		return get_userdata( $this->author_id ) ?: null;
	}

	public function mark_read() {
		return Submission_Item::set_read_status( $this->id, true );
	}

	public function mark_unread() {
		return Submission_Item::set_read_status( $this->id, false );
	}

	public function is_read(): bool {
		return $this->read_status === 1;
	}

	public function is_unread(): bool {
		return $this->read_status === 0;
	}

	/**
	 * @param 'view'|'read' $action
	 * @return string
	 */
	public function url( string $action = 'view', ?string $nonce_key = null ) {
		$link = ct_wpcf7_admin_url( array(
			'post' => $this->id,
			'action' => $action,
		) );

		if ( $nonce_key ) {
			return wp_nonce_url( $link, $nonce_key . $this->id );
		}

		return esc_url( $link );
	}
}
