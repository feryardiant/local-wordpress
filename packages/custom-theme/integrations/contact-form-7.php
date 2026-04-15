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
		'show_in_menu' => false,
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

	add_filter( "manage_{$post_type}_posts_columns", 'custom_theme_manage_submissions_columns', 10, 1 );
	add_filter( "manage_{$post_type}_posts_custom_column", 'custom_theme_manage_submissions_custom_column', 10, 2 );
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

function custom_theme_manage_submissions_columns( array $columns ) {
	return array(
		'cb' => $columns['cb'],
		'title' => $columns['title'],
		'form' => __( 'Form', 'custom-theme' ),
		'author' => $columns['author'],
		'date' => $columns['date'],
	);
}

function custom_theme_manage_submissions_custom_column( string $column, int $post_id ) {
	if ( $column === 'form' ) {
		$post = get_post( $post_id );

		if ( ! $post->post_parent ) {
			echo '<span aria-hidden="true">—</span><span class="screen-reader-text">(no form)</span>';

			return;
		}

		$form = get_post( $post->post_parent );

		echo $form->post_title;
	}
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
