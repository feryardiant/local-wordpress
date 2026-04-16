<?php

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

	add_action( 'admin_menu', 'ct_submissions_admin_menu', 9, 0 );
} );

add_action( 'wpcf7_before_send_mail', 'ct_wpcf7_before_send_mail' );

add_action( 'wpcf7_save_contact_form', 'ct_wpcf7_save_contact_form', 10, 3 );

add_filter( 'wpcf7_pre_construct_contact_form_properties', 'ct_wpcf7_pre_construct_contact_form_properties', 10, 2 );

add_filter( 'wpcf7_editor_panels', 'ct_wpcf7_editor_panels' );

function ct_wpcf7_before_send_mail( WPCF7_ContactForm $contact_form ) {
	$properties = ct_wpcf7_get_properties( $contact_form );
	$submission = WPCF7_Submission::get_instance();

	if ( ! $submission || ! $properties['record'] ) {
		return;
	}

	$form_data = array();

	foreach ( $contact_form->scan_form_tags() as $tag ) {
		/** @var WPCF7_FormTag $tag */
		if ( in_array( $tag->basetype, [ 'submit', 'button' ] ) ) {
			continue;
		}

		$form_data[$tag->name] = $submission->get_posted_string( $tag->name );
	}

	$submitter_id = 0;

	if ( $properties['author'] && $properties['author_email'] && $properties['author_name'] ) {
		$author_email = $form_data[$properties['author_email']] ?? null;
		$author_name = $form_data[$properties['author_name']] ?? null;
		$author_phone = $form_data[$properties['author_phone']] ?? null;

		if ( $author_email && $author_name && is_email( $author_email ) ) {
			$submitter_id = ct_wpcf7_register_submitter( $author_email, $author_name, $author_phone );
		}
	}

	do_action( 'ct_wpcf7_before_save', $form_data );

	$subject_title = $properties['subject'] ? $form_data[$properties['subject']] : null;

	// Saving the data
	$returned_id = wp_insert_post( array(
		'post_type' => 'form-submissions',
		'post_status' => 'publish',
		'post_title' => $subject_title ?? sprintf(
			/* translators: %s: Contact form title */
			__( 'Submission for "%s"', 'custom-theme' ),
			$contact_form->title()
		),
		'post_parent' => $contact_form->id(),
		'post_author' => $submitter_id,
		'post_excerpt' => $properties['message'] ? $form_data[$properties['message']] : null,
		// 'post_content' => wp_json_encode( $form_data ),
	) );

	if ( $returned_id ) {
		foreach ( $form_data as $field => $value ) {
			add_post_meta( $returned_id, $field, $value );
		}

		add_post_meta( $returned_id, '_ct_submission_read', 0 );
	}

	do_action( 'ct_wpcf7_after_save', $form_data );
}

function ct_wpcf7_pre_construct_contact_form_properties( array $properties, WPCF7_ContactForm $contact_form ) {
	$properties['submissions'] = array();

	return $properties;
}

function ct_wpcf7_save_contact_form( WPCF7_ContactForm $contact_form, array $data, string $context ) {
	$submissions = wp_parse_args( $data['ct-wpcf7-submissions'], array() );

	$contact_form->set_properties( array( 'submissions' => $submissions ) );
}

/**
 * @param WPCF7_ContactForm $contact_form
 * @return array{record: bool, subject: string, message: string, author: bool, author_name: string, author_email: string, author_phone: string}
 */
function ct_wpcf7_get_properties( WPCF7_ContactForm $contact_form ) {
	$properties = wp_parse_args( $contact_form->prop( 'submissions' ), array(
		'record' => null,
		'subject' => '',
		'message' => '',
		'author' => null,
		'author_name' => '',
		'author_email' => '',
		'author_phone' => '',
	) );

	$properties['record'] = ! is_null( $properties['record'] );
	$properties['author'] = ! is_null( $properties['author'] );

	return $properties;
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

	$mail_tags = $contact_form->collect_mail_tags();

	$fields = array(
		'record' => array(
			'label' => esc_html( __( 'Record', 'custom-theme' ) ),
			'description' => esc_html(
				__( 'Whether to record the submissions to the database', 'custom-theme' )
			),
			'atts' => array( 'type' => 'checkbox' ),
		),
		'subject' => array(
			'label' => esc_html( __( 'Subject', 'custom-theme' ) ),
			'description' => esc_html(
				__( 'Choose which field is identified as a submission subject', 'custom-theme' )
			),
			'type' => 'select',
			'atts' => array( 'class' => 'large-text code' ),
			'options' => $mail_tags,
		),
		'message' => array(
			'label' => esc_html( __( 'Message', 'custom-theme' ) ),
			'description' => esc_html(
				__( 'Choose which field is identified as a submission message', 'custom-theme' )
			),
			'type' => 'select',
			'atts' => array( 'class' => 'large-text code' ),
			'options' => $mail_tags,
		),
		'sep-1' => array( 'type' => 'separator' ),
		'author' => array(
			'label' => esc_html( __( 'Author', 'custom-theme' ) ),
			'description' => esc_html(
				__( 'Whether the submission author will be registered as subscriber', 'custom-theme' )
			),
			'atts' => array( 'type' => 'checkbox' ),
		),
		'author_name' => array(
			'label' => esc_html( __( 'Author Name', 'custom-theme' ) ),
			'description' => esc_html(
				__( 'Choose which field is identified as the submitter\'s name', 'custom-theme' )
			),
			'type' => 'select',
			'atts' => array( 'class' => 'large-text code' ),
			'options' => $mail_tags,
		),
		'author_email' => array(
			'label' => esc_html( __( 'Author Email', 'custom-theme' ) ),
			'description' => esc_html(
				__( 'Choose which field is identified as the submitter\'s email', 'custom-theme' )
			),
			'type' => 'select',
			'atts' => array( 'class' => 'large-text code' ),
			'options' => $mail_tags,
		),
		'author_phone' => array(
			'label' => esc_html( __( 'Author Phone', 'custom-theme' ) ),
			'description' => esc_html(
				__( 'Choose which field is identified as the submitter\'s phone number', 'custom-theme' )
			),
			'type' => 'select',
			'atts' => array( 'class' => 'large-text code' ),
			'options' => $mail_tags,
		),
	);

	$panel_id = 'ct-wpcf7-submissions';
	$submissions = ct_wpcf7_get_properties( $contact_form );

	foreach ( $fields as $id => $field ) {
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
			'value' => $submissions[$id] ?: null,
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
			$field_atts['checked'] = $submissions[$id] !== null;
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
	$screen = get_current_screen();
	$action = wpcf7_superglobal_request( 'action', null );

	do_action( 'ct_submissions_admin_page_load',
		wpcf7_superglobal_get( 'page' ),
		$action
	);

	require_once __DIR__ . '/class-submissions-list-table.php';
	require_once __DIR__ . '/class-submission-item.php';

	if ( 'read' === $action ) {
		$id = (int) wpcf7_superglobal_get( 'post' );

		check_admin_referer( 'ct-wpcf7-submission_' . $id );

		$query = array();
		$updated = update_post_meta( $id, '_ct_submission_read', 1 );

		if ( $updated ) {
			$query['post'] = $id;
			$query['message'] = 'marked-read';
		}

		$redirect_to = add_query_arg(
			$query,
			menu_page_url( 'ct-wpcf7-submissions', false )
		);

		wp_safe_redirect( $redirect_to );
		exit();
	}

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
