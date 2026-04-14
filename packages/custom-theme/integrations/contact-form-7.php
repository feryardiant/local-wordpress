<?php

add_action( 'init', static function() {
	register_post_type( 'form-submissions', array(
		'labels' => array(
			'name' => __( 'Submissions', 'custom-theme' ),
			'singular_name' => __( 'Submission', 'custom-theme' ),
		),
		'public' => false,
		'show_ui' => false,
		'show_in_menu' => false,
		'show_in_nav_menus' => false,
		'show_in_admin_bar' => false,
		'capability_type' => 'post',
		'hierarchical' => false,
		'supports' => [ 'title', 'excerpt', 'custom-fields' ],
		'rewrite' => false,
		'query_var' => false,
		'menu_icon' => 'dashicons-email-alt',
		// 'register_meta_box_cb' => static function( WP_Post $post ) {
		// 	//
		// },
	) );
} );

add_action( 'wpcf7_before_send_mail', 'ct_wpcf7_before_send_mail' );

add_filter( 'wpcf7_editor_panels', 'ct_wpcf7_editor_panels' );

function ct_wpcf7_before_send_mail( WPCF7_ContactForm $contact_form ) {
	if ( ! ( $submission = WPCF7_Submission::get_instance() ) ) {
		return;
	}

	$uploaded_files = $form_data = array();

	foreach ( $submission->uploaded_files() as $field => $file ) {
		$file = is_array( $file ) ? reset( $file ) : $file;
		if ( empty($file) ) {
			continue;
		}

		$uploaded_files[$field] = $file['tmp_name'];
	}

	foreach ( $submission->get_posted_data() as $field => $value ) {
		$value = ct_wpcf7_sanitize_text_value( $value );
		$field = sanitize_text_field( $field );

		$form_data[$field] = $value;
	}

	do_action( 'ct_wpcf7_before_save', $form_data );

	// Saving the data
	$returned_id = wp_insert_post( array(
		'post_type' => 'form-submissions',
		'post_status' => 'publish',
		'post_title' => 'Form Submission',
		'post_parent' => $contact_form->id(),
		// 'post_content' => wp_json_encode( $form_data ),
		// 'post_excerpt' => wp_json_encode( $form_data ),
	) );

	if ( $returned_id ) {
		foreach ( $form_data as $field => $value ) {
			add_post_meta( $returned_id, $field, $value );
		}
	}

	do_action( 'ct_wpcf7_after_save', $form_data );
}

function ct_wpcf7_sanitize_text_value( string|array $value ) {
	if ( is_string( $value ) ) {
		$bl = array('\"',"\'",'/','\\','"',"'");
		$wl = array('&quot;','&#039;','&#047;', '&#092;','&quot;','&#039;');

		return str_replace($bl, $wl, $value );
	}

	return array_map( 'ct_wpcf7_sanitize_text_value', $value );
}

function ct_wpcf7_editor_panels( array $panels ) {
	$panels['submissions'] = array(
		'title' => __( 'Submissions', 'custom-theme' ),
		'callback' => 'ct_wpcf7_submissions_panel',
	);

	return $panels;
}

function ct_wpcf7_submissions_panel( WPCF7_ContactForm $contact_form ) {
	$formatter = new WPCF7_HTMLFormatter();

	$formatter->append_start_tag( 'h2' );

	$formatter->append_preformatted(
		esc_html( __( 'Form Submissions', 'custom-theme' ) )
	);

	$formatter->end_tag( 'h2' );

	$formatter->append_start_tag( 'pre' );

	$submissions = get_posts( array(
		'post_type' => 'form-submissions',
		'post_parent' => $contact_form->id(),
		// 'posts_per_page' => -1,
	) );

	$items = array();

	foreach ( $submissions as $submission ) {
		$item = array(
			'ID' => $submission->ID,
			'post_type' => $submission->post_type,
			'post_title' => $submission->post_title,
			'post_excerpt' => $submission->post_excerpt,
			'post_content' => $submission->post_content,
			'post_date' => $submission->post_date,
			'post_date_gmt' => $submission->post_date_gmt,
			'post_modified' => $submission->post_modified,
			'post_modified_gmt' => $submission->post_modified_gmt,
			'post_author' => $submission->post_author,
			'post_status' => $submission->post_status,
			'post_name' => $submission->post_name,
			'guid' => $submission->guid,
			'meta' => array(),
		);

		foreach ( get_post_meta( $submission->ID ) as $field => $value ) {
			$item['meta'][$field] = is_array( $value ) ? reset( $value ) : $value;
		}

		$items[] = $item;
	}

	$formatter->append_preformatted( print_r( $items, true ) );

	$formatter->end_tag( 'pre' );

	$formatter->print();
}
