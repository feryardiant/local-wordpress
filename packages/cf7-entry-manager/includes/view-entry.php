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

				->div( array( 'id' => 'postbox-container-1', 'class' => 'postbox-container' ),
					static fn ( $element ) => $element
					->section( array( 'id' => 'wpcf7em-info', 'class' => 'wpcf7em-box postbox' ),
						static fn ( $element ) => $element
						->header( array( 'class' => 'postbox-header' ),
							static fn ( $element ) => $element
							->h2( child: esc_html( __( 'Info', 'wpcf7-entry-manager' ) ) )
							->div( array( 'class' => 'handle-actions hide-if-no-js' ),
								static fn ( $element ) => $element
								// Nothing for now
							), // .handle-actions
						) // .postbox-header

						->div( array( 'class' => 'inside' ),
							static fn ( $element ) => $element
							->div( array( 'class' => 'wpcf7em-row wpcf7em-info' ),
								static fn ( $element ) => $element
								->div( array( 'class' => 'wpcf7em-col wpcf7em-info-field' ),
									static fn ( $element ) => $element
									->p( child: __( 'Submitted', 'wpcf7-entry-manager' ) )
								)
								->div( array( 'class' => 'wpcf7em-col wpcf7em-info-value' ),
									static fn ( $element ) => $element
									->p( child: esc_html( $item->datetime?->format( 'Y-m-d H:i:s' ) ) )
								)
							) // .wpcf7em-row

							->div( array( 'class' => 'wpcf7em-row wpcf7em-info' ),
								static fn ( $element ) => $element
								->div( array( 'class' => 'wpcf7em-col wpcf7em-info-field' ),
									static fn ( $element ) => $element
									->p( child: __( 'Form', 'wpcf7-entry-manager' ) )
								)
								->div( array( 'class' => 'wpcf7em-col wpcf7em-info-value ' . ( $item->form_id ? '' : 'wpcf7em-no-value' ) ),
									static fn ( $element ) => $element
									->p( child: ( $form = $item->form() ) ? esc_html( $form->title() ) : sprintf(
										'<span aria-hidden="true">—</span><span class="screen-reader-text">(%s)</span>',
										__( 'no form', 'wpcf7-entry-manager' )
									) )
								)
							) // .wpcf7em-row

							->div( array( 'class' => 'wpcf7em-row wpcf7em-info' ),
								static fn ( $element ) => $element
								->div( array( 'class' => 'wpcf7em-col wpcf7em-info-field' ),
									static fn ( $element ) => $element
									->img( array(
										'class' => 'avatar photo',
										'src' => get_avatar_url( $item->author_id ),
										'loading' => 'lazy',
									) )
								)
								->div( array( 'class' => 'wpcf7em-col wpcf7em-info-value ' . ( $item->author_id ? '' : 'wpcf7em-no-value' ) ),
									static fn ( $element ) => $element
									->p( array( 'class' => $item->author_name ? '' : 'wpcf7em-no-value' ),
										$item->author_name ? esc_html( $item->author_name ) : sprintf(
											'<span aria-hidden="true">%s</span><span class="screen-reader-text">(%s)</span>',
											__( 'Anonymous', 'wpcf7-entry-manager' ),
											__( 'no author info', 'wpcf7-entry-manager' )
										)
									)
									->p( array( 'class' => $item->author_email ? '' : 'wpcf7em-no-value' ),
										$item->author_email ? esc_html( $item->author_email ) : sprintf(
											'<span aria-hidden="true">—</span><span class="screen-reader-text">(%s)</span>',
											__( 'no email info', 'wpcf7-entry-manager' )
										)
									)
									->p( array( 'class' => $item->author_phone ? '' : 'wpcf7em-no-value' ),
										$item->author_phone ? esc_html( $item->author_phone ) : sprintf(
											'<span aria-hidden="true">—</span><span class="screen-reader-text">(%s)</span>',
											__( 'no phone info', 'wpcf7-entry-manager' )
										)
									)
								)
							) // .wpcf7em-row
						), // .inside
					) // #wpcf7em-info
				) // #postbox-container-1

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

									if ( 'submit' === $tag->basetype ) {
										continue;
									}

									$value = $item->submission[ $tag->name ] ?? '';
									$has_value = '' !== $value && null !== $value;

									$element->div( array(
										'class' => 'wpcf7em-row wpcf7em-submission ' . ( $has_value ? 'field-answered' : 'field-no-answer' ),
									), static fn ( $element ) => $element
										->div( array( 'class' => 'wpcf7em-col wpcf7em-submission-field' ),
											static fn ( $element ) => $element->p( child: esc_html( $tag->name ) )
										)
										->div( array( 'class' => "wpcf7em-col wpcf7em-submission-value wpcf7em-type-{$tag->basetype}", ),
											static fn ( $element ) => match ( $tag->basetype ) {
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

												'file' => $element->p( child: esc_html( $has_value ? $value : __( 'No file uploaded', 'wpcf7-entry-manager' ) ) ),

												'acceptance' => $element->p( child: boolval( $value )
													? __( 'Accepted', 'wpcf7-entry-manager' )
													: __( 'Not accepted', 'wpcf7-entry-manager' )
												),

												default => $element->p( child: esc_html( $has_value ? $value : __( 'No answer', 'wpcf7-entry-manager' ) ) ),
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

														$element->p( child: esc_html( sprintf(
															/* translators: %s: comma-separated list of form tag options */
															__( 'Options: %s', 'wpcf7-entry-manager' ),
															implode( ', ', $options )
														) ) );
													}
												)
											)
											->when( ! empty( $tag->content ), static fn ( $element ) => $element
												->p( array( 'class' => 'wpcf7em-submission-content' ), $tag->content )
											)
											->when( $tag->basetype === 'quiz', static fn ( $element ) => $element
												->p( child: __( 'Questions', 'wpcf7-entry-manager' ) )
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
