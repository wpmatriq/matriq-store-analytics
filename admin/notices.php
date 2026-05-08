<?php
/**
 * Admin notices.
 *
 * @package EC_Sales_Pulse
 * @since x.x.x
 */

namespace EC_Sales_Pulse\Admin;

use EC_Sales_Pulse\Inc\Traits\Get_Instance;

/**
 * Notices
 *
 * @since x.x.x
 */
class Notices {
	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since x.x.x
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
	 * @since x.x.x
	 */
	public function minimum_pro_version_requirement(): void {
		if ( ! $this->should_notice_be_visible() ) {
			return;
		}

		if ( ! defined( 'EC_SALES_PULSE_PRO_VER' ) || ! defined( 'EC_SALES_PULSE_PRO_PRODUCT' ) ) {
			return;
		}
	}
}
