<?php
/**
 * Settings configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'users'        => [
		'sections' => [
			'registration' => [
				'fields' => [
					'user_auth_methods' => [
						'label'       => esc_html__( 'Authentication Methods', 'hivepress-authentication' ),
						'description' => esc_html__( 'Select the available authentication methods. Each method requires the API credentials that you can set in the Integrations section.', 'hivepress-authentication' ),
						'type'        => 'select',
						'multiple'    => true,
						'_order'      => 20,

						'options'     => [
							'facebook' => 'Facebook',
							'google'   => 'Google',
						],
					],
				],
			],
		],
	],

	'integrations' => [
		'sections' => [
			'google'   => [
				'fields' => [
					'google_client_id' => [
						'label'      => hivepress()->translator->get_string( 'client_id' ),
						'type'       => 'text',
						'max_length' => 256,
						'_order'     => 10,
					],
				],
			],

			'facebook' => [
				'title'  => 'Facebook',
				'_order' => 100,

				'fields' => [
					'facebook_app_id' => [
						'label'      => hivepress()->translator->get_string( 'app_id' ),
						'type'       => 'text',
						'max_length' => 256,
						'_order'     => 10,
					],
				],
			],
		],
	],
];
