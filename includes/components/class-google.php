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
		if ( ! in_array( 'google', (array) get_option( 'hp_user_authentication_methods' ), true ) || get_option( 'hp_google_client_id' ) === '' ) {
			return;
		}

		// todo.
		add_filter( 'hivepress/v1/todo/google', [ $this, 'todo' ], 10, 2 );

		if ( ! is_admin() ) {

			// Enqueue scripts.
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

			// Render header.
			add_action( 'wp_head', [ $this, 'render_header' ] );
		}
	}

	// todo.
	public function todo( $response, $request ) {
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

			if ( $response['aud'] !== get_option( 'hp_google_client_id' ) ) {
				return [ 'error' => 'invalid_client' ];
			}

			if ( $response['email_verified'] !== 'true' ) {
				return [ 'error' => 'unverified_email' ];
			}

			$response['id']         = $response['sub'];
			$response['first_name'] = $response['given_name'];
			$response['last_name']  = $response['family_name'];
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
}
