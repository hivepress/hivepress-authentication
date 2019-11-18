<?php
/**
 * Plugin Name: HivePress Authentication
 * Description: Allow users to sign in via third-party services.
 * Version: 1.0.1
 * Author: HivePress
 * Author URI: https://hivepress.io/
 * Text Domain: hivepress-authentication
 * Domain Path: /languages/
 *
 * @package HivePress
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Register plugin directory.
add_filter(
	'hivepress/v1/dirs',
	function( $dirs ) {
		return array_merge( $dirs, [ __DIR__ ] );
	}
);
