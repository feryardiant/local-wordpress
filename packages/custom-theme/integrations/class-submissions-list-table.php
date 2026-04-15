<?php

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

		$this->items = $q->query( $args );

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

	protected function column_default( $item, $column_name ) {
		return '';
	}

	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$item->ID
		);
	}

	public function column_title( WP_Post $item ) {
		$edit_link = add_query_arg(
			array(
				'post' => absint( $item->ID ),
				'action' => 'edit',
			),
			menu_page_url( 'ct-wpcf7-submissions', false )
		);

		$output = sprintf(
			'<a class="row-title" href="%1$s" aria-label="%2$s">%3$s</a>',
			esc_url( $edit_link ),
			esc_attr( sprintf(
				/* translators: %s: title of contact form */
				__( 'Edit &#8220;%s&#8221;', 'contact-form-7' ),
				$item->post_title
			) ),
			esc_html( $item->post_title )
		);

		$output = sprintf( '<strong>%s</strong>', $output );

		return $output;
	}

	public function column_author( WP_Post $item ) {
		$author_id = absint( $item->post_author );

		if ( ! $author_id ) {
			return '<span aria-hidden="true">—</span><span class="screen-reader-text">(no author)</span>';
		}

		$author = get_userdata( $author_id );

		return esc_html( $author->display_name );
	}

	public function column_form( WP_Post $item ) {
		if ( ! $item->post_parent ) {
			return '<span aria-hidden="true">—</span><span class="screen-reader-text">(no form)</span>';
		}

		$form = get_post( $item->post_parent );

		return esc_html( $form->post_title );
	}

	public function column_date( WP_Post $item ) {
		$datetime = get_post_datetime( $item->ID );

		if ( false === $datetime ) {
			return '';
		}

		return sprintf(
			/* translators: 1: date, 2: time */
			__( '%1$s at %2$s', 'custom-theme' ),
			/* translators: date format, see https://www.php.net/date */
			$datetime->format( __( 'Y/m/d', 'custom-theme' ) ),
			/* translators: time format, see https://www.php.net/date */
			$datetime->format( __( 'g:i a', 'custom-theme' ) )
		);
	}
}
