<?php

namespace CT_WPCF7;

use DateTimeImmutable;
use WP_Post;
use WP_User;

final class Submission_Item {
	public readonly int $id;
	public readonly int $parent_id;
	public readonly int $author_id;
	public readonly int $read_status;
	public readonly string $title;
	public readonly string $message;
	public readonly ?DateTimeImmutable $datetime;

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

	public function is_read(): bool {
		return $this->read_status === 1;
	}

	public function is_unread(): bool {
		return $this->read_status === 0;
	}

	public static function mark_read( int $id ): int {
		return update_post_meta( $id, '_ct_submission_read', 1 );
	}

	/**
	 * @param 'view'|'read' $action
	 * @return string
	 */
	public function url( string $action = 'view', ?string $nonce_key = null ) {
		$link = add_query_arg( array(
			'post' => $this->id,
			'action' => $action,
		), menu_page_url( 'ct-wpcf7-submissions', false ) );

		if ( $nonce_key ) {
			return wp_nonce_url( $link, $nonce_key . $this->id );
		}

		return esc_url( $link );
	}
}
