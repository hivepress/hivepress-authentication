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
final class Authentication extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {
		if ( ! is_user_logged_in() && ! is_admin() ) {

			// Render form header.
			add_filter( 'hivepress/v1/forms/user_login', [ $this, 'render_form_header' ] );
			add_filter( 'hivepress/v1/forms/user_register', [ $this, 'render_form_header' ] );
		}

		parent::__construct( $args );
	}

	/**
	 * Renders form header.
	 *
	 * @param array $args Form arguments.
	 * @return array
	 */
	public function render_form_header( $args ) {

		// Get header.
		$header = apply_filters( 'hivepress/v1/forms/user_authenticate/header', '' );

		// Format header.
		if ( $header ) {
			$header = preg_replace( '/(<br>)+$/', '', $header ) . '<hr>';
		}

		// Set header.
		$args['header'] = hp\get_array_value( $args, 'header' ) . $header;

		return $args;
	}
}
