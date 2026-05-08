<?php
/**
 * Helper.
 *
 * @package EC_Sales_Pulse
 * @since x.x.x
 */

namespace EC_Sales_Pulse\Inc\Utils;

use EC_Sales_Pulse\Core\Models\Controller;

/**
 * Initialize setup
 *
 * @since x.x.x
 * @package EC_Sales_Pulse
 */

defined( 'ABSPATH' ) || exit;

/**
 * This class setup all helper action
 *
 * @class Helper
 */
class Helper {
	/**
	 * Returns an option from the database for the admin settings.
	 *
	 * @param  string $key     The option key.
	 * @param  mixed  $default Option default value if option is not available.
	 * @return mixed   Returns the option value
	 *
	 * @since x.x.x
	 */
	public static function get_option( $key, $default = false ) {
		$portal_settings = Settings::get_wc_sma_settings();

		if ( empty( $portal_settings ) || ! is_array( $portal_settings ) || ! array_key_exists( $key, $portal_settings ) ) {
			$portal_settings[ $key ] = '';
		}

		// Get the setting option if we're in the admin panel.
		$value = $portal_settings[ $key ];

		if ( $value === '' && $default !== false ) {
			return $default;
		}

		return $value;
	}

	/**
	 * Update option from the database for the admin settings.
	 *
	 * @param  string $key      The option key.
	 * @param  mixed  $value    Option value to update.
	 * @return string           Return the option value
	 *
	 * @since x.x.x
	 */
	public static function update_option( $key, $value = true ) {
		$portal_settings = Settings::get_wc_sma_settings( false );

		if ( ! is_array( $portal_settings ) ) {
			$portal_settings = [];
		}

		// If the value is same as default then remove it from the DB.
		// This will help in the translatable strings.
		if ( Settings::get_default_option( $key ) === $value ) {
			unset( $portal_settings[ $key ] );
		} else {
			$portal_settings[ $key ] = $value;
		}

		update_option( EC_SALES_PULSE_SETTINGS, $portal_settings );

		return $value;
	}

	/**
	 * Delete option from the database for the admin settings.
	 *
	 * @param  string $key The option key.
	 * @return bool        Returns true if the option was deleted, false otherwise.
	 *
	 * @since 1.0.0
	 */
	public static function delete_option( $key ) {
		$portal_settings = get_option( EC_SALES_PULSE_SETTINGS );

		if ( empty( $portal_settings ) || ! is_array( $portal_settings ) ) {
			return false;
		}

		// If the key does not exist, return false.
		if ( ! isset( $portal_settings[ $key ] ) ) {
			return false;
		}

		if ( isset( $portal_settings[ $key ] ) ) {
			unset( $portal_settings[ $key ] );
			update_option( EC_SALES_PULSE_SETTINGS, $portal_settings );
		}

		return true;
	}
}
