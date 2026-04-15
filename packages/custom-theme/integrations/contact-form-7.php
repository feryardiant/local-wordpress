<?php

add_action( 'init', static function() {
	$post_type = 'form-submissions';

	register_post_type( $post_type, array(
		'labels' => array(
			'name' => __( 'Submissions', 'custom-theme' ),
			'singular_name' => __( 'Submission', 'custom-theme' ),
		),
		'public' => false,
		'show_ui' => false,
		'show_in_nav_menus' => false,
		'show_in_admin_bar' => false,
		'capability_type' => 'post',
		'hierarchical' => false,
		'supports' => [ 'title', 'excerpt', 'author', 'custom-fields' ],
		'rewrite' => false,
		'query_var' => false,
		'menu_icon' => 'dashicons-email-alt',
		// 'register_meta_box_cb' => static function( WP_Post $post ) {
		// 	//
		// },
	) );

	add_action( 'admin_menu', 'ct_submissions_admin_menu', 9, 0 );
} );

add_action( 'wpcf7_before_send_mail', 'ct_wpcf7_before_send_mail' );

add_filter( 'wpcf7_editor_panels', 'ct_wpcf7_editor_panels' );

function ct_wpcf7_before_send_mail( WPCF7_ContactForm $contact_form ) {
	if ( ! ( $submission = WPCF7_Submission::get_instance() ) ) {
		return;
	}

	$form_data = array();
	$email_field = null;
	$phone_field = null;
	$name_field = null;
	$subject_field = null;

	foreach ( $contact_form->scan_form_tags() as $tag ) {
		/** @var WPCF7_FormTag $tag */
		if ( in_array( $tag->basetype, [ 'submit', 'button' ] ) ) {
			continue;
		}

		$value = $submission->get_posted_string( $tag->name );

		if ( ! empty( $value ) ) {
			if ( in_array( 'autocomplete:email', $tag->options ) ) {
				$email_field = $tag->name;
			}

			if ( in_array( 'autocomplete:phone', $tag->options ) ) {
				$phone_field = $tag->name;
			}

			if ( in_array( 'autocomplete:name', $tag->options ) ) {
				$name_field = $tag->name;
			}

			if ( in_array( 'autocomplete:subject', $tag->options ) ) {
				$subject_field = $tag->name;
			}
		}

		$form_data[$tag->name] = $value;
	}

	$submitter_id = 0;

	if ( $email_field && $name_field && is_email( $form_data[$email_field] ) ) {
		$submitter_id = ct_wpcf7_register_submitter(
			$form_data[$email_field],
			$form_data[$name_field],
			$form_data[$phone_field],
		);
	}

	do_action( 'ct_wpcf7_before_save', $form_data );

	$subject_title = $subject_field ? $form_data[$subject_field] : null;

	// Saving the data
	$returned_id = wp_insert_post( array(
		'post_type' => 'form-submissions',
		'post_status' => 'publish',
		'post_title' => $subject_title ?? sprintf(
			/* translators: %s: Contact form title */
			__( 'Submission for "%s"' ),
			$contact_form->title()
		),
		'post_parent' => $contact_form->id(),
		'post_author' => $submitter_id,
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

	$formatter->append_start_tag( 'fieldset' );

	$formatter->append_start_tag( 'legend' );
	$formatter->append_preformatted( 'Description goes here' );
	$formatter->end_tag( 'legend' );

	// Future content goes here

	$formatter->print();
}

function ct_submissions_admin_menu() {
	$submissions = add_submenu_page( 'wpcf7',
		__( 'List of Submissions', 'custom-theme' ),
		__( 'Submissions', 'custom-theme' ),
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
	$screen = get_current_screen();

	if ( ! class_exists( Submissions_List_Table::class ) ) {
		require_once __DIR__ . '/class-submissions-list-table.php';
	}

	add_filter(
		'manage_' . $screen->id . '_columns',
		array( Submissions_List_Table::class, 'define_column' ),
		10, 1
	);
}

function ct_submissions_admin_management_page() {
	$list_table = new Submissions_List_Table();

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
		esc_html( __( 'Form Submissions', 'custom-theme' ) )
	);

	$formatter->end_tag( 'h1' );

	$formatter->append_start_tag( 'hr', array(
		'class' => 'wp-header-end',
	) );

	$formatter->call_user_func( static function () use ( $list_table ) {
		$list_table->search_box(
			__( 'Search Submissions', 'custom-theme' ),
			'ct-wpcf7-submissions'
		);

		$list_table->display();
	} );

	$formatter->end_tag( 'div' );

	$formatter->print();
}

function ct_wpcf7_register_submitter( string $email, string $name, ?string $phone = null ) {
	if ( email_exists( $email ) ) {
		$user = WP_User::get_data_by( 'email', $email );

		return (int) $user->ID;
	}

	list( $login ) = explode( '@', $email );

	$login = sanitize_user( $login );

	if ( username_exists( $login ) ) {
		$user = WP_User::get_data_by( 'login', $login );

		return (int) $user->ID;
	}

	$user_data = array(
		'user_login' => $login,
		'user_email' => $email,
		'display_name' => $name,
		'user_pass' => wp_generate_password( 12, true ),
	);

	$name_parts = explode( ' ', $name );

	if ( count( $name_parts ) > 1 ) {
		$user_data['first_name'] = $name_parts[0];
		$user_data['last_name'] = implode( ' ', array_slice( $name_parts, 1 ) );
	}

	$user_id = wp_insert_user( $user_data );

	if ( ! $user_id || is_wp_error( $user_id ) ) {
		return 0;
	}

	add_user_meta( $user_id, 'user_phone', $phone ?? '' );

	return (int) $user_id;
}
