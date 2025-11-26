<?php
/**
 * Plugin Name: HivePress Authentication
 * Description: Allow users to sign in via third-party services.
 * Version: 1.2.0
 * Author: HivePress
 * Author URI: https://hivepress.io/
 * Text Domain: hivepress-authentication
 * Domain Path: /languages/
 *
 * @package HivePress
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Register extension directory.
add_filter(
	'hivepress/v1/extensions',
	function( $extensions ) {
		$extensions[] = __DIR__;

		return $extensions;
	}
);
