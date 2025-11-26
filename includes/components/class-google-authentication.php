<?php
/**
 * Google authentication component.
 *
 * @package HivePress\Components
 */
namespace HivePress\Components;
use HivePress\Helpers as hp;
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
/**
 * Google authentication component class.
 *
 * @class Google_Authentication
 */
final class Google_Authentication extends Component {
	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {
		// Check Google status.
		if ( ! in_array( 'google', (array) get_option( 'hp_user_auth_methods' ), true ) || ! get_option( 'hp_google_client_id' ) ) {
			return;
		}
		// Set response.
		add_filter( 'hivepress/v1/authenticators/google/response', [ $this, 'set_response' ], 10, 2 );
		if ( ! is_user_logged_in() && ! is_admin() ) {
			// Enqueue scripts.
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
			// Render header.
			add_action( 'wp_head', [ $this, 'render_header' ] );
			// Render button.
			add_filter( 'hivepress/v1/forms/user_authenticate/header', [ $this, 'render_button' ] );
			// Render footer initialization.
			add_action( 'wp_footer', [ $this, 'render_footer' ] );
		}
		parent::__construct( $args );
	}
	/**
	 * Sets response.
	 *
	 * @param array $response Response data.
	 * @param array $request Request data.
	 * @return mixed
	 */
	public function set_response( $response, $request ) {
		// Get response.
		$response = json_decode(
			wp_remote_retrieve_body(
				wp_remote_get(
					'https://oauth2.googleapis.com/tokeninfo?' . http_build_query(
						[
							'id_token' => hp\get_array_value( $request, 'id_token' ),
						]
					)
				)
			),
			true
		);
		if ( $response && ! isset( $response['error'] ) ) {
			// Check client ID.
			if ( get_option( 'hp_google_client_id' ) !== $response['aud'] ) {
				return [ 'error' => 'invalid_client' ];
			}
			// Check email status.
			if ( 'true' !== $response['email_verified'] ) {
				return [ 'error' => 'unverified_email' ];
			}
			// Set user details.
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
		// UPDATED: Load new Google Identity Services library instead of old platform.js
		wp_enqueue_script( 'google-identity-services', 'https://accounts.google.com/gsi/client', [], null, true );
		wp_script_add_data( 'google-identity-services', 'async', true );
		wp_script_add_data( 'google-identity-services', 'defer', true );
	}
	/**
	 * Renders header.
	 */
	public function render_header() {
		// Keep the meta tag for backwards compatibility, though new API doesn't require it
		echo '<meta name="google-signin-client_id" content="' . esc_attr( get_option( 'hp_google_client_id' ) ) . '">';
	}
	/**
	 * Renders button.
	 *
	 * @param string $output Header HTML.
	 * @return string
	 */
	public function render_button( $output ) {
		// UPDATED: Use a simple div container where Google will render the button
		return $output . '<div id="g-signin-button"></div><br><br>';
	}
	/**
	 * Renders footer with Google Sign-In initialization.
	 * ADDED: New method to initialize the new Google Identity Services API
	 */
	public function render_footer() {
		$client_id = esc_js( get_option( 'hp_google_client_id' ) );
		?>
		<script type="text/javascript">
		(function() {
			function initializeGoogleSignIn() {
				if (typeof google !== 'undefined' && google.accounts && google.accounts.id) {
					// Initialize Google Identity Services
					google.accounts.id.initialize({
						client_id: '<?php echo $client_id; ?>',
						callback: window.onGoogleAuth
					});
					
					// Render the Sign-In button
					var buttonDiv = document.getElementById('g-signin-button');
					if (buttonDiv) {
						google.accounts.id.renderButton(
							buttonDiv,
							{
								theme: 'outline',
								size: 'large',
								text: 'signin_with',
								shape: 'rectangular'
							}
						);
					}
				} else {
					// Retry if Google library hasn't loaded yet
					setTimeout(initializeGoogleSignIn, 50);
				}
			}
			
			// Initialize when DOM is ready
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', initializeGoogleSignIn);
			} else {
				initializeGoogleSignIn();
			}
		})();
		</script>
		<?php
	}
}
