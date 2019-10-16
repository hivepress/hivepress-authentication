<?php
/**
 * Authentication component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Authentication component class.
 *
 * @class Authentication
 */
final class Authentication {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			// todo.
			add_filter( 'hivepress/v1/forms/form/args', [ $this, 'render_buttons' ], 10, 2 );
		}
	}

	// todo.
	public function render_buttons( $args, $form ) {
		if ( in_array( $form, [ 'user_login', 'user_register' ], true ) ) {
			$buttons = apply_filters( 'hivepress/v1/auth/buttons', '' );

			if ( '' !== $buttons ) {
				$buttons = preg_replace( '/(<br>)+$/', '', $buttons ) . '<hr>';
			}

			$args['header'] = hp\get_array_value( $args, 'header' ) . $buttons;
		}

		return $args;
	}
}
