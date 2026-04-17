<?php
/**
 * @package feryardiant/wpcf7-entry-manager
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 */

namespace CF7_EntryManager;

use WPCF7_ContactForm;

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
			'wpcf7-entry-manager',
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
	static function ( WPCF7_ContactForm $contact_form ): void {
		$option = Option::get( $contact_form );

		if ( ! $option ) {
			return;
		}

		$form_data = $option->form_data();

		\do_action( 'wpcf7em_before_save', $form_data );

		$returned_id = Item::store( $contact_form, $option );

		\do_action( 'wpcf7em_after_save', $form_data, $returned_id );
	},
	10, 1
);

/**
 * Prepare to store option properties values.
 */
\add_action(
	'wpcf7_save_contact_form',
	static function ( WPCF7_ContactForm $contact_form, array $data ): void {
		$submissions = \wp_parse_args( $data['wpcf7-entry-manager'], array() );

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

	\do_action( 'wpcf7em_admin_page_load',
		\wpcf7_superglobal_get( 'page' ),
		$action
	);

	if ( 'read' === $action ) {
		$id = (int) \wpcf7_superglobal_get( 'post' );

		\check_admin_referer( 'wpcf7em-entry_' . $id );

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
	$post_type_object = \get_post_type_object( 'form-submissions' );
	$element = new Page_Element( array(
		'allowed_html' => array(
			'form' => array( 'method' => true ),
		),
	) );

	$element->h2( array(), \esc_html( $post_type_object->label ) );

	$element->fieldset( child: static fn ( $element ) => $element
		->legend( array(), \esc_html(
			__( 'You can edit the way you treat each submissions here.', 'wpcf7-entry-manager' )
		) )

		->table( array( 'class' => 'form-table' ), static fn ( $element ) => $element
			->tbody( child: static function ( $element ) use ( $contact_form ) {
				$option = new Option( $contact_form );
				$panel_id = 'wpcf7-entry-manager';

				foreach ( $option->fields() as $id => $field ) {
					$field = \wp_parse_args( $field, array(
						'label' => '',
						'description' => '',
						'type' => 'input',
						'atts' => array(),
						'options' => array(),
					) );

					if ( $field['type'] === 'separator' ) {
						$element->tr( child: static fn ( $element ) => $element
							->td( array( 'colspan' => '2', 'style' => 'padding: 0;' ),
								static fn ( $element ) => $element->hr()
							)
						);

						continue;
					}

					$field_id = sprintf( '%s-%s', $panel_id, $id );

					$element->tr( child: static fn ( $element ) => $element
						->th( array( 'scope' => 'row' ),
							static fn ( $element ) => $element
								->label( array( 'for' => $field_id ), $field['label'] )
						)

						->td( child: static function ( $element ) use ( $option, $id, $panel_id, $field, $field_id ) {
							$field_atts = \wp_parse_args( $field['atts'], array(
								'id' => $field_id,
								'name' => sprintf( '%s[%s]', $panel_id, $id ),
								'value' => $option[$id],
							) );

							$is_select = $field['type'] === 'select';
							$is_checkbox = $field['type'] === 'input' && $field_atts['type'] === 'checkbox';

							$selected = null;

							if ( $is_select ) {
								$selected = $field_atts['value'];
								unset( $field_atts['value'] );
							}

							if ( $is_checkbox ) {
								$element->label( array( 'for' => $field_id ) );

								$field_atts['value'] = 'on';
								$field_atts['checked'] = $option[$id];
							}

							$element->{$field['type']}( $field_atts, static function ( Page_Element $element ) use ( $field, $selected ) {
								if ( $field['type'] !== 'select' || ! is_array( $field['options'] ?? null ) ) {
									return;
								}

								$element->option( array( 'selected' => is_null( $selected ), 'value' => '' ),
									\esc_html( __( 'None selected', 'wpcf7-entry-manager' ) )
								);

								foreach ( $field['options'] as $value => $label ) {
									$value = is_int( $value ) ? $label : $value;

									$element->option( array( 'value' => \esc_attr( $value ), 'selected' => $selected === $value ),
										\esc_html( $label )
									);
								}
							} );

							if ( ! empty( $field['description'] ) ) {
								if ( $is_checkbox ) {
									$element->span( array(), esc_html( $field['description'] ) );
								} else {
									$element->p( array( 'class' => 'description' ), esc_html( $field['description'] ) );
								}
							}
						} )
					);
				}
			} )
		)
	);

	$element->render();
}

/**
 * Render the submissions admin management page.
 *
 * @internal
 */
function admin_management_page(): void {
	$action = \wpcf7_superglobal_request( 'action', null );
	$item = \wpcf7_superglobal_request( 'post', null );

	if ( 'view' === $action && $item ) {
		$item = new Item( $item );
		$item->mark_read();

		require_once __DIR__ . '/view-entry.php';

		return;
	}

	$list_table = new List_Table();
	$post_type_object = \get_post_type_object( 'form-submissions' );

	$list_table->prepare_items();

	$element = new Page_Element( array(
		'allowed_html' => array(
			'form' => array( 'method' => true ),
		),
	) );

	$element->div(
		array( 'class' => 'wrap' ),
		static fn ( $element ) => $element
			->h1( array( 'class' => 'wp-heading-inline' ),
				\esc_html( $post_type_object->labels->items_list )
			)

			->hr( array( 'class' => 'wp-header-end' ) )

			->form( array( 'method' => 'get' ),
				static fn ( $element ) => $element
					->input( array(
						'type' => 'hidden',
						'name' => 'page',
						'value' => 'wpcf7-entry-manager',
					) )

					->call( static function () use ( $list_table, $post_type_object ) {
						$list_table->search_box(
							$post_type_object->labels->search_items,
							'wpcf7-entry-manager'
						);

						$list_table->display();
					} )
			)
	);

	$element->render();
}

/**
 * Generate the admin URL for the submissions page.
 */
function admin_menu_url( array $query ): string {
	return \add_query_arg(
		$query,
		\menu_page_url( 'wpcf7-entry-manager', false )
	);
}
