<?php
/**
 * Admin notices.
 *
 * @package WC_Smart_Analytics
 * @since x.x.x
 */

namespace WC_Smart_Analytics\Admin;

use WC_Smart_Analytics\Inc\Traits\Get_Instance;

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

		if ( ! defined( 'WC_SMART_ANALYTICS_PRO_VER' ) || ! defined( 'WC_SMART_ANALYTICS_PRO_PRODUCT' ) ) {
			return;
		}
	}
}
