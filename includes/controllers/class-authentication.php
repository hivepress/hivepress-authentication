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
final class Authentication extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					'user_auth_action' => [
						'base'   => 'users_resource',
						'path'   => '/auth/(?P<provider_name>[a-z]+)',
						'method' => 'POST',
						'action' => [ $this, 'authenticate_user' ],
						'rest'   => true,
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
		if ( ! is_user_logged_in() && ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
			return hp\rest_error( 401 );
		}

		// Check permissions.
		if ( is_user_logged_in() && ! current_user_can( 'create_users' ) ) {
			return hp\rest_error( 403 );
		}

		// Get provider.
		$provider = sanitize_key( $request->get_param( 'provider_name' ) );

		// Get response.
		$response = apply_filters( 'hivepress/v1/authenticators/' . $provider . '/response', [], $request->get_params() );

		if ( empty( $response ) || isset( $response['error'] ) ) {
			return hp\rest_error( 401 );
		}

		// Get user by provider ID.
		$user_object = reset(
			( get_users(
				[
					'meta_key'   => hp\prefix( $provider . '_id' ),
					'meta_value' => $response['id'],
					'number'     => 1,
				]
			) )
		);

		if ( empty( $user_object ) ) {

			// Get user by email.
			$user_object = get_user_by( 'email', $response['email'] );
		}

		if ( empty( $user_object ) ) {

			// Get username.
			$username = reset( ( explode( '@', $response['email'] ) ) );

			$username = sanitize_user( $username, true );

			if ( empty( $username ) ) {
				$username = 'user';
			}

			while ( username_exists( $username ) ) {
				$username .= wp_rand( 1, 9 );
			}

			// Get password.
			$password = wp_generate_password();

			// Register user.
			$user = ( new Models\User() )->fill(
				array_merge(
					$response,
					[
						'username' => $username,
						'password' => $password,
					]
				)
			);

			if ( ! $user->save() ) {
				return hp\rest_error( 400 );
			}

			// Set provider ID.
			update_user_meta( $user->get_id(), hp\prefix( $provider . '_id' ), $response['id'] );

			do_action(
				'hivepress/v1/models/user/register',
				$user->get_id(),
				array_merge(
					$response,
					[
						'username' => $username,
						'password' => $password,
					]
				)
			);
		} else {

			// Get user.
			$user = Models\User::query()->get_by_id( $user_object );
		}

		// Authenticate user.
		if ( ! is_user_logged_in() ) {
			wp_set_auth_cookie( $user->get_id(), true );
		}

		return hp\rest_response(
			200,
			[
				'id' => $user->get_id(),
			]
		);
	}
}
