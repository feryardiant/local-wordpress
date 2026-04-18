<?php
/**
 * @package feryardiant/cf7-entry-manager
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 */

namespace CF7_Entry_Manager;

use ArrayAccess;
use WPCF7_ContactForm;
use WPCF7_Submission;

final class Option implements ArrayAccess {
	public readonly array $defaults;

	/**
	 * Whether to record the submissions to the database.
	 */
	public bool $should_record;

	/**
	 * The field key that is identified as a submission subject.
	 */
	public string $subject_field;

	/**
	 * The configured value of {$subject_field}.
	 */
	public ?string $subject = null;

	/**
	 * The field key that is identified as a submission message.
	 */
	public string $message_field;

	/**
	 * The configured value of {$message_field}.
	 */
	public ?string $message = null;

	/**
	 * Whether to store submission author as a subscriber.
	 */
	public bool $store_author;

	/**
	 * The field key that is identified as a submission name.
	 */
	public string $name_field;

	/**
	 * The configured value of {$name_field}.
	 */
	public ?string $name = null;

	/**
	 * The field key that is identified as a submission email.
	 */
	public string $email_field;

	/**
	 * The configured value of {$email_field}.
	 */
	public ?string $email = null;

	/**
	 * The field key that is identified as a submission phone.
	 */
	public string $phone_field;

	/**
	 * The configured value of {$phone_field}.
	 */
	public ?string $phone = null;

	private array $form_data = array();

	private array $field_map = array(
		'subject' => 'subject_field',
		'message' => 'message_field',
		'name' => 'name_field',
		'email' => 'email_field',
		'phone' => 'phone_field',
	);

	/**
	 * Get all available options for the given $contact_form.
	 */
	public static function get( WPCF7_ContactForm $contact_form ): Option|false {
		$option = new self( $contact_form );
		$submission = WPCF7_Submission::get_instance();

		if ( ! $submission || ! $option->should_record ) {
			return false;
		}

		foreach ( $contact_form->scan_form_tags() as $tag ) {
			/** @var \WPCF7_FormTag $tag */
			if ( 'submit' === $tag->basetype ) {
				continue;
			}

			$option->form_data[$tag->name] = $submission->get_posted_string( $tag->name );
		}

		foreach ( $option->field_map as $key => $field ) {
			$option[$key] = $option->form_data[$option[$field]] ?? null;
		}

		return $option;
	}

	public function __construct(
		private WPCF7_ContactForm $contact_form
	) {
		$this->defaults = array(
			'should_record' => null,
			'subject_field' => '',
			'message_field' => '',
			'store_author' => null,
			'name_field' => '',
			'email_field' => '',
			'phone_field' => '',
		);

		$properties = \wp_parse_args( $contact_form->prop( 'submissions' ), $this->defaults );
		$boolean_keys = [ 'should_record', 'store_author' ];

		foreach ( $properties as $key => $value ) {
			$this->$key = in_array( $key, $boolean_keys )
				? ! is_null( $value )
				: $value;
		}
	}

	/**
	 * Get the form fields for the submission option form.
	 *
	 * @return array<string, array{label: string, description: string, type: string, atts: array, options: array}>
	 */
	public function fields() {
		$mail_tags = $this->contact_form->collect_mail_tags();

		return array(
			'should_record' => array(
				'label' => \esc_html__( 'Record', 'cf7-entry-manager' ),
				'description' => \esc_html__(
					'Whether to record the submissions to the database', 'cf7-entry-manager'
				),
				'atts' => array( 'type' => 'checkbox' ),
			),
			'subject_field' => array(
				'label' => \esc_html( __( 'Subject', 'cf7-entry-manager' ) ),
				'description' => \esc_html__(
				'Choose which field is identified as a submission subject', 'cf7-entry-manager'
				),
				'type' => 'select',
				'atts' => array( 'class' => 'large-text code' ),
				'options' => $mail_tags,
			),
			'message_field' => array(
				'label' => \esc_html__( 'Message', 'cf7-entry-manager' ),
				'description' => \esc_html__(
					'Choose which field is identified as a submission message', 'cf7-entry-manager'
				),
				'type' => 'select',
				'atts' => array( 'class' => 'large-text code' ),
				'options' => $mail_tags,
			),
			'sep-1' => array( 'type' => 'separator' ),
			'store_author' => array(
				'label' => \esc_html__( 'Author', 'cf7-entry-manager' ),
				'description' => \esc_html__(
					'Whether the submission author will be registered as subscriber', 'cf7-entry-manager'
				),
				'atts' => array( 'type' => 'checkbox' ),
			),
			'name_field' => array(
				'label' => \esc_html__( 'Author Name', 'cf7-entry-manager' ),
				'description' => \esc_html__(
					'Choose which field is identified as the submitter\'s name', 'cf7-entry-manager'
				),
				'type' => 'select',
				'atts' => array( 'class' => 'large-text code' ),
				'options' => $mail_tags,
			),
			'email_field' => array(
				'label' => \esc_html__( 'Author Email', 'cf7-entry-manager' ),
				'description' => \esc_html__(
					'Choose which field is identified as the submitter\'s email', 'cf7-entry-manager'
				),
				'type' => 'select',
				'atts' => array( 'class' => 'large-text code' ),
				'options' => $mail_tags,
			),
			'phone_field' => array(
				'label' => \esc_html__( 'Author Phone', 'cf7-entry-manager' ),
				'description' => \esc_html__(
					'Choose which field is identified as the submitter\'s phone number', 'cf7-entry-manager'
				),
				'type' => 'select',
				'atts' => array( 'class' => 'large-text code' ),
				'options' => $mail_tags,
			),
		);
	}

	/**
	 * Get the form data for the submission option form.
	 *
	 * @return array<string, mixed>
	 */
	public function form_data() {
		return $this->form_data;
	}

	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->$offset ?? null;
	}

	public function offsetSet( $offset, $value ): void {
		if ( array_key_exists( $offset, $this->field_map ) ) {
			$this->$offset = $value;
		}
	}

	public function offsetUnset( $offset ): void {
		// Doing nothing.
	}

	public function offsetExists( $offset ): bool {
		return property_exists( $this, $offset );
	}
}
