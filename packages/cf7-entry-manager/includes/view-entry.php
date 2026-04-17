<?php
/**
 * @package feryardiant/wpcf7-entry-manager
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 */

use CF7_EntryManager\Item;

/**
 * @var Item $item
 */

$post_id = $item->id;

$save_button = sprintf(
	'<input %s />',
	wpcf7_format_atts( array(
		'type' => 'submit',
		'class' => 'button-primary',
		'name' => 'wpcf7-save',
		'value' => __( 'Save', 'wpcf7-entry-manager' ),
	) )
);

$formatter = new WPCF7_HTMLFormatter( array(
	'allowed_html' => array_merge( wpcf7_kses_allowed_html(), array(
		'form' => array(
			'method' => true,
			'action' => true,
			'id' => true,
			'class' => true,
			'disabled' => true,
		),
	) ),
) );

$formatter->append_start_tag( 'div', array(
	'id' => 'wpcf7em-submission-entry-viewer',
	'class' => 'wrap',
) );

$formatter->append_start_tag( 'h1', array(
	'class' => 'wp-heading-inline',
) );

$formatter->append_preformatted(
	esc_html( __( 'View Form Submission', 'wpcf7-entry-manager' ) )
);

$formatter->end_tag( 'h1' );

if ( ! $item->id and current_user_can( 'wpcf7_edit_contact_forms' ) ) {
	$formatter->append_whitespace();

	$formatter->append_preformatted(
		wpcf7_link(
			menu_page_url( 'wpcf7-new', false ),
			__( 'Add Contact Form', 'wpcf7-entry-manager' ),
			array( 'class' => 'page-title-action' )
		)
	);
}

$formatter->append_start_tag( 'hr', array(
	'class' => 'wp-header-end',
) );

$formatter->call_user_func( static function () use ( $item ) {
	do_action( 'wpcf7_admin_warnings',
		$item->id ? 'wpcf7-new' : 'wpcf7',
		wpcf7_current_action(),
		$item
	);

	do_action( 'wpcf7_admin_notices',
		$item->id ? 'wpcf7-new' : 'wpcf7',
		wpcf7_current_action(),
		$item
	);
} );

if ( $item ) {
	$formatter->append_start_tag( 'form', array(
		'method' => 'post',
		'action' => $item->url(),
		'id' => 'wpcf7-admin-form-element',
		'disabled' => ! current_user_can( 'wpcf7_edit_contact_form', $post_id ),
	) );

	if ( current_user_can( 'wpcf7_edit_contact_form', $post_id ) ) {
		$formatter->call_user_func( static function () use ( $post_id ) {
			wp_nonce_field( 'wpcf7-save-submission-entry_' . $post_id );
		} );
	}

	$formatter->append_start_tag( 'input', array(
		'type' => 'hidden',
		'id' => 'post_ID',
		'name' => 'post_ID',
		'value' => (int) $post_id,
	) );

	$formatter->append_start_tag( 'input', array(
		'type' => 'hidden',
		'id' => 'hiddenaction',
		'name' => 'action',
		'value' => 'save',
	) );

	$formatter->append_start_tag( 'input', array(
		'type' => 'hidden',
		'id' => 'active-tab',
		'name' => 'active-tab',
		'value' => wpcf7_superglobal_get( 'active-tab' ),
	) );

	$formatter->append_start_tag( 'div', array(
		'id' => 'poststuff',
	) );

	$formatter->append_start_tag( 'div', array(
		'id' => 'post-body',
		'class' => 'metabox-holder columns-2 wp-clearfix',
	) );

	$formatter->append_start_tag( 'div', array(
		'id' => 'post-body-content',
	) );

	$formatter->append_start_tag( 'div', array(
		'id' => 'titlediv',
	) );

	$formatter->append_start_tag( 'div', array(
		'id' => 'titlewrap',
	) );

	$formatter->append_start_tag( 'input', array(
		'type' => 'text',
		'name' => 'post_title',
		'value' => $item->title,
		'id' => 'title',
		'spellcheck' => 'true',
		'autocomplete' => 'off',
		'disabled' => ! current_user_can( 'wpcf7_edit_contact_form', $post_id ),
		'placeholder' => __( 'Enter title here', 'wpcf7-entry-manager' ),
		'aria-label' => __( 'Enter title here', 'wpcf7-entry-manager' ),
	) );

	$formatter->end_tag( 'div' ); // #titlewrap

	$formatter->append_start_tag( 'div', array(
		'class' => 'inside',
	) );

	$formatter->end_tag( 'div' ); // .inside
	$formatter->end_tag( 'div' ); // #titlediv
	$formatter->end_tag( 'div' ); // #post-body-content

	$formatter->append_start_tag( 'div', array(
		'id' => 'postbox-container-1',
		'class' => 'postbox-container',
	) );

	$formatter->append_start_tag( 'section', array(
		'id' => 'submitdiv',
		'class' => 'postbox',
	) );

	$formatter->append_start_tag( 'div', array(
		'class' => 'postbox-header'
	) );

	$formatter->append_start_tag( 'h2' );

	$formatter->append_preformatted(
		esc_html( __( 'Status', 'wpcf7-entry-manager' ) )
	);

	$formatter->end_tag( 'h2' );

	$formatter->append_start_tag( 'div', array(
		'class' => 'handle-actions hide-if-no-js',
	) );

	// Nothing just now

	$formatter->end_tag( 'div' );

	$formatter->end_tag( 'div' ); // .postbox-header

	$formatter->append_start_tag( 'div', array(
		'class' => 'inside',
	) );

	$formatter->append_start_tag( 'div', array(
		'class' => 'submitbox',
		'id' => 'submitpost',
	) );

	$formatter->append_start_tag( 'div', array(
		'id' => 'minor-publishing-actions',
	) );

	$formatter->append_start_tag( 'div', array(
		'class' => 'hidden',
	) );

	$formatter->append_start_tag( 'input', array(
		'type' => 'submit',
		'class' => 'button-primary',
		'name' => 'wpcf7-save',
		'value' => __( 'Save', 'wpcf7-entry-manager' ),
	) );

	$formatter->end_tag( 'div' ); // .hidden

	if ( ! $item->id ) {
		$formatter->append_start_tag( 'input', array(
			'type' => 'submit',
			'name' => 'wpcf7-copy',
			'class' => 'copy button',
			'value' => __( 'Duplicate', 'wpcf7-entry-manager' ),
		) );
	}

	$formatter->end_tag( 'div' ); // #minor-publishing-actions

	$formatter->append_start_tag( 'div', array(
		'id' => 'major-publishing-actions',
	) );

	if ( ! $item->id ) {
		$formatter->append_start_tag( 'div', array(
			'id' => 'delete-action',
		) );

		$formatter->append_start_tag( 'input', array(
			'type' => 'submit',
			'name' => 'wpcf7-delete',
			'class' => 'delete submitdelete',
			'value' => __( 'Delete', 'wpcf7-entry-manager' ),
		) );

		$formatter->end_tag( 'div' ); // #delete-action
	}

	$formatter->append_start_tag( 'div', array(
		'id' => 'publishing-action',
	) );

	$formatter->append_preformatted( '<span class="spinner"></span>' );
	$formatter->append_preformatted( $save_button );

	$formatter->end_tag( 'div' ); // #publishing-action

	$formatter->append_preformatted( '<div class="clear"></div>' );

	$formatter->end_tag( 'div' ); // #major-publishing-actions
	$formatter->end_tag( 'div' ); // #submitpost
	$formatter->end_tag( 'div' ); // .inside
	$formatter->end_tag( 'section' ); // #submitdiv

	$formatter->append_start_tag( 'section', array(
		'id' => 'authordiv',
		'class' => 'postbox',
	) );

	$formatter->append_start_tag( 'div', array(
		'class' => 'postbox-header'
	) );

	$formatter->append_start_tag( 'h2' );

	$formatter->append_preformatted(
		esc_html( __( 'Author', 'wpcf7-entry-manager' ) )
	);

	$formatter->end_tag( 'h2' );

	$formatter->append_start_tag( 'div', array(
		'class' => 'handle-actions hide-if-no-js',
	) );

	// Nothing just now

	$formatter->end_tag( 'div' );

	$formatter->end_tag( 'div' ); // .postbox-header

	$formatter->append_start_tag( 'div', array(
		'class' => 'inside',
	) );

	// Author info goes here

	$formatter->end_tag( 'div' ); // .inside
	$formatter->end_tag( 'section' ); // #authordiv

	$formatter->end_tag( 'div' ); // #postbox-container-1

	$formatter->append_start_tag( 'div', array(
		'id' => 'postbox-container-2',
		'class' => 'postbox-container',
	) );

	$formatter->append_start_tag( 'div', array(
		'id' => 'submission-entry-editor',
	) );

	$formatter->append_start_tag( 'section', array(
		'class' => 'postbox',
	) );

	$formatter->append_start_tag( 'div', array(
		'class' => 'postbox-header'
	) );

	$formatter->append_start_tag( 'h2' );

	$formatter->append_preformatted(
		esc_html( __( 'Submission Entry', 'wpcf7-entry-manager' ) )
	);

	$formatter->end_tag( 'h2' );

	$formatter->append_start_tag( 'div', array(
		'class' => 'handle-actions hide-if-no-js',
	) );

	// Nothing just now

	$formatter->end_tag( 'div' );

	$formatter->end_tag( 'div' ); // .postbox-header

	$formatter->append_start_tag( 'div', array(
		'class' => 'inside',
	) );

	// Nothing here

	$formatter->end_tag( 'div' ); // .inside
	$formatter->end_tag( 'section' ); // .postbox

	$formatter->end_tag( 'div' ); // #submission-entry-editor

	$formatter->end_tag( 'div' ); // #postbox-container-2
	$formatter->end_tag( 'div' ); // #post-body

	$formatter->append_preformatted( '<br class="clear" />' );

	$formatter->end_tag( 'div' ); // #poststuff
	$formatter->end_tag( 'form' );
}

$formatter->end_tag( 'div' ); // #wpcf7em-submission-entry-viewer.wrap

$formatter->print();

do_action( 'wpcf7_admin_footer', $item );
