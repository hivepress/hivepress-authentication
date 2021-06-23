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
					'user_authenticate_action' => [
						'base'   => 'users_resource',
						'path'   => '/authenticate/(?P<authenticator>[a-z_]+)',
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

		// Check permissions.
		if ( is_user_logged_in() && ! current_user_can( 'create_users' ) ) {
			return hp\rest_error( 403 );
		}

		// Get authenticator.
		$authenticator = sanitize_key( $request->get_param( 'authenticator' ) );

		// Get response.
		$response = apply_filters( 'hivepress/v1/authenticators/' . $authenticator . '/response', [], $request->get_params() );

		if ( empty( $response ) || isset( $response['error'] ) ) {
			return hp\rest_error( 401 );
		}

		// Get user by authenticator ID.
		$user_object = reset(
			( get_users(
				[
					'meta_key'   => hp\prefix( $authenticator . '_id' ),
					'meta_value' => $response['id'],
					'number'     => 1,
				]
			) )
		);

		if ( empty( $user_object ) ) {

			// Get user by email.
			$user_object = get_user_by( 'email', $response['email'] );

			if ( $user_object ) {

				// Set authenticator ID.
				update_user_meta( $user_object->ID, hp\prefix( $authenticator . '_id' ), $response['id'] );
			}
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
				[
					'username'   => $username,
					'password'   => $password,
					'email'      => $response['email'],
					'first_name' => hp\get_array_value( $response, 'first_name' ),
					'last_name'  => hp\get_array_value( $response, 'last_name' ),
				]
			);

			if ( ! $user->save() ) {
				return hp\rest_error( 400, $user->_get_errors() );
			}

			// Update user name.
			$user->save( [ 'first_name', 'last_name' ] );

			// Set user object.
			$user_object = get_userdata( $user->get_id() );

			// Set authenticator ID.
			update_user_meta( $user->get_id(), hp\prefix( $authenticator . '_id' ), $response['id'] );

			do_action(
				'hivepress/v1/models/user/register',
				$user->get_id(),
				array_merge(
					$response,
					[
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
			do_action( 'hivepress/v1/models/user/login' );

			wp_set_auth_cookie( $user->get_id(), true );

			do_action( 'wp_login', $user->get_username(), $user_object );
		}

		return hp\rest_response(
			200,
			[
				'id' => $user->get_id(),
			]
		);
	}
}
