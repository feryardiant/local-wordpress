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

	->h1( array( 'class' => 'wp-heading-inline' ),
		esc_html( __( 'View Form Submission', 'wpcf7-entry-manager' ) )
	)

	->hr( array( 'class' => 'wp-header-end' ) )

	->form( array(
		'method' => 'post',
		'action' => $item->url(),
		'id' => 'wpcf7-admin-form-element',
		'disabled' => ! $item->current_user_can( 'wpcf7_edit_contact_form' ),
	), static fn ( $element ) => $element
		->call_when( $item->current_user_can( 'wpcf7_edit_contact_form' ),
			static function () use ( $item ) {
				wp_nonce_field( 'wpcf7-save-submission-entry_' . $item->id );
			}
		)

		->input( array( 'type' => 'hidden', 'id' => 'post_ID', 'name' => 'post_ID', 'value' => $item->id ) )

		->input( array( 'type' => 'hidden', 'id' => 'hiddenaction', 'name' => 'action', 'value' => 'save' ) )

		->div( array( 'id' => 'poststuff', ), static fn ( $element ) => $element
			->div( array( 'id' => 'post-body', 'class' => 'metabox-holder columns-2 wp-clearfix' ),
				static fn ( $element ) => $element
				->div( array( 'id' => 'post-body-content'),
					static fn ( $element ) => $element
					->div( array( 'id' => 'titlediv'),
						static fn ( $element ) => $element
						->div( array( 'id' => 'titlewrap'),
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

					->div( array( 'id' => 'postbox-container-1', 'class' => 'postbox-container' ),
						static fn ( $element ) => $element
						->section( array( 'id' => 'submitdiv', 'class' => 'postbox' ),
							static fn ( $element ) => $element
							->div( array( 'class' => 'postbox-header' ),
								static fn ( $element ) => $element
								->h2( child: esc_html( __( 'Status', 'wpcf7-entry-manager' ) ) )
								->div( array( 'class' => 'handle-actions hide-if-no-js' ),
									static fn ( $element ) => $element
										// Nothing for now
								), // .handle-actions
							) // .postbox-header

							->div( array( 'class' => 'inside' ),
								static fn ( $element ) => $element
								->div( array( 'id' => 'submitpost', 'class' => 'submitbox' ),
									static fn ( $element ) => $element
									->div( array( 'id' => 'minor-publishing-actions' ),
										static fn ( $element ) => $element
										->div( array( 'class' => 'hidden' ),
											static fn ( $element ) => $element
											->input( array(
												'type' => 'submit',
												'class' => 'button-primary',
												'name' => 'wpcf7-save',
												'value' => __( 'Save', 'wpcf7-entry-manager' ),
											) )
										) // .hidden
									) // #minor-publishing-actions

									->div( array( 'id' => 'major-publishing-actions' ),
										static fn ( $element ) => $element
										->div( array( 'id' => 'delete-action' ),
											static fn ( $element ) => $element
											->input( array(
												'type' => 'submit',
												'name' => 'wpcf7-delete',
												'class' => 'delete submitdelete',
												'value' => __( 'Delete', 'wpcf7-entry-manager' ),
											) )
										) // #delete-action

										->div( array( 'id' => 'publishing-action' ),
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

						->section( array( 'id' => 'authordiv', 'class' => 'postbox' ),
							static fn ( $element ) => $element
							->div( array( 'class' => 'postbox-header' ),
								static fn ( $element ) => $element
								->h2( child: esc_html( __( 'Author', 'wpcf7-entry-manager' ) ))
								->div( array( 'class' => 'handle-actions hide-if-no-js' ),
									static fn ( $element ) => $element
										// Nothing for now
								), // .handle-actions
							) // .postbox-header

							->div( array( 'class' => 'inside' ),
								static fn ( $element ) => $element
									// Nothing for now
							), // .inside
						) // #authordiv
					) // #postbox-container-1

					->div( array( 'id' => 'postbox-container-2', 'class' => 'postbox-container' ),
						static fn ( $element ) => $element
						->section( array( 'id' => 'wpcf7em-viewer', 'class' => 'postbox', ),
							static fn ( $element ) => $element
							->div( array( 'class' => 'postbox-header' ),
								static fn ( $element ) => $element
								->h2( child: esc_html( __( 'Submission Entry', 'wpcf7-entry-manager' ) ))
								->div( array( 'class' => 'handle-actions hide-if-no-js' ),
									static fn ( $element ) => $element
										// Nothing for now
								), // .handle-actions
							) // .postbox-header
							->div( array( 'id' => 'wpcf7em-entry', 'class' => 'inside', ),
								static function ( $element ) use ( $item ) {
									foreach ( $item->form()->scan_form_tags() as $tag ) {
										/** @var \WPCF7_FormTag $tag */

										if ( in_array( $tag->basetype, array( 'submit', 'button' ) ) ) {
											continue;
										}

										$value = $item->submission[ $tag->name ] ?? '';

										$element->div( array(
											'class' => 'wpcf7em-row wpcf7em-submission ' . ( empty( $value ) ? 'field-no-answer' : 'field-answered' ),
										), static fn ( $element ) => $element
											->div( array( 'class' => 'wpcf7em-col wpcf7em-submission-field' ),
												static fn ( $element ) => $element->p( child: esc_html( $tag->name ) )
											)
											->div( array( 'class' => "wpcf7em-col wpcf7em-submission-value wpcf7em-type-{$tag->basetype}", ),
												static fn ( $element ) => match ( $tag->basetype ) {
													'number', 'range' => $element->p( child: esc_html( $value ) ),
													'date' => $element->p( child: esc_html( $value ) ),

													'tel' => $element->p( child: static fn ( $element ) => $element
														->a( array( 'href' => 'tel:' . esc_attr( $value ) ), esc_html( $value ) )
													),

				->div( array( 'id' => 'postbox-container-2', 'class' => 'postbox-container' ),
					static fn ( $element ) => $element
					->section( array( 'id' => 'wpcf7em-entry', 'class' => 'wpcf7em-box postbox', ),
						static fn ( $element ) => $element
						->header( array( 'class' => 'postbox-header' ),
							static fn ( $element ) => $element
							->h2( child: esc_html( __( 'Submission Entry', 'wpcf7-entry-manager' ) ))
							->div( array( 'class' => 'handle-actions hide-if-no-js' ),
								static fn ( $element ) => $element
									// Nothing for now
							), // .handle-actions
						) // .postbox-header
						->div( array( 'class' => 'inside' ),
							static function ( $element ) use ( $item ) {
								foreach ( $item->form()->scan_form_tags() as $tag ) {
									/** @var \WPCF7_FormTag $tag */

									if ( in_array( $tag->basetype, array( 'submit', 'button' ) ) ) {
										continue;
									}

									$value = $item->submission[ $tag->name ] ?? '';

									$element->div( array(
										'class' => 'wpcf7em-row wpcf7em-submission ' . ( empty( $value ) ? 'field-no-answer' : 'field-answered' ),
									), static fn ( $element ) => $element
										->div( array( 'class' => 'wpcf7em-col wpcf7em-submission-field' ),
											static fn ( $element ) => $element->p( child: esc_html( $tag->name ) )
										)
										->div( array( 'class' => "wpcf7em-col wpcf7em-submission-value wpcf7em-type-{$tag->basetype}", ),
											static fn ( $element ) => match ( $tag->basetype ) {
												'number', 'range' => $element->p( child: esc_html( $value ) ),
												'date' => $element->p( child: esc_html( $value ) ),

												'tel' => $element->p( child: static fn ( $element ) => $element
													->a( array( 'href' => 'tel:' . esc_attr( $value ) ), esc_html( $value ) )
												),

												'email' => $element->p( child: static fn ( $element ) => $element
													->a( array( 'href' => 'mailto:' . esc_attr( $value ) ), esc_html( $value ) )
												),

												'select', 'checkbox', 'radio' => $element->ol(
													child: static function ( $element ) use ( $tag, $value ) {
														foreach ( $tag->values as $i => $option ) {
															$element->li(
																array( 'class' => ( $value === $option ) ? 'selected' : '' ),
																esc_html( $option )
															);
														}
													}
												),

												'file' => $element->p( child: esc_html( $value ?: 'No file uploaded' ) ),

												'acceptance' => $element->when( boolval( $value ),
													static fn ( $element ) => $element
														->p( child: __( 'Accepted', 'cf7-entry-manager' ) )
												),

												default => $element->p( child: esc_html( $value ) ),
											}
										)
										->div( array( 'class' => 'wpcf7em-col wpcf7em-submission-info' ),
											static fn ( $element ) => $element
											->when( ! empty( $tag->options ), static fn ( $element ) => $element
												->span( array( 'class' => 'wpcf7em-submission-option' ),
													static function ( $element ) use ( $tag ) {
														$options = array_reduce( $tag->options, static function ( $carry, $option ) {
															if ( ! str_contains( $option, ':' ) ) {
																if ( $option !== 'optional' ) {
																	$carry[] = $option;
																}

																return $carry;
															}

															list( $key, $value ) = explode( ':', $option );

															$carry[] = sprintf( '%s: %s', $key, $value );

															return $carry;
														}, array() );

														if ( ! str_contains( $tag->type, '*' ) ) {
															array_unshift( $options, 'optional' );
														}

														$element->p( child: esc_html( __(
															sprintf( 'Options: %s', implode( ', ', $options ) )
														) ) );
													}
												)
											)
											->when( ! empty( $tag->content ), static fn ( $element ) => $element
												->p( array( 'class' => 'wpcf7em-submission-content' ), $tag->content )
											)
											->when( $tag->basetype === 'quiz', static fn ( $element ) => $element
												->p( child: __( 'Questions', 'cf7-entry-manager' ) )
												->ol(
													child: static function ( $element ) use ( $tag ) {
														foreach ( $tag->raw_values as $i => $option ) {
															list( $question, $answer ) = array_map( 'trim', explode( '|', $option ) );

															$element->li( child: static fn ( $element ) => $element
																->span( child: sprintf( '%s %s', $question, $answer ) )
															);
														}
													}
												)
											)
										)
									);
								}
							}
						) // #wpcf7em-entry
					) // #wpcf7em-viewer
				) // #postbox-container-2
			) // #post-body

			->clear()
		) // #poststuff
	) // #wpcf7-admin-form-element
); // #wpcf7em-submission-entry-viewer.wrap

$element->render();
