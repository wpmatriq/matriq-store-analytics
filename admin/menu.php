<?php
/**
 * Menu
 *
 * This class will holds the code related to the admin area modification
 * along with the plugin functionalities.
 *
 * @package WC_Smart_Analytics
 *
 * @since x.x.x
 */

namespace WC_Smart_Analytics\Admin;

use WC_Smart_Analytics\Core\Models\Controller;
use WC_Smart_Analytics\Inc\Traits\Get_Instance;
use WC_Smart_Analytics\Inc\Utils\Helper;
use WC_Smart_Analytics\Inc\Utils\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Menu
 *
 * @since x.x.x
 */
class Menu {
	use Get_Instance;

	/**
	 * Settings page ID for Plugin settings.
	 */
	public const PAGE_ID = 'wc-smart-analytics';

	/**
	 * Constructor
	 *
	 * @since x.x.x
	 *
	 * @return void
	 */
	public function __construct() {
		// The admin area actions.
		$this->initialize_hooks();

		add_action( 'admin_init', [ $this, 'settings_admin_scripts' ] );
	}

	/**
	 * Function to load the admin area actions.
	 *
	 * @since x.x.x
	 */
	public function initialize_hooks(): void {
		// Load the Plugin's main menus.
		add_action( 'admin_menu', [ $this, 'register_plugin_menus' ] );

		// Load the Plugin's main menu CSS for some custom design.
		add_action( 'admin_head', [ $this, 'admin_menu_css' ] );
	}

	/**
	 *  Initialize Admin Setup.
	 *
	 * @since x.x.x
	 */
	public function settings_admin_scripts(): void {
		if ( ! empty( $_GET['page'] ) ) { // phpcs:ignore -- Input var okay.
			$page = sanitize_text_field( wp_unslash( $_GET['page'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( $page === self::PAGE_ID || $page === 'wc-sma-onboarding' || strpos( $page, self::PAGE_ID . '_' ) !== false ) {
				add_action( 'admin_enqueue_scripts', [ $this, 'app_build_scripts' ] );
			}
		}
	}

	/**
	 * Add submenu to admin menu.
	 *
	 * @since x.x.x
	 */
	public function register_plugin_menus(): void {
		if ( current_user_can( WC_SMART_ANALYTICS_CAPABILITY ) ) {
			global $submenu;
			$parent_slug   = self::PAGE_ID;
			$capability    = WC_SMART_ANALYTICS_CAPABILITY;
			$menu_priority = apply_filters( self::PAGE_ID . '_menu_priority', 40 );

			add_menu_page(
				'Smart Analytics',
				'Smart Analytics',
				$capability,
				$parent_slug,
				[ $this, 'render_main_page' ],
				'dashicons-analytics',
				$menu_priority
			);

			add_submenu_page(
				$parent_slug,
				__( 'Settings', 'wc-smart-analytics' ),
				__( 'Settings', 'wc-smart-analytics' ),
				$capability,
				'admin.php?page=' . self::PAGE_ID . '&tab=settings'
			);

			add_submenu_page(
				'',
				'WC Smart Analytics ' . __( 'Onboarding', 'wc-smart-analytics' ),
				'',
				$capability,
				'wc-sma-onboarding',
				[ $this, 'render_main_page' ]
			);

			$submenu[ $parent_slug ][0][0] = esc_html__( 'Dashboard', 'wc-smart-analytics' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Required to rename the home menu.
		}
	}

	/**
	 * Add the CSS to design the main side-bar menu of the plugin.
	 *
	 * @since x.x.x
	 */
	public function admin_menu_css(): void {
		echo '<style>
			#toplevel_page_portal li {
				clear: both;
			}
			#toplevel_page_portal li:not(:last-child) a[href^="admin.php?page=portal"]:after {
				border-bottom: 1px solid hsla(0,0%,100%,.2);
				display: block;
				float: left;
				margin: 13px -15px 8px;
				content: "";
				width: calc(100% + 26px);
			}
			#toplevel_page_portal li:not(:last-child) a[href^="admin.php?page=portal&tab=spaces"]:after,
			#toplevel_page_portal li:not(:last-child) a[href^="admin.php?page=portal&tab=posts"]:after,
			#toplevel_page_portal li:not(:last-child) a[href^="admin.php?page=portal&tab=settings"]:after {
				content: none;
			}
		</style>';
	}

	/**
	 * Renders the WC SMA screen canvas.
	 *
	 * @since x.x.x
	 */
	public function render_main_page(): void {
		echo "<div id='wc-sma-main-page--wrapper'></div>";
	}

	/**
	 * Enqueue the Admin's build files for plugin to work.
	 *
	 * @since x.x.x
	 */
	public function app_build_scripts(): void {
		if ( is_customize_preview() ) {
			return;
		}

		// Check weather the current page is application or it's child pages.
		$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( is_admin() && ( $current_page === self::PAGE_ID || $current_page === 'wc-sma-onboarding' ) ) {
			wp_enqueue_media();

			$localized_data = apply_filters(
				'portal_localized_admin_data',
				[
					'dashboard_url'     => admin_url( 'admin.php?page=' . self::PAGE_ID ),
					'ajax_url'          => admin_url( 'admin-ajax.php' ),
					'version'           => WC_SMART_ANALYTICS_VER,
					'update_nonce'      => wp_create_nonce( 'wc_sma_update_admin_setting' ),
					'home_slug'         => self::PAGE_ID,
					'settings'          => Settings::get_wc_sma_settings(),
					'pro_available'     => wc_sma_is_pro_active(),
					'pro_version'       => wc_sma_is_pro_active() ? WC_SMART_ANALYTICS_PRO_VER : 0,
					'upgrade_link'      => WC_SMART_ANALYTICS_UPGRADE_LINK,
					'is_user_onboarded' => get_option( 'suredash_onboarding_completed', false ) === 'yes' || get_option( '__wc_sma_onboarding_skipped' ) === 'yes' ? true : false,
				]
			);

			$handle            = 'wc_sma_admin_scripts';
			$build_path        = WC_SMART_ANALYTICS_URL . 'assets/build/';
			$script_asset_path = WC_SMART_ANALYTICS_DIR . 'assets/build/wc-sma-app.asset.php';

			$script_info = file_exists( $script_asset_path )
			? include $script_asset_path
			: [
				'dependencies' => [],
				'version'      => WC_SMART_ANALYTICS_VER,
			];

			$script_dep = array_merge( $script_info['dependencies'], [ 'wp-plugins', 'wp-edit-site', 'wp-data', 'updates' ] );

			wp_enqueue_script(
				$handle,
				$build_path . 'wc-sma-app.js',
				$script_dep,
				WC_SMART_ANALYTICS_VER,
				true
			);

			wp_localize_script( $handle, 'wc_sma_admin_data', $localized_data );

			wp_set_script_translations( $handle, 'wc-smart-analytics', WC_SMART_ANALYTICS_DIR . 'languages' );

			wp_enqueue_style(
				'wc-sma-font',
				esc_url( WC_SMART_ANALYTICS_CSS_ASSETS_FOLDER . ( is_rtl() ? 'font-rtl' : 'font' ) . WC_SMART_ANALYTICS_CSS_SUFFIX ),
				[],
				$script_info['version']
			);

			wp_enqueue_style( $handle, esc_url( is_rtl() ? $build_path . 'wc-sma-app-rtl.css' : $build_path . 'wc-sma-app.css' ), [ 'wc-sma-font' ], WC_SMART_ANALYTICS_VER );
		}
	}

	/**
	 * Get plugin status
	 *
	 * @since x.x.x
	 *
	 * @param  string $plugin_init_file plugin init file.
	 * @return string
	 */
	public function get_plugin_status( $plugin_init_file ) {

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$installed_plugins = get_plugins();

		if ( ! isset( $installed_plugins[ $plugin_init_file ] ) ) {
			return 'not-installed';
		}
		if ( is_plugin_active( $plugin_init_file ) ) {
			return 'active';
		}

		return 'inactive';
	}
}
