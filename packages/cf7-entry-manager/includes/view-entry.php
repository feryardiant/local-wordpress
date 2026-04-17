<?php
/**
 * @package feryardiant/wpcf7-entry-manager
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 */

namespace CF7_EntryManager;

/**
 * @var Item $item
 */

$element = new Page_Element( array(
	'allowed_html' => array(
		'form' => array(
			'method' => true,
			'action' => true,
			'id' => true,
			'class' => true,
			'disabled' => true,
		),
	)
) );

$element->div( array(
	'id' => 'wpcf7em-submission-entry-viewer',
	'class' => 'wrap',
), static fn ( $element ) => $element
	->call( static function ( $item ) {
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
	}, $item )

	->h1(
		array( 'class' => 'wp-heading-inline' ),
		esc_html( __( 'View Form Submission', 'wpcf7-entry-manager' ) )
	)

	->hr( array( 'class' => 'wp-header-end' ) )

	->form( array(
		'method' => 'post',
		'action' => $item->url(),
		'id' => 'wpcf7-admin-form-element',
		'disabled' => ! $item->current_user_can( 'wpcf7_edit_contact_form' ),
	), static fn ( $element ) => $element
		->call_when(
			$item->current_user_can( 'wpcf7_edit_contact_form' ),
			static function () use ( $item ) {
				wp_nonce_field( 'wpcf7-save-submission-entry_' . $item->id );
			}
		)
		->input( array( 'type' => 'hidden', 'id' => 'post_ID', 'name' => 'post_ID', 'value' => $item->id ) )
		->input( array( 'type' => 'hidden', 'id' => 'hiddenaction', 'name' => 'action', 'value' => 'save' ) )
		->div(
			array( 'id' => 'poststuff', ),
			static fn ( $element ) => $element
				->div( array(
					'id' => 'post-body',
					'class' => 'metabox-holder columns-2 wp-clearfix',
				), static fn ( $element ) => $element
					->div(
						array( 'id' => 'post-body-content'),
						static fn ( $element ) => $element
						->div(
							array( 'id' => 'titlediv'),
							static fn ( $element ) => $element
							->div(
								array( 'id' => 'titlewrap'),
								static fn ( $element ) => $element
								->input( array(
									'type' => 'text',
									'name' => 'post_title',
									'value' => $item->title,
									'id' => 'title',
									'spellcheck' => 'true',
									'autocomplete' => 'off',
									'disabled' => ! $item->current_user_can( 'wpcf7_edit_contact_form' ),
									'placeholder' => __( 'Enter title here', 'wpcf7-entry-manager' ),
									'aria-label' => __( 'Enter title here', 'wpcf7-entry-manager' ),
								) )
							) // #titlewrap
						) // #titlediv
					) // #post-body-content

					->div( array(
						'id' => 'postbox-container-1',
						'class' => 'postbox-container',
					), static fn ( $element ) => $element
						->section(
							array( 'id' => 'submitdiv', 'class' => 'postbox' ),
							static fn ( $element ) => $element
								->div(
									array( 'class' => 'postbox-header' ),
									static fn ( $element ) => $element
										->h2(
											array(),
											esc_html( __( 'Status', 'wpcf7-entry-manager' ) ),
										)
										->div(
											array( 'class' => 'handle-actions hide-if-no-js' ),
											static fn ( $element ) => $element
												// Nothing for now
										), // .handle-actions
								) // .postbox-header

								->div(
									array( 'class' => 'inside' ),
									static fn ( $element ) => $element
										->div(
											array( 'id' => 'submitpost', 'class' => 'submitbox' ),
											static fn ( $element ) => $element
												->div(
													array( 'id' => 'minor-publishing-actions' ),
													static fn ( $element ) => $element
														->div(
															array( 'class' => 'hidden' ),
															static fn ( $element ) => $element
																->input( array(
																	'type' => 'submit',
																	'class' => 'button-primary',
																	'name' => 'wpcf7-save',
																	'value' => __( 'Save', 'wpcf7-entry-manager' ),
																) )
														) // .hidden
												) // #minor-publishing-actions

												->div(
													array( 'id' => 'major-publishing-actions' ),
													static fn ( $element ) => $element
														->div(
															array( 'id' => 'delete-action' ),
															static fn ( $element ) => $element
																->input( array(
																	'type' => 'submit',
																	'name' => 'wpcf7-delete',
																	'class' => 'delete submitdelete',
																	'value' => __( 'Delete', 'wpcf7-entry-manager' ),
																) )
														) // #delete-action

														->div(
															array( 'id' => 'publishing-action' ),
															static fn ( $element ) => $element
																->span( array( 'class' => 'spinner' ) )
																->input( array(
																	'type' => 'submit',
																	'class' => 'button-primary',
																	'name' => 'wpcf7-save',
																	'value' => __( 'Save', 'wpcf7-entry-manager' ),
																) )
														) // #publishing-action

														->clear()
												), // #major-publishing-actions
										), // #submitpost.submitbox
								), // .inside
						) // #submitdiv

						->section(
							array( 'id' => 'authordiv', 'class' => 'postbox' ),
							static fn ( $element ) => $element
								->div(
									array( 'class' => 'postbox-header' ),
									static fn ( $element ) => $element
										->h2(
											array(),
											esc_html( __( 'Author', 'wpcf7-entry-manager' ) ),
										)
										->div(
											array( 'class' => 'handle-actions hide-if-no-js' ),
											static fn ( $element ) => $element
												// Nothing for now
										), // .handle-actions
								) // .postbox-header

								->div(
									array( 'class' => 'inside' ),
									static fn ( $element ) => $element
										// Nothing for now
								), // .inside
						) // #authordiv
					) // #postbox-container-1

					->div( array(
						'id' => 'postbox-container-2',
						'class' => 'postbox-container',
					), static fn ( $element ) => $element
						->div(
							array( 'id' => 'submission-entry-editor' ),
							static fn ( $element ) => $element
								->section(
									array( 'class' => 'postbox', ),
									static fn ( $element ) => $element
										->div(
											array( 'class' => 'postbox-header' ),
											static fn ( $element ) => $element
												->h2(
													array(),
													esc_html( __( 'Submission Entry', 'wpcf7-entry-manager' ) )
												)
												->div(
													array( 'class' => 'handle-actions hide-if-no-js' ),
													static fn ( $element ) => $element
														// Nothing for now
												), // .handle-actions
										) // .postbox-header
										->div(
											array( 'class' => 'inside', ),
											static fn ( $element ) => $element
												// Nothing here
										) // .inside
								) // .postbox
						) // #submission-entry-editor
					) // #postbox-container-2
				) // #post-body

				->clear()
		) // #poststuff
	) // #wpcf7-admin-form-element
); // #wpcf7em-submission-entry-viewer.wrap

$element->render();
