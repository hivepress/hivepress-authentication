<?php
/**
 * Facebook authentication component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Facebook authentication component class.
 *
 * @class Facebook_Authentication
 */
final class Facebook_Authentication extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Add admin notices.
		add_filter( 'hivepress/v1/admin_notices', [ $this, 'add_admin_notices' ] );

		// Check Facebook status.
		if ( ! in_array( 'facebook', (array) get_option( 'hp_user_auth_methods' ), true ) || ! get_option( 'hp_facebook_app_id' ) || ! get_option( 'hp_facebook_app_secret' ) ) {
			return;
		}

		// Set response.
		add_filter( 'hivepress/v1/authenticators/facebook/response', [ $this, 'set_response' ], 10, 2 );

		if ( ! is_user_logged_in() && ! is_admin() ) {

			// Enqueue scripts.
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

			// Render footer.
			add_action( 'wp_footer', [ $this, 'render_footer' ] );

			// Render button.
			add_filter( 'hivepress/v1/forms/user_authenticate/header', [ $this, 'render_button' ] );
		}

		parent::__construct( $args );
	}

	/**
	 * Adds admin notices.
	 *
	 * @param array $notices Admin notices.
	 * @return array
	 */
	public function add_admin_notices( $notices ) {
		if ( in_array( 'facebook', (array) get_option( 'hp_user_auth_methods' ), true ) && ! get_option( 'hp_facebook_app_secret' ) ) {
			$notices['facebook_app_secret_required'] = [
				'type' => 'error',
				/* translators: %s: settings link. */
				'text' => sprintf( hp\sanitize_html( __( 'Please add an App Secret for %s to continue working.', 'hivepress-authentication' ) ), '<a href="' . esc_url( admin_url( 'admin.php?page=hp_settings&tab=integrations' ) ) . '">Facebook Login</a>' ),
			];
		}

		return $notices;
	}

	/**
	 * Sets response.
	 *
	 * @param array $response Response data.
	 * @param array $request Request data.
	 * @return mixed
	 */
	public function set_response( $response, $request ) {

		// Get access token.
		$access_token = hp\get_array_value( $request, 'access_token' );

		// Get token data.
		$token_data = hp\get_array_value(
			json_decode(
				wp_remote_retrieve_body(
					wp_remote_get(
						'https://graph.facebook.com/debug_token?' . http_build_query(
							[
								'input_token'  => $access_token,
								'access_token' => get_option( 'hp_facebook_app_id' ) . '|' . get_option( 'hp_facebook_app_secret' ),
							]
						)
					)
				),
				true
			),
			'data',
			[]
		);

		// Verify app ID.
		if ( ! hp\get_array_value( $token_data, 'is_valid' ) || get_option( 'hp_facebook_app_id' ) !== (string) hp\get_array_value( $token_data, 'app_id' ) ) {
			return [ 'error' => 'invalid_client' ];
		}

		return json_decode(
			wp_remote_retrieve_body(
				wp_remote_get(
					'https://graph.facebook.com/v4.0/me?' . http_build_query(
						[
							'fields'       => 'id,first_name,last_name,email',
							'access_token' => $access_token,
						]
					)
				)
			),
			true
		);
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
	 * @param string $output Header HTML.
	 * @return string
	 */
	public function render_button( $output ) {
		return $output . '<div class="fb-login-button" data-width="" data-size="large" data-button-type="login_with" data-auto-logout-link="false" data-use-continue-as="false" data-scope="email" data-onlogin="onFacebookAuth"></div><br><br>';
	}
}
