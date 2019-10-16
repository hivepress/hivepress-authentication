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
			'authentication' => [
				'fields' => [
					'user_authentication_methods' => [
						'label'   => esc_html__( 'Authentication Methods', 'hivepress-authentication' ),
						'type'    => 'checkboxes',
						'order'   => 20,

						'options' => [
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
					'google_client_id'     => [
						'label'      => esc_html__( 'Client ID', 'hivepress-authentication' ),
						'type'       => 'text',
						'max_length' => 256,
						'order'      => 10,
					],

					'google_client_secret' => [
						'label'      => esc_html__( 'Client Secret', 'hivepress-authentication' ),
						'type'       => 'text',
						'max_length' => 256,
						'order'      => 20,
					],
				],
			],

			'facebook' => [
				'title'  => 'Facebook',
				'order'  => 40,

				'fields' => [
					'facebook_app_id'     => [
						'label'      => esc_html__( 'App ID', 'hivepress-authentication' ),
						'type'       => 'text',
						'max_length' => 256,
						'order'      => 10,
					],

					'facebook_app_secret' => [
						'label'      => esc_html__( 'App Secret', 'hivepress-authentication' ),
						'type'       => 'text',
						'max_length' => 256,
						'order'      => 20,
					],
				],
			],
		],
	],
];
