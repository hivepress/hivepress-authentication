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
	'authentication_frontend' => [
		'handle'  => 'hivepress-authentication-frontend',
		'src'     => hivepress()->get_url( 'authentication' ) . '/assets/js/frontend.min.js',
		'version' => hivepress()->get_version( 'authentication' ),
		'deps'    => [ 'hivepress-core' ],
	],
];
