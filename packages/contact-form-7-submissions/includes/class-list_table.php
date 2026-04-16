<?php

namespace WPCF7S;

use WP_List_Table;
use WP_Query;
use WPCF7_ContactForm;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class List_Table extends WP_List_Table {
	/**
	 * Define the columns for the submissions list table.
	 *
	 * @param array $columns
	 * @return array
	 */
	public static function define_column( array $columns ) {
		return \wp_parse_args( $columns, array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Subject', 'wpcf7-submissions' ),
			'form' => __( 'Form', 'wpcf7-submissions' ),
			'author' => __( 'Author', 'wpcf7-submissions' ),
			'date' => __( 'Date', 'wpcf7-submissions' ),
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

	/**
	 * Prepare the items for the submissions list table.
	 */
	public function prepare_items() {
		$per_page = $this->get_items_per_page( 'wpcf7s_submissions_per_page' );

		$args = array(
			'post_type' => 'form-submissions',
			'post_parent' => $this->contact_form?->id(),
			'posts_per_page' => $per_page,
			'orderby' => 'date',
			'order' => 'DESC',
			'offset' => ( $this->get_pagenum() - 1 ) * $per_page,
		);

		if ( $search_keyword = \wpcf7_superglobal_request( 's' ) ) {
			$args['s'] = $search_keyword;
		}

		if ( $order_by = \wpcf7_superglobal_request( 'orderby' ) ) {
			$args['orderby'] = $order_by;
		}

		if (
			$order = \wpcf7_superglobal_request( 'order' ) and
			'desc' === strtolower( $order )
		) {
			$args['order'] = 'DESC';
		}

		$q = new WP_Query();

		foreach ( $q->query( $args ) as &$item ) {
			$this->items[] = new Item( $item );
		}

		$total_items = $q->found_posts;

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => ceil( $total_items / $per_page ),
			'per_page' => $per_page,
		) );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_sortable_columns() {
		$columns = array(
			'title' => array( 'title', true ),
			'author' => array( 'author', false ),
			'date' => array( 'date', false ),
		);

		return $columns;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_columns() {
		return \get_column_headers( \get_current_screen() );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param Item $item
	 * @param string $column_name
	 */
	protected function column_default( $item, $column_name ) {
		return '';
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param Item $item
	 * @param string $column_name
	 * @param string $primary
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( $column_name !== $primary ) {
			return '';
		}

		$actions = array(
			'view' => sprintf(
				'<a href="%1$s" aria-label="%2$s">%3$s</a>',
				$item->url(),
				\esc_attr( sprintf(
					/* translators: %s: title of contact form */
					__( 'View "%s"', 'wpcf7-submissions' ),
					$item->title
				) ),
				__( 'View', 'wpcf7-submissions' ),
			),
		);

		if ( $item->is_unread() ) {
			$actions['read'] = sprintf(
				'<a href="%1$s" aria-label="%2$s">%3$s</a>',
				$item->url( 'read', 'wpcf7s-entry_' ),
				\esc_attr( sprintf(
					/* translators: %s: title of contact form */
					__( 'Mark "%s" as read', 'wpcf7-submissions' ),
					$item->title,
				) ),
				__( 'Mark as read', 'wpcf7-submissions' ),
			);
		}

		return $this->row_actions( $actions );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param Item $item
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$item->id
		);
	}

	/**
	 * Configure the title column.
	 *
	 * @param Item $item
	 */
	public function column_title( Item $item ) {
		$output = sprintf(
			'<a class="%4$s" href="%1$s" aria-label="%2$s">%3$s</a>',
			$item->url(),
			\esc_attr( sprintf(
				/* translators: %s: title of submission */
				__( 'View &#8220;%s&#8221;', 'wpcf7-submissions' ),
				$item->title
			) ),
			\esc_html( $item->title ),
			$item->is_unread() ? 'row-title' : ''
		);

		return $output;
	}

	/**
	 * Configure the author column.
	 *
	 * @param Item $item
	 */
	public function column_author( Item $item ) {
		if ( $author = $item->author() ) {
			return \esc_html( $author->display_name );
		}

		return '<span aria-hidden="true">—</span><span class="screen-reader-text">(no author)</span>';
	}

	/**
	 * Configure the form column.
	 *
	 * @param Item $item
	 */
	public function column_form( Item $item ) {
		if ( $form = $item->form() ) {
			return \esc_html( $form->post_title );
		}

		return '<span aria-hidden="true">—</span><span class="screen-reader-text">(no form)</span>';
	}

	/**
	 * Configure the date column.
	 *
	 * @param Item $item
	 */
	public function column_date( Item $item ) {
		if ( ! $item->datetime ) {
			return '';
		}

		return sprintf(
			/* translators: 1: date, 2: time */
			__( '%1$s at %2$s', 'wpcf7-submissions' ),
			/* translators: date format, see https://www.php.net/date */
			$item->datetime->format( __( 'Y/m/d', 'wpcf7-submissions' ) ),
			/* translators: time format, see https://www.php.net/date */
			$item->datetime->format( __( 'g:i a', 'wpcf7-submissions' ) )
		);
	}
}
