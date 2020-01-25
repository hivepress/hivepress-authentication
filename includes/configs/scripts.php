<?php
/**
 * Scripts configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'authentication' => [
		'handle'  => 'hivepress-authentication',
		'src'     => hivepress()->get_url( 'authentication' ) . '/assets/js/common.min.js',
		'version' => hivepress()->get_version( 'authentication' ),
		'deps'    => [ 'hivepress-core' ],

		'data'    => [
			'apiURL' => hivepress()->router->get_url(
				'user_authenticate_action',
				[
					'authenticator' => '',
				]
			),
		],
	],
];
