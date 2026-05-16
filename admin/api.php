<?php
/**
 * Portal
 *
 * This class will holds the code related to the managing of
 * posts of portals
 *
 * @package Matriq\MSA
 *
 * @since 0.0.2
 */

namespace Matriq\MSA\Admin;

use Matriq\MSA\Inc\Traits\API_Base;
use Matriq\MSA\Inc\Traits\Get_Instance;
use Matriq\MSA\Inc\Utils\Settings;

defined( 'ABSPATH' ) || exit;
/**
 * API
 *
 * @since 0.0.2
 */
class API {
	use Get_Instance;
	use API_Base;

	/**
	 * Route base.
	 *
	 * @var string $rest_base REST base.
	 */
	protected string $rest_base = '/dataset/';

	/**
	 * Option name
	 *
	 * @access private
	 *
	 * @var string $option_name DB option name.
	 *
	 * @since 0.0.2
	 */
	private static string $option_name = 'matriq_msa_admin_settings';

	/**
	 * Constructor
	 *
	 * @since 0.0.2
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register API routes.
	 *
	 * @since 0.0.2
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			$this->rest_base,
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_admin_settings' ],
					'permission_callback' => [ $this, 'get_permissions_check' ],
					'args'                => [],
				],
			]
		);
	}

	/**
	 * Get common settings.
	 *
	 * @return array<string, mixed> $updated_option defaults + set DB option data.
	 *
	 * @since 0.0.2
	 */
	public function get_admin_settings(): array {
		return Settings::get_settings();
	}

	/**
	 * Check whether a given request has permission to read notes.
	 *
	 * @since 0.0.2
	 *
	 * @return bool|\WP_Error
	 */
	public function get_permissions_check() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Update an value of a key,
	 * from the settings database option for the admin settings page.
	 *
	 * @param string $key       The option key.
	 * @param mixed  $value     The value to update.
	 *
	 * @return void             Return the option value based on provided key
	 *
	 * @since 0.0.2
	 */
	public static function update_admin_settings_option( string $key, $value ): void {
		$updated_settings = get_option( self::$option_name, [] );

		if ( ! is_array( $updated_settings ) ) {
			$updated_settings = [];
		}

		$updated_settings[ $key ] = $value;
		update_option( self::$option_name, $updated_settings );
	}
}
