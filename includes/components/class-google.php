<?php
/**
 * Google component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Google component class.
 *
 * @class Google
 */
final class Google {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Check Google status.
		if ( is_user_logged_in() || ! in_array( 'google', (array) get_option( 'hp_user_auth_methods' ), true ) || get_option( 'hp_google_client_id' ) === '' ) {
			return;
		}

		// Set response.
		add_filter( 'hivepress/v1/auth/response', [ $this, 'set_response' ], 10, 3 );

		if ( ! is_admin() ) {

			// Enqueue scripts.
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

			// Render header.
			add_action( 'wp_head', [ $this, 'render_header' ] );

			// Render button.
			add_filter( 'hivepress/v1/auth/buttons', [ $this, 'render_button' ] );
		}
	}

	/**
	 * Sets response.
	 *
	 * @param array  $response Response data.
	 * @param array  $request Request data.
	 * @param string $provider Provider name.
	 * @return mixed
	 */
	public function set_response( $response, $request, $provider ) {
		if ( 'google' === $provider ) {

			// Get response.
			$response = json_decode(
				wp_remote_retrieve_body(
					wp_remote_get(
						'https://oauth2.googleapis.com/tokeninfo?' . http_build_query(
							[
								'id_token' => $request['id_token'],
							]
						)
					)
				),
				true
			);

			if ( ! empty( $response ) && ! isset( $response['error'] ) ) {

				// Check client ID.
				if ( get_option( 'hp_google_client_id' ) !== $response['aud'] ) {
					return [ 'error' => 'invalid_client' ];
				}

				// Check email.
				if ( 'true' !== $response['email_verified'] ) {
					return [ 'error' => 'unverified_email' ];
				}

				// Set details.
				$response['id']         = $response['sub'];
				$response['first_name'] = $response['given_name'];
				$response['last_name']  = $response['family_name'];
			}
		}

		return $response;
	}

	/**
	 * Enqueues scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'google-platform', 'https://apis.google.com/js/platform.js', [], null, true );

		wp_script_add_data( 'google-platform', 'async', true );
		wp_script_add_data( 'google-platform', 'defer', true );
	}

	/**
	 * Renders header.
	 */
	public function render_header() {
		echo '<meta name="google-signin-client_id" content="' . esc_attr( get_option( 'hp_google_client_id' ) ) . '">';
	}

	/**
	 * Renders button.
	 *
	 * @param string $output Button HTML.
	 * @return string
	 */
	public function render_button( $output ) {
		return $output . '<div class="g-signin2" data-theme="dark" data-height="40" data-longtitle="true" data-onsuccess="onGoogleAuth"></div><br><br>';
	}
}
