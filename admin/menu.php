<?php
/**
 * Menu
 *
 * This class will holds the code related to the admin area modification
 * along with the plugin functionalities.
 *
 * @package Matriq\MSA
 *
 * @since 0.0.2
 */

namespace Matriq\MSA\Admin;

use Matriq\MSA\Core\Database\SystemState;
use Matriq\MSA\Inc\Traits\Get_Instance;
use Matriq\MSA\Inc\Utils\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Menu
 *
 * @since 0.0.2
 */
class Menu {
	use Get_Instance;

	/**
	 * Settings page ID for Plugin settings.
	 */
	public const PAGE_ID = 'matriq-store-analytics';

	/**
	 * Constructor
	 *
	 * @since 0.0.2
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
	 * @since 0.0.2
	 */
	public function initialize_hooks(): void {
		add_action( 'admin_menu', [ $this, 'register_plugin_menus' ] );
	}

	/**
	 * Check if the current page is a plugin page.
	 *
	 * @since 0.0.2
	 */
	public function is_plugin_page(): bool {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return false;
		}

		if ( ! empty( $_GET['page'] ) ) { // phpcs:ignore -- Input var okay.
			$page = sanitize_text_field( wp_unslash( $_GET['page'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( $page === self::PAGE_ID || $page === 'matriq-store-analytics-onboarding' || strpos( $page, self::PAGE_ID . '_' ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 *  Initialize Admin Setup.
	 *
	 * @since 0.0.2
	 */
	public function settings_admin_scripts(): void {
		if ( $this->is_plugin_page() ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'app_build_scripts' ] );
		}
	}

	/**
	 * Add submenu to admin menu.
	 *
	 * V2 navigation: Overview (default morning briefing), History (daily
	 * explanation list), Campaigns (start/stop active campaigns), Impact
	 * (data foundation stats), Settings (timezone, revenue basis, email
	 * digest).
	 *
	 * @since 0.0.2
	 */
	public function register_plugin_menus(): void {
		if ( ! current_user_can( MATRIQ_MSA_CAPABILITY ) ) {
			return;
		}

		global $submenu;
		$parent_slug   = self::PAGE_ID;
		$capability    = MATRIQ_MSA_CAPABILITY;
		$menu_priority = apply_filters( 'matriq_msa_menu_priority', 40 );

		add_menu_page(
			'Matriq Store Analytics',
			'Matriq Store Analytics',
			$capability,
			$parent_slug,
			[ $this, 'render_main_page' ],
			'dashicons-chart-area',
			$menu_priority
		);

		$default_submenus = [
			'impact'    => [
				'page_title' => __( 'Impact', 'matriq-store-analytics' ),
				'menu_title' => __( 'Impact', 'matriq-store-analytics' ),
				'capability' => $capability,
				'menu_slug'  => 'admin.php?page=' . self::PAGE_ID . '&tab=impact',
				'callback'   => null,
			],
			'history'   => [
				'page_title' => __( 'History', 'matriq-store-analytics' ),
				'menu_title' => __( 'History', 'matriq-store-analytics' ),
				'capability' => $capability,
				'menu_slug'  => 'admin.php?page=' . self::PAGE_ID . '&tab=history',
				'callback'   => null,
			],
			'campaigns' => [
				'page_title' => __( 'Campaigns', 'matriq-store-analytics' ),
				'menu_title' => __( 'Campaigns', 'matriq-store-analytics' ),
				'capability' => $capability,
				'menu_slug'  => 'admin.php?page=' . self::PAGE_ID . '&tab=campaigns',
				'callback'   => null,
			],
			'settings'  => [
				'page_title' => __( 'Settings', 'matriq-store-analytics' ),
				'menu_title' => __( 'Settings', 'matriq-store-analytics' ),
				'capability' => $capability,
				'menu_slug'  => 'admin.php?page=' . self::PAGE_ID . '&tab=settings',
				'callback'   => null,
			],
		];

		/**
		 * Filter the Matriq Store Analytics admin sub-menus before they are registered.
		 *
		 * Premium extensions add tabs (e.g. "Copilot") here. Each entry is keyed
		 * by a stable slug and must define page_title, menu_title, capability,
		 * menu_slug (string), and an optional callback (callable|null).
		 *
		 * @since 0.0.2
		 *
		 * @param array<string, array<string, mixed>> $submenus    Submenu definitions.
		 * @param string                              $parent_slug Parent menu slug.
		 */
		$submenus = apply_filters( 'matriq_msa_admin_submenus', $default_submenus, $parent_slug );

		foreach ( $submenus as $entry ) {
			if ( empty( $entry['menu_slug'] ) ) {
				continue;
			}
			add_submenu_page(
				$parent_slug,
				(string) ( $entry['page_title'] ?? '' ),
				(string) ( $entry['menu_title'] ?? '' ),
				(string) ( $entry['capability'] ?? $capability ),
				(string) $entry['menu_slug'],
				is_callable( $entry['callback'] ?? null ) ? $entry['callback'] : ''
			);
		}

		add_submenu_page(
			'',
			'WC Matriq Store Analytics ' . __( 'Onboarding', 'matriq-store-analytics' ),
			'',
			$capability,
			'matriq-store-analytics-onboarding',
			[ $this, 'render_main_page' ]
		);

		$submenu[ $parent_slug ][0][0] = esc_html__( 'Overview', 'matriq-store-analytics' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Required to rename the home menu.
	}

	/**
	 * Renders the WC SMA screen canvas.
	 *
	 * @since 0.0.2
	 */
	public function render_main_page(): void {
		echo "<div id='matriq-msa-main-page--wrapper'></div>";
	}

	/**
	 * Enqueue the Admin's build files for plugin to work.
	 *
	 * @since 0.0.2
	 */
	public function app_build_scripts(): void {
		if ( is_customize_preview() ) {
			return;
		}

		// Check weather the current page is application or it's child pages.
		$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( is_admin() && ( $current_page === self::PAGE_ID || $current_page === 'matriq-store-analytics-onboarding' ) ) {
			wp_enqueue_media();

			$last_snapshot_at = null;
			if ( class_exists( SystemState::class ) ) {
				$last_snapshot_at = SystemState::get_instance()->get_last_snapshot_timestamp();
				if ( $last_snapshot_at ) {
					// MySQL datetime (local) → ISO8601 so JS can parse it unambiguously.
					$last_snapshot_at = mysql2date( 'c', $last_snapshot_at, false );
				}
			}

			$localized_data = apply_filters(
				'matriq_msa_localized_admin_data',
				[
					'dashboard_url'     => admin_url( 'admin.php?page=' . self::PAGE_ID ),
					'ajax_url'          => admin_url( 'admin-ajax.php' ),
					'rest_url'          => esc_url_raw( rest_url( 'matriq-store-analytics/v2/' ) ),
					'rest_nonce'        => wp_create_nonce( 'wp_rest' ),
					'version'           => MATRIQ_MSA_VER,
					'update_nonce'      => wp_create_nonce( 'matriq_msa_update_admin_setting' ),
					'home_slug'         => self::PAGE_ID,
					'settings'          => Settings::get_settings(),
					'last_snapshot_at'  => $last_snapshot_at,
					'currency'          => function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'USD',
					'pro_available'     => matriq_msa_is_pro_active(),
					'pro_version'       => matriq_msa_is_pro_active() ? MATRIQ_MSA_PRO_VER : 0,
					'upgrade_link'      => MATRIQ_MSA_UPGRADE_LINK,
					'is_user_onboarded' => get_option( 'suredash_onboarding_completed', false ) === 'yes' || get_option( 'matriq_msa_onboarding_skipped' ) === 'yes' ? true : false,
				]
			);

			$handle            = 'matriq_msa_admin_scripts';
			$build_path        = MATRIQ_MSA_URL . 'assets/build/';
			$script_asset_path = MATRIQ_MSA_DIR . 'assets/build/matriq-msa-app.asset.php';

			$script_info = file_exists( $script_asset_path )
			? include $script_asset_path
			: [
				'dependencies' => [],
				'version'      => MATRIQ_MSA_VER,
			];

			$script_dep = $script_info['dependencies'];

			wp_enqueue_script(
				$handle,
				$build_path . 'matriq-msa-app.js',
				$script_dep,
				MATRIQ_MSA_VER,
				true
			);

			wp_localize_script( $handle, 'matriqMSAData', $localized_data );

			wp_set_script_translations( $handle, 'matriq-store-analytics', MATRIQ_MSA_DIR . 'languages' );

			wp_enqueue_style(
				'matriq-msa-font',
				esc_url( MATRIQ_MSA_CSS_ASSETS_FOLDER . ( is_rtl() ? 'font-rtl' : 'font' ) . MATRIQ_MSA_CSS_SUFFIX ),
				[],
				$script_info['version']
			);

			wp_enqueue_style( $handle, esc_url( is_rtl() ? $build_path . 'matriq-msa-app-rtl.css' : $build_path . 'matriq-msa-app.css' ), [ 'matriq-msa-font' ], MATRIQ_MSA_VER );
		}
	}

	/**
	 * Get plugin status
	 *
	 * @since 0.0.2
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
