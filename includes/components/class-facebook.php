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
		if ( ! in_array( 'facebook', (array) get_option( 'hp_user_authentication_methods' ), true ) || get_option( 'hp_facebook_app_id' ) === '' || get_option( 'hp_facebook_app_secret' ) === '' ) {
			return;
		}

		if ( ! is_admin() ) {

			// Enqueue scripts.
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

			// Render footer.
			add_action( 'wp_footer', [ $this, 'render_footer' ] );
		}
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
}
