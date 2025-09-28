<?php
/**
 * Portal
 *
 * This class will holds the code related to the managing of
 * posts of portals
 *
 * @package EC_Sales_Pulse
 *
 * @since x.x.x
 */

namespace EC_Sales_Pulse\Admin;

use EC_Sales_Pulse\Inc\Traits\API_Base;
use EC_Sales_Pulse\Inc\Traits\Get_Instance;
use EC_Sales_Pulse\Inc\Utils\Settings;

defined( 'ABSPATH' ) || exit;
/**
 * API
 *
 * @since x.x.x
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
	 * @since x.x.x
	 */
	private static string $option_name = 'wc_sma_admin_settings';

	/**
	 * Constructor
	 *
	 * @since x.x.x
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register API routes.
	 *
	 * @since x.x.x
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
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * Get common settings.
	 *
	 * @return array<string, mixed> $updated_option defaults + set DB option data.
	 *
	 * @since x.x.x
	 */
	public function get_admin_settings(): array {
		return Settings::get_wc_sma_settings();
	}

	/**
	 * Check whether a given request has permission to read notes.
	 *
	 * @since x.x.x
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
	 * @since x.x.x
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
