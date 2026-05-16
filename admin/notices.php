<?php
/**
 * Admin notices.
 *
 * @package Matriq\MSA
 * @since 0.0.2
 */

namespace Matriq\MSA\Admin;

defined( 'ABSPATH' ) || exit;

use Matriq\MSA\Inc\Traits\Get_Instance;

/**
 * Notices
 *
 * @since 0.0.2
 */
class Notices {
	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 0.0.2
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'minimum_pro_version_requirement' ] );
	}

	/**
	 * Check if the current screen is the admin screen to display the notice.
	 *
	 * @return bool
	 */
	public function should_notice_be_visible(): bool {
		if ( ! current_user_can( 'activate_plugins' ) || ! current_user_can( 'install_plugins' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Display admin notice if premium incompatible version is activated.
	 *
	 * @since 0.0.2
	 */
	public function minimum_pro_version_requirement(): void {
		if ( ! $this->should_notice_be_visible() ) {
			return;
		}

		if ( ! defined( 'MATRIQ_MSA_PRO_VER' ) || ! defined( 'MATRIQ_MSA_PRO_PRODUCT' ) ) {
			return;
		}
	}
}
