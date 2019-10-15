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
					'authentication_methods' => [
						'label'   => esc_html__( 'Authentication Methods', 'hivepress-authentication' ),
						'type'    => 'checkboxes',
						'order'   => 20,

						'options' => [
							'facebook' => 'Facebook',
						],
					],
				],
			],
		],
	],

	'integrations' => [
		'sections' => [
			'facebook' => [
				'title'  => 'Facebook',
				'order'  => 30,

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
