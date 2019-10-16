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
	'auth_frontend' => [
		'handle'  => 'hp-auth-frontend',
		'src'     => HP_AUTHENTICATION_URL . '/assets/js/frontend.min.js',
		'version' => HP_AUTHENTICATION_VERSION,
		'deps'    => [ 'hp-core-frontend' ],
	],
];
