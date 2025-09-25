<?php
/**
 * Maintenance.
 *
 * @package WC_Smart_Analytics
 * @since x.x.x
 */

namespace WC_Smart_Analytics\Inc\Utils;

use WC_Smart_Analytics\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

/**
 * Update Compatibility
 *
 * @package WC_Smart_Analytics
 */
class Maintenance {
	use Get_Instance;

	/**
	 *  Constructor
	 */
	public function __construct() {
		add_action( 'wc_smart_analytics_update_before', self::class . '::manage_backward' );

		if ( is_admin() ) {
			add_action( 'admin_init', self::class . '::init' );
		} else {
			add_action( 'init', self::class . '::init' );
		}
	}

	/**
	 * Init
	 *
	 * @since x.x.x
	 * @return void
	 */
	public static function init(): void {
		do_action( 'wc_smart_analytics_update_before' );

		// Get auto saved version number.
		$saved_version = get_option( 'wc_sma_saved_version', '' );

		// Update auto saved version number.
		if ( ! $saved_version ) {
			update_option( 'wc_sma_saved_version', WC_SMART_ANALYTICS_VER );
		}

		// If equals then return.
		if ( version_compare( strval( $saved_version ), WC_SMART_ANALYTICS_VER, '=' ) ) {
			return;
		}

		// Update auto saved version number.
		update_option( 'wc_sma_saved_version', WC_SMART_ANALYTICS_VER );

		do_action( 'wc_smart_analytics_update_after' );

		// Finally flush rewrite rules.
		delete_option( 'rewrite_rules' );
	}

	/**
	 * Manage backward compatibility.
	 *
	 * @since x.x.x
	 * @return void
	 */
	public static function manage_backward(): void {
	}
}
