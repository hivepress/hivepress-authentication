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
						'path'      => '/todo',
						'rest'      => true,

						'endpoints' => [
							[
								'path'    => '/todo',
								'methods' => 'POST',
								'action'  => 'todo',
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
	 * Todos user.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function todo( $request ) {
		$response = wp_remote_get(
			'https://graph.facebook.com/v4.0/me?' . http_build_query(
				[
					'fields'   => 'id,first_name,last_name,email',
					'accesss_token' => $request->get_param('access_token'),
				]
			)
		);

		//if(is_wp_error($response) || )



		return new \WP_Rest_Response(
			[
				'data' => [
					'id' => wp_remote_retrieve_body($response),
				],
			],
			200
		);
	}
}
