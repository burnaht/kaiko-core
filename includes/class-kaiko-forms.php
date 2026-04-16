<?php
/**
 * Forms Module.
 *
 * Handles the AJAX contact form on the KAIKO Contact page.
 * Validates input, checks a honeypot field for spam, and sends
 * the message via wp_mail().
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

class Kaiko_Forms {

	/** Email recipient. */
	const RECIPIENT = 'info@kaikoproducts.com';

	/** Allowed subject keys → labels. */
	const SUBJECTS = [
		'general'  => 'General Enquiry',
		'trade'    => 'Trade Account',
		'product'  => 'Product Question',
		'returns'  => 'Returns',
	];

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'wp_ajax_kaiko_contact',        [ $this, 'handle' ] );
		add_action( 'wp_ajax_nopriv_kaiko_contact',  [ $this, 'handle' ] );
	}

	/**
	 * AJAX handler — validate, check spam, send mail.
	 */
	public function handle(): void {
		// Nonce check
		if ( ! check_ajax_referer( 'kaiko_contact_nonce', '_nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Security check failed. Please refresh and try again.' ], 403 );
		}

		// Honeypot — bots fill the hidden field
		$honeypot = isset( $_POST['kaiko_website'] ) ? sanitize_text_field( wp_unslash( $_POST['kaiko_website'] ) ) : '';
		if ( '' !== $honeypot ) {
			// Pretend success so bots don't retry
			wp_send_json_success( [ 'message' => 'Thanks! Your message has been sent.' ] );
		}

		// Sanitise fields
		$name    = isset( $_POST['name'] )    ? sanitize_text_field( wp_unslash( $_POST['name'] ) )    : '';
		$email   = isset( $_POST['email'] )   ? sanitize_email( wp_unslash( $_POST['email'] ) )        : '';
		$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
		$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

		// Validate
		$errors = [];
		if ( '' === $name ) {
			$errors[] = 'Please enter your name.';
		}
		if ( ! is_email( $email ) ) {
			$errors[] = 'Please enter a valid email address.';
		}
		if ( ! isset( self::SUBJECTS[ $subject ] ) ) {
			$errors[] = 'Please select a subject.';
		}
		if ( '' === $message ) {
			$errors[] = 'Please enter a message.';
		}

		if ( $errors ) {
			wp_send_json_error( [ 'message' => implode( ' ', $errors ) ], 422 );
		}

		// Build email
		$subject_label = self::SUBJECTS[ $subject ];
		$mail_subject  = sprintf( '[KAIKO Contact] %s from %s', $subject_label, $name );

		$mail_body  = "Name: {$name}\n";
		$mail_body .= "Email: {$email}\n";
		$mail_body .= "Subject: {$subject_label}\n\n";
		$mail_body .= "Message:\n{$message}\n";

		$headers = [
			'Content-Type: text/plain; charset=UTF-8',
			sprintf( 'Reply-To: %s <%s>', $name, $email ),
		];

		$sent = wp_mail( self::RECIPIENT, $mail_subject, $mail_body, $headers );

		if ( $sent ) {
			wp_send_json_success( [ 'message' => 'Thanks! Your message has been sent. We\'ll be in touch soon.' ] );
		} else {
			wp_send_json_error( [ 'message' => 'Something went wrong sending your message. Please try emailing us directly.' ], 500 );
		}
	}
}
