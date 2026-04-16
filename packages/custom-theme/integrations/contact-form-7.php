<?php

use CT_WPCF7\Submission_Item;
use CT_WPCF7\Submission_Option;

add_action( 'init', static function() {
	$post_type = 'form-submissions';

	register_post_type( $post_type, array(
		'labels' => array(
			'name' => __( 'Submissions', 'custom-theme' ),
			'singular_name' => __( 'Submission', 'custom-theme' ),
			'view_item' => __( 'View Submission', 'custom-theme' ),
			'search_items' => __( 'Search Submissions', 'custom-theme' ),
			'not_found' => __( 'No submissions found.', 'custom-theme' ),
			'not_found_in_trash' => __( 'No submissions found in Trash.', 'custom-theme' ),
			'filter_items_list' => _x( 'Filter submissions list', 'Screen reader text for the filter links heading on the post type listing screen.', 'custom-theme' ),
			'items_list_navigation' => _x( 'Submissions list navigation', 'Screen reader text for the pagination heading on the post type listing screen.', 'custom-theme' ),
			'items_list' => _x( 'Submissions list', 'Screen reader text for the items list heading on the post type listing screen.', 'custom-theme' ),
		),
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
		// 'register_meta_box_cb' => static function( WP_Post $post ) {
		// 	//
		// },
	) );

	require_once __DIR__ . '/class-submission-option.php';
	require_once __DIR__ . '/class-submissions-list-table.php';
	require_once __DIR__ . '/class-submission-item.php';

	add_action( 'admin_menu', 'ct_submissions_admin_menu', 9, 0 );
} );

add_action( 'wpcf7_before_send_mail', 'ct_wpcf7_before_send_mail' );

add_action( 'wpcf7_save_contact_form', 'ct_wpcf7_save_contact_form', 10, 3 );

add_filter( 'wpcf7_pre_construct_contact_form_properties', 'ct_wpcf7_pre_construct_contact_form_properties', 10, 2 );

add_filter( 'wpcf7_editor_panels', 'ct_wpcf7_editor_panels' );

function ct_wpcf7_before_send_mail( WPCF7_ContactForm $contact_form ) {
	$option = Submission_Option::get( $contact_form );

	$form_data = $option->form_data();

	do_action( 'ct_wpcf7_before_save', $form_data );

	$returned_id = Submission_Item::store( $option, $contact_form );

	do_action( 'ct_wpcf7_after_save', $form_data, $returned_id );
}

function ct_wpcf7_pre_construct_contact_form_properties( array $properties, WPCF7_ContactForm $contact_form ) {
	$properties['submissions'] = array();

	return $properties;
}

function ct_wpcf7_save_contact_form( WPCF7_ContactForm $contact_form, array $data, string $context ) {
	$submissions = wp_parse_args( $data['ct-wpcf7-submissions'], array() );

	$contact_form->set_properties( array( 'submissions' => $submissions ) );
}

function ct_wpcf7_editor_panels( array $panels ) {
	$post_type_object = get_post_type_object( 'form-submissions' );

	$panels['submissions'] = array(
		'title' => $post_type_object->label,
		'callback' => 'ct_wpcf7_submissions_panel',
	);

	return $panels;
}

function ct_wpcf7_submissions_panel( WPCF7_ContactForm $contact_form ) {
	$formatter = new WPCF7_HTMLFormatter();
	$post_type_object = get_post_type_object( 'form-submissions' );

	$formatter->append_start_tag( 'h2' );

	$formatter->append_preformatted(
		esc_html( $post_type_object->label )
	);

	$formatter->end_tag( 'h2' );

	$formatter->append_start_tag( 'fieldset' );

	$formatter->append_start_tag( 'legend' );

	$description = __( 'You can edit the way you treat each submissions here.', 'custom-theme' );

	$formatter->append_preformatted( esc_html( $description ) );

	$formatter->end_tag( 'legend' );

	$formatter->append_start_tag( 'table', array(
		'class' => 'form-table',
	) );

	$formatter->append_start_tag( 'tbody' );

	$option = new Submission_Option( $contact_form );
	$panel_id = 'ct-wpcf7-submissions';

	foreach ( $option->fields() as $id => $field ) {
		$field = wp_parse_args( $field, array(
			'label' => '',
			'description' => '',
			'type' => 'input',
			'atts' => array(),
			'options' => array(),
		) );

		$formatter->append_start_tag( 'tr' );

		if ( $field['type'] === 'separator' ) {
			$formatter->append_start_tag( 'td', array(
				'colspan' => '2',
				'style' => 'padding: 0;',
			) );

			$formatter->append_start_tag( 'hr' );
			continue;
		}

		$formatter->append_start_tag( 'th', array(
			'scope' => 'row',
		) );

		$field_id = sprintf( '%s-%s', $panel_id, $id );
		$field_atts = wp_parse_args( $field['atts'], array(
			'id' => $field_id,
			'name' => sprintf( '%s[%s]', $panel_id, $id ),
			'value' => $option[$id],
		) );

		$formatter->append_start_tag( 'label', array(
			'for' => $field_id,
		) );

		$formatter->append_preformatted( $field['label'] );

		$formatter->append_start_tag( 'td' );

		$is_select = $field['type'] === 'select';
		$is_checkbox = $field['type'] === 'input' && $field_atts['type'] === 'checkbox';

		$selected = null;

		if ( $is_select ) {
			$selected = $field_atts['value'];
			unset( $field_atts['value'] );
		}

		if ( $is_checkbox ) {
			$formatter->append_start_tag( 'label', array(
				'for' => $field_id,
			) );

			$field_atts['value'] = 'on';
			$field_atts['checked'] = $option[$id] !== null;
		}

		$formatter->append_start_tag( $field['type'], $field_atts );

		if ( $field['type'] === 'select' && is_array( $field['options'] ?? null ) ) {
			$formatter->append_start_tag( 'option', array(
				'selected' => is_null( $selected ),
				'value' => '',
			) );

			$formatter->append_preformatted(
				esc_html( __( 'None selected', 'custom-theme' ) )
			);

			foreach ( $field['options'] as $value => $label ) {
				$value = is_int( $value ) ? $label : $value;

				$formatter->append_start_tag( 'option', array(
					'value' => esc_attr( $value ),
					'selected' => $selected === $value,
				) );

				$formatter->append_preformatted( esc_html( $label ) );
			}

			$formatter->end_tag( $field['type'] );
		}

		if ( ! $is_checkbox ) {
			$formatter->append_start_tag( 'p', array(
				'class' => 'description',
			) );
		}

		if ( ! empty( $field['description'] ) ) {
			$formatter->append_preformatted( esc_html( $field['description'] ) );
		}
	}

	$formatter->end_tag( 'tbody' );

	$formatter->end_tag( 'table' );

	$formatter->print();
}

function ct_submissions_admin_menu() {
	$post_type_object = get_post_type_object( 'form-submissions' );

	$submissions = add_submenu_page( 'wpcf7',
		$post_type_object->labels->items_list,
		$post_type_object->labels->menu_name,
		'wpcf7_read_contact_forms',
		'ct-wpcf7-submissions',
		'ct_submissions_admin_management_page',
		1,
	);

	add_action(
		'load-' . $submissions,
		'ct_submissions_load_page',
		10, 0
	);
}

function ct_submissions_load_page() {
	$action = wpcf7_superglobal_request( 'action', null );

	do_action( 'ct_submissions_admin_page_load',
		wpcf7_superglobal_get( 'page' ),
		$action
	);

	if ( 'read' === $action ) {
		$id = (int) wpcf7_superglobal_get( 'post' );

		check_admin_referer( 'ct-wpcf7-submission_' . $id );

		$query = array();

		if ( Submission_Item::set_read_status( $id, true ) ) {
			$query['post'] = $id;
			$query['message'] = 'marked-read';
		}

		wp_safe_redirect( ct_wpcf7_admin_url( $query ) );
		exit();
	}

	$screen = get_current_screen();

	add_filter(
		'manage_' . $screen->id . '_columns',
		array( CT_WPCF7\Submissions_List_Table::class, 'define_column' ),
		10, 1
	);
}

function ct_submissions_admin_management_page() {
	$list_table = new CT_WPCF7\Submissions_List_Table();
	$post_type_object = get_post_type_object( 'form-submissions' );

	$list_table->prepare_items();

	$formatter = new WPCF7_HTMLFormatter( array(
		'allowed_html' => array_merge( wpcf7_kses_allowed_html(), array(
			'form' => array( 'method' => true ),
		) ),
	) );

	$formatter->append_start_tag( 'div', array(
		'class' => 'wrap',
	) );

	$formatter->append_start_tag( 'h1', array(
		'class' => 'wp-heading-inline',
	) );

	$formatter->append_preformatted(
		esc_html( $post_type_object->labels->items_list )
	);

	$formatter->end_tag( 'h1' );

	$formatter->append_start_tag( 'hr', array(
		'class' => 'wp-header-end',
	) );

	$formatter->call_user_func( static function () use ( $list_table, $post_type_object ) {
		$list_table->search_box( $post_type_object->labels->search_items, 'ct-wpcf7-submissions' );

		$list_table->display();
	} );

	$formatter->end_tag( 'div' );

	$formatter->print();
}

function ct_wpcf7_admin_url( array $query ) {
	return add_query_arg(
		$query,
		menu_page_url( 'ct-wpcf7-submissions', false )
	);
}
