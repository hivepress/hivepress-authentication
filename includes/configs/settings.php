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
						'type'        => 'checkboxes',
						'order'       => 20,

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
				'title'  => 'Google',
				'order'  => 30,

				'fields' => [
					'google_client_id' => [
						'label'      => esc_html__( 'Client ID', 'hivepress-authentication' ),
						'type'       => 'text',
						'max_length' => 256,
						'order'      => 10,
					],
				],
			],

			'facebook' => [
				'title'  => 'Facebook',
				'order'  => 40,

				'fields' => [
					'facebook_app_id' => [
						'label'      => esc_html__( 'App ID', 'hivepress-authentication' ),
						'type'       => 'text',
						'max_length' => 256,
						'order'      => 10,
					],
				],
			],
		],
	],
];
