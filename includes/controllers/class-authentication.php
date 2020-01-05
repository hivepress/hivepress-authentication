<?php
/**
 * Authentication controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Authentication controller class.
 *
 * @class Authentication
 */
class Authentication extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					[
						'path'   => '/auth',
						'rest'   => true,

						'routes' => [
							[
								'path'   => '/(?P<provider>[a-z]+)',
								'method' => 'POST',
								'action' => [ $this, 'authenticate_user' ],
							],
						],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
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

		// Get provider.
		$provider = sanitize_key( $request->get_param( 'provider' ) );

		// Filter response.
		$response = apply_filters( 'hivepress/v1/auth/response', [], $request->get_params(), $provider );

		if ( empty( $response ) || isset( $response['error'] ) ) {
			return hp\rest_error( 401 );
		}

		// Get user by provider ID.
		$users = get_users(
			[
				'meta_key'   => hp\prefix( $provider ) . '_id',
				'meta_value' => $response['id'],
				'number'     => 1,
			]
		);

		if ( ! empty( $users ) && hp\get_array_value( $response, 'id' ) ) {
			$user = reset( $users );
		} else {

			// Get user by email.
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
			$user = Models\User::query()->get_by_id( $user_id );

			// Set provider ID.
			update_user_meta( $user_id, hp\prefix( $provider ) . '_id', $response['id'] );

			// Set name.
			update_user_meta( $user_id, 'first_name', $response['first_name'] );
			update_user_meta( $user_id, 'last_name', $response['last_name'] );

			do_action( 'hivepress/v1/models/user/register', $user_id, $user );
		} else {
			$user_id = $user->ID;
		}

		// Authenticate user.
		if ( ! is_user_logged_in() ) {
			wp_set_auth_cookie( $user_id, true );
		}

		return new \WP_Rest_Response(
			[
				'data' => [
					'id' => $user_id,
				],
			],
			200
		);
	}
}
