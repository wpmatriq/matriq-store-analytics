<?php
/**
 * Maintenance.
 *
 * @package Matriq\MSA
 * @since 0.0.2
 */

namespace Matriq\MSA\Inc\Utils;

use Matriq\MSA\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

/**
 * Update Compatibility
 *
 * @package Matriq\MSA
 */
class Maintenance {
	use Get_Instance;

	/**
	 *  Constructor
	 */
	public function __construct() {
		add_action( 'matriq_msa_update_before', self::class . '::manage_backward' );

		if ( is_admin() ) {
			add_action( 'admin_init', self::class . '::init' );
		} else {
			add_action( 'init', self::class . '::init' );
		}
	}

	/**
	 * Init
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public static function init(): void {
		do_action( 'matriq_msa_update_before' );

		// Get auto saved version number.
		$saved_version = get_option( 'matriq_msa_saved_version', '' );

		// Update auto saved version number.
		if ( ! $saved_version ) {
			update_option( 'matriq_msa_saved_version', MATRIQ_MSA_VER );
		}

		// If equals then return.
		if ( version_compare( strval( $saved_version ), MATRIQ_MSA_VER, '=' ) ) {
			return;
		}

		// Update auto saved version number.
		update_option( 'matriq_msa_saved_version', MATRIQ_MSA_VER );

		do_action( 'matriq_msa_update_after' );

		// Finally flush rewrite rules.
		delete_option( 'rewrite_rules' );
	}

	/**
	 * Manage backward compatibility.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public static function manage_backward(): void {
	}
}
