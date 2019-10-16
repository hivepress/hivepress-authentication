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
		'handle'  => 'hp-authentication-frontend',
		'src'     => HP_AUTHENTICATION_URL . '/assets/js/frontend.min.js',
		'version' => HP_AUTHENTICATION_VERSION,
		'deps'    => [ 'hp-core-frontend' ],
	],
];
