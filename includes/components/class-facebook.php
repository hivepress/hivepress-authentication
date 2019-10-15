<?php
/**
 * Facebook component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Facebook component class.
 *
 * @class Facebook
 */
final class Facebook {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Add form button.
		add_filter( 'hivepress/v1/forms/user_login/args', [ $this, 'add_form_button' ] );
		add_filter( 'hivepress/v1/forms/user_register/args', [ $this, 'add_form_button' ] );
	}

	// todo
	public function add_form_button( $form ) {
		$form['header'].= '<a href="#" class="button">Button</a>';

		return $form;
	}
}
