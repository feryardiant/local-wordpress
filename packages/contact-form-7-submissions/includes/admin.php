<?php

namespace WPCF7S;

use WPCF7_ContactForm;
use WPCF7_HTMLFormatter;

/**
 * Register the submissions admin menu.
 */
\add_action(
	'admin_menu',
	static function (): void {
		$post_type_object = \get_post_type_object( 'form-submissions' );

		$submissions = \add_submenu_page( 'wpcf7',
			$post_type_object->labels->items_list,
			$post_type_object->labels->menu_name,
			'wpcf7_read_contact_forms',
			'wpcf7-submissions',
			__NAMESPACE__ . '\admin_management_page',
			2,
		);

		\add_action(
			'load-' . $submissions,
			__NAMESPACE__ . '\admin_load_page',
			10, 0
		);
	},
	9, 0
);

/**
 * Capture the contact form submission and store it to database before sending it.
 */
\add_action(
	'wpcf7_before_send_mail',
	static function ( WPCF7_ContactForm $contact_form ) {
		$option = Option::get( $contact_form );

		if ( ! $option ) {
			return;
		}

		$form_data = $option->form_data();

		\do_action( 'wpcf7s_before_save', $form_data );

		$returned_id = Item::store( $contact_form, $option );

		\do_action( 'wpcf7s_after_save', $form_data, $returned_id );
	},
	10, 1
);

/**
 * Prepare to store option properties values.
 */
\add_action(
	'wpcf7_save_contact_form',
	static function ( WPCF7_ContactForm $contact_form, array $data ): void {
		$submissions = \wp_parse_args( $data['wpcf7-submissions'], array() );

		$contact_form->set_properties( array( 'submissions' => $submissions ) );
	},
	10, 2
);

/**
 * Register new contact form option properties.
 */
\add_filter(
	'wpcf7_pre_construct_contact_form_properties',
	static fn ( array $properties ) => array_merge(
		$properties, array( 'submissions' => array() )
	),
	10, 1
);

/**
 * Add a submissions panel to the contact form editor.
 */
\add_filter(
	'wpcf7_editor_panels',
	static function ( array $panels ): array {
		$post_type_object = \get_post_type_object( 'form-submissions' );

		$panels['submissions'] = array(
			'title' => $post_type_object->label,
			'callback' => __NAMESPACE__ . '\admin_editor_panel',
		);

		return $panels;
	},
	10, 1
);

/**
 * Load the submissions admin page.
 *
 * @internal
 */
function admin_load_page(): void {
	$action = \wpcf7_superglobal_request( 'action', null );

	\do_action( 'wpcf7s_admin_page_load',
		\wpcf7_superglobal_get( 'page' ),
		$action
	);

	if ( 'read' === $action ) {
		$id = (int) \wpcf7_superglobal_get( 'post' );

		\check_admin_referer( 'wpcf7s-entry_' . $id );

		$query = array();

		if ( Item::set_read_status( $id, true ) ) {
			$query['post'] = $id;
			$query['message'] = 'marked-read';
		}

		\wp_safe_redirect( admin_menu_url( $query ) );

		exit();
	}

	$screen = \get_current_screen();

	\add_filter(
		'manage_' . $screen->id . '_columns',
		array( List_Table::class, 'define_column' ),
		10, 1
	);
}

/**
 * Render the submissions panel for the contact form editor.
 *
 * @internal
 */
function admin_editor_panel( WPCF7_ContactForm $contact_form ): void {
	$formatter = new WPCF7_HTMLFormatter();
	$post_type_object = \get_post_type_object( 'form-submissions' );

	$formatter->append_start_tag( 'h2' );

	$formatter->append_preformatted(
		\esc_html( $post_type_object->label )
	);

	$formatter->end_tag( 'h2' );

	$formatter->append_start_tag( 'fieldset' );

	$formatter->append_start_tag( 'legend' );

	$description = __( 'You can edit the way you treat each submissions here.', 'wpcf7-submissions' );

	$formatter->append_preformatted( \esc_html( $description ) );

	$formatter->end_tag( 'legend' );

	$formatter->append_start_tag( 'table', array(
		'class' => 'form-table',
	) );

	$formatter->append_start_tag( 'tbody' );

	$option = new Option( $contact_form );
	$panel_id = 'wpcf7-submissions';

	foreach ( $option->fields() as $id => $field ) {
		$field = \wp_parse_args( $field, array(
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
		$field_atts = \wp_parse_args( $field['atts'], array(
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
			$field_atts['checked'] = $option[$id];
		}

		$formatter->append_start_tag( $field['type'], $field_atts );

		if ( $field['type'] === 'select' && is_array( $field['options'] ?? null ) ) {
			$formatter->append_start_tag( 'option', array(
				'selected' => is_null( $selected ),
				'value' => '',
			) );

			$formatter->append_preformatted(
				\esc_html( __( 'None selected', 'wpcf7-submissions' ) )
			);

			foreach ( $field['options'] as $value => $label ) {
				$value = is_int( $value ) ? $label : $value;

				$formatter->append_start_tag( 'option', array(
					'value' => \esc_attr( $value ),
					'selected' => $selected === $value,
				) );

				$formatter->append_preformatted( \esc_html( $label ) );
			}

			$formatter->end_tag( $field['type'] );
		}

		if ( ! $is_checkbox ) {
			$formatter->append_start_tag( 'p', array(
				'class' => 'description',
			) );
		}

		if ( ! empty( $field['description'] ) ) {
			$formatter->append_preformatted( \esc_html( $field['description'] ) );
		}
	}

	$formatter->end_tag( 'tbody' );

	$formatter->end_tag( 'table' );

	$formatter->print();
}

/**
 * Render the submissions admin management page.
 *
 * @internal
 */
function admin_management_page(): void {
	$list_table = new List_Table();
	$post_type_object = \get_post_type_object( 'form-submissions' );

	$list_table->prepare_items();

	$formatter = new WPCF7_HTMLFormatter( array(
		'allowed_html' => array_merge( \wpcf7_kses_allowed_html(), array(
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
		\esc_html( $post_type_object->labels->items_list )
	);

	$formatter->end_tag( 'h1' );

	$formatter->append_start_tag( 'hr', array(
		'class' => 'wp-header-end',
	) );

	$formatter->call_user_func( static function () use ( $list_table, $post_type_object ) {
		$list_table->search_box( $post_type_object->labels->search_items, 'wpcf7-submissions' );

		$list_table->display();
	} );

	$formatter->end_tag( 'div' );

	$formatter->print();
}

/**
 * Generate the admin URL for the submissions page.
 */
function admin_menu_url( array $query ): string {
	return \add_query_arg(
		$query,
		\menu_page_url( 'wpcf7-submissions', false )
	);
}
