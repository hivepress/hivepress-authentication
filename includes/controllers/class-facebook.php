<?php
/**
 * Facebook controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Facebook controller class.
 *
 * @class Facebook
 */
class Facebook extends Controller {

	/**
	 * Controller name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Controller routes.
	 *
	 * @var array
	 */
	protected static $routes = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Controller arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					[
						'path'      => '/users',
						'rest'      => true,

						'endpoints' => [
							[
								'path'    => '/login/(?P<provider>[a-z]+)',
								'methods' => 'POST',
								'action'  => 'authenticate_user',
							],
						],
					],
				],
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Authenticates user.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function authenticate_user( $request ) {

		// Check authentication.
		$nonce = hp\get_array_value( $request->get_params(), '_wpnonce', $request->get_header( 'X-WP-Nonce' ) );

		if ( ! is_user_logged_in() && ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return hp\rest_error( 401 );
		}

		// Check permissions.
		if ( is_user_logged_in() && ! current_user_can( 'create_users' ) ) {
			return hp\rest_error( 403 );
		}

		$response = json_decode(
			wp_remote_retrieve_body(
				wp_remote_get(
					'https://graph.facebook.com/v4.0/me?' . http_build_query(
						[
							'fields'       => 'id,first_name,last_name,email',
							'access_token' => $request->get_param( 'access_token' ),
						]
					)
				)
			),
			true
		);

		if ( empty( $response ) || isset( $response['error'] ) ) {
			return hp\rest_error( 401 );
		}

		$users = get_users(
			[
				'meta_key'   => 'hp_facebook_id',
				'meta_value' => $response['id'],
				'number'     => 1,
			]
		);

		if ( ! empty( $users ) ) {
			$user = reset( $users );
		} else {
			$user = get_user_by( 'email', $response['email'] );
		}

		if ( false === $user ) {

			// Get username.
			list($username, $domain) = explode( '@', $response['email'] );

			$username = sanitize_user( $username, true );

			if ( '' === $username ) {
				$username = 'user';
			}

			while ( username_exists( $username ) ) {
				$username .= wp_rand( 1, 9 );
			}

			// Register user.
			$user_id = wp_create_user( $username, wp_generate_password(), $response['email'] );

			if ( is_wp_error( $user_id ) ) {
				return hp\rest_error( 400 );
			}

			// Get user.
			$user = get_userdata( $user_id );

			// Set Facebook ID.
			update_user_meta( $user->ID, 'hp_facebook_id', $response['id'] );

			// Set name.
			update_user_meta( $user->ID, 'first_name', $response['first_name'] );
			update_user_meta( $user->ID, 'last_name', $response['last_name'] );

			// todo action.
		}

		// Authenticate user.
		if ( ! is_user_logged_in() ) {
			wp_set_auth_cookie( $user->ID, true );
		}

		return new \WP_Rest_Response(
			[
				'data' => [
					'id' => $user->ID,
				],
			],
			200
		);
	}
}
