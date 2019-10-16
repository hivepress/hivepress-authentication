<?php
/**
 * Facebook component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Facebook component class.
 *
 * @class Facebook
 */
final class Facebook {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Check Facebook status.
		if ( is_user_logged_in() || ! in_array( 'facebook', (array) get_option( 'hp_user_auth_methods' ), true ) || ! get_option( 'hp_facebook_app_id' ) ) {
			return;
		}

		// Set response.
		add_filter( 'hivepress/v1/auth/response', [ $this, 'set_response' ], 10, 3 );

		if ( ! is_admin() ) {

			// Enqueue scripts.
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

			// Render footer.
			add_action( 'wp_footer', [ $this, 'render_footer' ] );

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
		if ( 'facebook' === $provider ) {
			$response = json_decode(
				wp_remote_retrieve_body(
					wp_remote_get(
						'https://graph.facebook.com/v4.0/me?' . http_build_query(
							[
								'fields'       => 'id,first_name,last_name,email',
								'access_token' => $request['access_token'],
							]
						)
					)
				),
				true
			);
		}

		return $response;
	}

	/**
	 * Enqueues scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'facebook-sdk',
			'https://connect.facebook.net/' . get_locale() . '/sdk.js#' . http_build_query(
				[
					'version'          => 'v4.0',
					'xfbml'            => '1',
					'autoLogAppEvents' => '1',
					'appId'            => get_option( 'hp_facebook_app_id' ),
				]
			),
			[],
			null,
			true
		);

		wp_script_add_data( 'facebook-sdk', 'async', true );
		wp_script_add_data( 'facebook-sdk', 'defer', true );
		wp_script_add_data( 'facebook-sdk', 'crossorigin', 'anonymous' );
	}

	/**
	 * Renders footer.
	 */
	public function render_footer() {
		echo '<div id="fb-root"></div>';
	}

	/**
	 * Renders button.
	 *
	 * @param string $output Button HTML.
	 * @return string
	 */
	public function render_button( $output ) {
		return $output . '<div class="fb-login-button" data-width="" data-size="large" data-button-type="login_with" data-auto-logout-link="false" data-use-continue-as="false" data-scope="email" data-onlogin="onFacebookAuth"></div><br><br>';
	}
}
