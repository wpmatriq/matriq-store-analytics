<?php
/**
 * Settings Controller.
 *
 * Minimal settings: snapshot time, email digest toggle, diagnosis sensitivity.
 * Stored in wp_options (few keys, not worth a custom table).
 *
 * @package EC_Sales_Pulse\Core\Controllers
 */

namespace EC_Sales_Pulse\Core\Controllers;

use EC_Sales_Pulse\Core\Database\SystemState;

defined( 'ABSPATH' ) || exit;

/**
 * REST controller for the Settings page. Reads/writes the `salespulse_settings`
 * option (digest schedule, recipient, sensitivity, etc.) under the
 * `sales-pulse/v2/settings` namespace.
 */
class SettingsController extends BaseController {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'settings';

	/**
	 * Option key for all plugin settings.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'salespulse_settings';

	/**
	 * Default settings.
	 *
	 * @var array<string, mixed>
	 */
	const DEFAULTS = [
		'snapshot_hour'         => 2,              // 0-23.
		'snapshot_min'          => 10,             // 0-59.
		'email_enabled'         => false,
		'email_address'         => '',             // Defaults to admin email.
		'diagnosis_sensitivity' => 'balanced',     // 'calm' | 'balanced' | 'vigilant'.
		'last_digest_error'     => null,           // string|null - last failure reason, cleared on success.
	];

	/**
	 * Allowed values for the diagnosis_sensitivity setting.
	 *
	 * @var string[]
	 */
	const SENSITIVITY_VALUES = [ 'calm', 'balanced', 'vigilant' ];

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		// Get settings.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_settings' ],
				'permission_callback' => [ $this, 'admin_permission_check' ],
			]
		);

		// Update settings.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'update_settings' ],
				'permission_callback' => [ $this, 'admin_permission_check' ],
				'args'                => [
					'snapshot_hour'         => [
						'type'              => 'integer',
						'minimum'           => 0,
						'maximum'           => 23,
						'sanitize_callback' => 'absint',
					],
					'snapshot_min'          => [
						'type'              => 'integer',
						'minimum'           => 0,
						'maximum'           => 59,
						'sanitize_callback' => 'absint',
					],
					'email_enabled'         => [
						'type'              => 'boolean',
						'sanitize_callback' => 'rest_sanitize_boolean',
					],
					'email_address'         => [
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_email',
					],
					'diagnosis_sensitivity' => [
						'type'              => 'string',
						'enum'              => self::SENSITIVITY_VALUES,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);
	}

	/**
	 * Get current settings.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_settings( \WP_REST_Request $request ): \WP_REST_Response {
		return $this->success( self::get_all() );
	}

	/**
	 * Update settings (partial update - only provided keys are changed).
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function update_settings( \WP_REST_Request $request ): \WP_REST_Response {
		$current = self::get_all();
		$params  = $request->get_json_params();
		// `last_digest_error` is internal-only, set by DigestMailer; never accept it from clients.
		$allowed = array_diff( array_keys( self::DEFAULTS ), [ 'last_digest_error' ] );
		$updated = false;

		foreach ( $allowed as $key ) {
			if ( array_key_exists( $key, $params ) ) {
				$value = $params[ $key ];

				// Validate specific fields.
				if ( $key === 'snapshot_hour' && ( $value < 0 || $value > 23 ) ) {
					continue;
				}
				if ( $key === 'snapshot_min' && ( $value < 0 || $value > 59 ) ) {
					continue;
				}
				if ( $key === 'email_address' && ! empty( $value ) && ! is_email( $value ) ) {
					continue;
				}
				if ( $key === 'diagnosis_sensitivity' && ! in_array( $value, self::SENSITIVITY_VALUES, true ) ) {
					continue;
				}

				$current[ $key ] = $value;
				$updated         = true;
			}
		}

		if ( $updated ) {
			update_option( self::OPTION_KEY, $current );
		}

		return $this->success( $current );
	}

	/**
	 * Get all settings merged with defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_all(): array {
		$saved = get_option( self::OPTION_KEY, [] );
		if ( ! is_array( $saved ) ) {
			$saved = [];
		}

		$settings = array_merge( self::DEFAULTS, $saved );

		// Default email address to admin email if empty.
		if ( empty( $settings['email_address'] ) ) {
			$settings['email_address'] = get_option( 'admin_email', '' );
		}

		// Include derived info.
		$settings['timezone']        = wp_timezone_string();
		$settings['currency']        = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'USD';
		$settings['currency_symbol'] = function_exists( 'get_woocommerce_currency_symbol' ) ? html_entity_decode( get_woocommerce_currency_symbol() ) : '$';

		// Surface digest health from SystemState so the UI can render "Last sent ..." without a second round-trip.
		$state                           = SystemState::get_instance();
		$settings['last_digest_sent_at'] = $state->get( SystemState::KEY_LAST_DIGEST_SENT_AT );

		return $settings;
	}

	/**
	 * Get a single setting value.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public static function get( string $key, $default = null ) {
		$settings = self::get_all();
		return $settings[ $key ] ?? $default;
	}
}
