<?php

namespace CT_WPCF7;

use WP_List_Table;
use WP_Query;
use WPCF7_ContactForm;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Submissions_List_Table extends WP_List_Table {
	public static function define_column( array $columns ) {
		return wp_parse_args( $columns, array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Subject', 'custom-theme' ),
			'form' => __( 'Form', 'custom-theme' ),
			'author' => __( 'Author', 'custom-theme' ),
			'date' => __( 'Date', 'custom-theme' ),
		) );
	}

	public function __construct(
		private ?WPCF7_ContactForm $contact_form = null,
	) {
		parent::__construct( array(
			'singular' => 'post',
			'plural' => 'posts',
			'ajax' => false,
		) );
	}

	public function prepare_items() {
		$per_page = $this->get_items_per_page( 'ct_wpcf7_submissions_per_page' );

		$args = array(
			'post_type' => 'form-submissions',
			'post_parent' => $this->contact_form?->id(),
			'posts_per_page' => $per_page,
			'orderby' => 'date',
			'order' => 'DESC',
			'offset' => ( $this->get_pagenum() - 1 ) * $per_page,
		);

		if ( $search_keyword = wpcf7_superglobal_request( 's' ) ) {
			$args['s'] = $search_keyword;
		}

		if ( $order_by = wpcf7_superglobal_request( 'orderby' ) ) {
			$args['orderby'] = $order_by;
		}

		if (
			$order = wpcf7_superglobal_request( 'order' ) and
			'desc' === strtolower( $order )
		) {
			$args['order'] = 'DESC';
		}

		$q = new WP_Query();

		foreach ( $q->query( $args ) as &$item ) {
			$this->items[] = new Submission_Item( $item );
		}

		$total_items = $q->found_posts;
		$total_pages = ceil( $total_items / $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page' => $per_page,
		) );
	}

	protected function get_sortable_columns() {
		$columns = array(
			'title' => array( 'title', true ),
			'author' => array( 'author', false ),
			'date' => array( 'date', false ),
		);

		return $columns;
	}

	public function get_columns() {
		return get_column_headers( get_current_screen() );
	}

	/**
	 * @param Submission_Item $item
	 * @param string $column_name
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		return '';
	}

	/**
	 * @param Submission_Item $item
	 * @param string $column_name
	 * @param string $primary
	 * @return string
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( $column_name !== $primary ) {
			return '';
		}

		$actions = array(
			'view' => sprintf(
				'<a href="%1$s" aria-label="%2$s">%3$s</a>',
				$item->url(),
				esc_attr( sprintf(
					/* translators: %s: title of contact form */
					__( 'View "%s"', 'custom-theme' ),
					$item->title
				) ),
				__( 'View', 'custom-theme' ),
			),
		);

		if ( $item->is_unread() ) {
			$actions['read'] = sprintf(
				'<a href="%1$s" aria-label="%2$s">%3$s</a>',
				$item->url( 'read', 'ct-wpcf7-submission_' ),
				esc_attr( sprintf(
					/* translators: %s: title of contact form */
					__( 'Mark "%s" as read', 'custom-theme' ),
					$item->title,
				) ),
				__( 'Mark as read', 'custom-theme' ),
			);
		}

		return $this->row_actions( $actions );
	}

	/**
	 * @param Submission_Item $item
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$item->id
		);
	}

	public function column_title( Submission_Item $item ) {
		$output = sprintf(
			'<a class="%4$s" href="%1$s" aria-label="%2$s">%3$s</a>',
			$item->url(),
			esc_attr( sprintf(
				/* translators: %s: title of submission */
				__( 'View &#8220;%s&#8221;', 'custom-theme' ),
				$item->title
			) ),
			esc_html( $item->title ),
			$item->is_unread() ? 'row-title' : ''
		);

		return $output;
	}

	public function column_author( Submission_Item $item ) {
		$author = $item->author();

		if ( ! $author ) {
			return '<span aria-hidden="true">—</span><span class="screen-reader-text">(no author)</span>';
		}

		return esc_html( $author->display_name );
	}

	public function column_form( Submission_Item $item ) {
		$form = $item->form();

		if ( ! $form ) {
			return '<span aria-hidden="true">—</span><span class="screen-reader-text">(no form)</span>';
		}

		return esc_html( $form->post_title );
	}

	public function column_date( Submission_Item $item ) {
		if ( ! $item->datetime ) {
			return '';
		}

		return sprintf(
			/* translators: 1: date, 2: time */
			__( '%1$s at %2$s', 'custom-theme' ),
			/* translators: date format, see https://www.php.net/date */
			$item->datetime->format( __( 'Y/m/d', 'custom-theme' ) ),
			/* translators: time format, see https://www.php.net/date */
			$item->datetime->format( __( 'g:i a', 'custom-theme' ) )
		);
	}
}
