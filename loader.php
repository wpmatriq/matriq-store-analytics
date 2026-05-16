<?php
/**
 * Plugin Loader.
 *
 * @package Matriq\MSA
 * @since 0.0.2
 */

namespace Matriq\MSA;

defined( 'ABSPATH' ) || exit;

use Matriq\MSA\Admin\API;
use Matriq\MSA\Admin\Menu;
use Matriq\MSA\Admin\Notices;
use Matriq\MSA\Core\Controllers\CampaignsController;
use Matriq\MSA\Core\Controllers\DataReadiness;
use Matriq\MSA\Core\Controllers\DigestController;
use Matriq\MSA\Core\Controllers\History;
use Matriq\MSA\Core\Controllers\ImpactController;
use Matriq\MSA\Core\Controllers\Overview;
use Matriq\MSA\Core\Controllers\SettingsController;
use Matriq\MSA\Core\Cron\CronManager;
use Matriq\MSA\Core\Database\Schema;
use Matriq\MSA\Core\Hooks\OrderHooks;
use Matriq\MSA\Core\Services\DigestEmail;
use Matriq\MSA\Core\Services\DigestMailer;
use Matriq\MSA\Inc\Utils\Maintenance;

/**
 * Loader
 *
 * @since 0.0.2
 */
class Loader {
	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class Instance.
	 * @since 0.0.2
	 */
	private static $instance;

	/**
	 * Constructor
	 *
	 * @since 0.0.2
	 */
	public function __construct() {
		// Define the constants.
		$this->define_constants();

		spl_autoload_register( [ $this, 'autoload' ] );

		// Load helper functions after constants are defined.

		add_action( 'admin_init', [ $this, 'activation_redirect' ] );

		// Activation hook.
		register_activation_hook( MATRIQ_MSA_FILE, [ $this, 'activation_actions' ] );

		// Deactivation hook.
		register_deactivation_hook( MATRIQ_MSA_FILE, [ $this, 'deactivation_actions' ] );

		add_action( 'plugins_loaded', [ $this, 'load_plugin' ], 99 );

		// Remove this after the translation error is fixed.
		add_filter( 'doing_it_wrong_trigger_error', [ $this, 'suppress_translation_error' ], 10, 4 );

		// Prevent Query Monitor from collecting the error.
		add_action( 'doing_it_wrong_run', [ $this, 'prevent_qm_collection' ], 5, 3 );

		add_filter( 'plugin_row_meta', [ $this, 'add_meta_links' ], 10, 2 );
	}

	/**
	 * Suppress translation error.
	 *
	 * @param bool   $status       Status.
	 * @param string $function_name Function name.
	 * @param string $message      Message.
	 * @param string $version      Version.
	 *
	 * @return bool
	 * @since 0.0.2
	 */
	public function suppress_translation_error( $status, $function_name, $message, $version ) {
		if ( $function_name === '_load_textdomain_just_in_time' && strpos( $message, 'matriq-store-analytics' ) !== false ) {
			return false;
		}
		return $status;
	}

	/**
	 * Prevent Query Monitor from collecting textdomain errors.
	 *
	 * @param string $function_name The function that was called.
	 * @param string $message The error message.
	 * @param string $version The version.
	 * @return void
	 * @since 0.0.2
	 */
	public function prevent_qm_collection( $function_name, $message, $version ): void {
		if ( $function_name === '_load_textdomain_just_in_time' && strpos( $message, 'matriq-store-analytics' ) !== false ) {
			// Remove Query Monitor's action temporarily.
			if ( class_exists( '\QM_Collectors' ) ) {
				$collector = \QM_Collectors::get( 'doing_it_wrong' );
				if ( $collector ) {
					remove_action( 'doing_it_wrong_run', [ $collector, 'action_doing_it_wrong_run' ], 10 );

					// Re-add it after this specific error.
					add_action(
						'shutdown',
						static function() use ( $collector ): void {
							if ( ! has_action( 'doing_it_wrong_run', [ $collector, 'action_doing_it_wrong_run' ] ) ) {
								add_action( 'doing_it_wrong_run', [ $collector, 'action_doing_it_wrong_run' ], 10, 3 );
							}
						},
						-1
					);
				}
			}
		}
	}

	/**
	 * Initiator
	 *
	 * @since 0.0.2
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Autoload classes.
	 *
	 * @param string $class class name.
	 */
	public function autoload( $class ): void {
		if ( strpos( $class, __NAMESPACE__ ) !== 0 ) {
			return;
		}

		$class_to_load = $class;

		$namespace_pattern = preg_quote( __NAMESPACE__, '/' );

		$filename = strtolower(
			preg_replace( // phpcs:ignore Generic.PHP.ForbiddenFunctions.FoundWithAlternative
				[ '/^' . $namespace_pattern . '\\\/', '/([a-z])([A-Z])/', '/_/', '/\\\/' ],
				[ '', '$1-$2', '-', DIRECTORY_SEPARATOR ],
				$class_to_load
			)
		);

		$file = MATRIQ_MSA_DIR . $filename . '.php';

		// if the file readable, include it.
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}

	/**
	 * Activation Reset
	 *
	 * @return void
	 * @since 0.0.2
	 */
	public function activation_redirect(): void {
		// Avoid redirection in case of WP_CLI calls.
		if ( defined( 'WP_CLI' ) && \WP_CLI ) {
			return;
		}

		// Avoid redirection in case of ajax calls.
		if ( wp_doing_ajax() ) {
			return;
		}

		$do_redirect = apply_filters( 'matriq_msa_activation_redirection', get_option( 'matriq_msa_do_redirect' ) );

		if ( $do_redirect ) {

			update_option( 'matriq_msa_do_redirect', false );

			if ( ! is_multisite() ) {
				$is_onboarding_completed = get_option( 'matriq_msa_onboarding_completed' ) === 'yes' || get_option( 'matriq_msa_onboarding_skipped' ) === 'yes';

				if ( $is_onboarding_completed ) {
					// Onboarding is completed, no need to redirect.
					return;
				}

				// Redirect to onboarding page.
				wp_safe_redirect(
					add_query_arg(
						[
							'page' => 'matriq-store-analytics-onboarding',
							'matriq-msa-activation-redirect' => true,
						],
						admin_url( 'admin.php' )
					)
				);

				exit;
			}
		}
	}

	/**
	 * Define the constants which will be used throughout the plugin.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public function define_constants(): void {
		define( 'MATRIQ_MSA_UPGRADE_LINK', '#' );
		define( 'MATRIQ_MSA_TABLET_BREAKPOINT', '1024' );
		define( 'MATRIQ_MSA_MOBILE_BREAKPOINT', '768' );

		define( 'MATRIQ_MSA_BASE', plugin_basename( MATRIQ_MSA_FILE ) );
		define( 'MATRIQ_MSA_DIR', plugin_dir_path( MATRIQ_MSA_FILE ) );
		define( 'MATRIQ_MSA_URL', plugins_url( '/', MATRIQ_MSA_FILE ) );

		define( 'MATRIQ_MSA_SETTINGS', 'matriq_msa_settings' );
		define( 'MATRIQ_MSA_CAPABILITY', 'manage_options' );

		! defined( 'MATRIQ_MSA_DEVELOPMENT_MODE' ) && define( 'MATRIQ_MSA_DEVELOPMENT_MODE', false );

		$css_suffix = MATRIQ_MSA_DEVELOPMENT_MODE ? '.css' : '.min.css';
		$js_suffix  = MATRIQ_MSA_DEVELOPMENT_MODE ? '.js' : '.min.js';

		define( 'MATRIQ_MSA_CSS_SUFFIX', $css_suffix );
		define( 'MATRIQ_MSA_JS_SUFFIX', $js_suffix );

		define( 'MATRIQ_MSA_CSS_ASSETS_FOLDER', MATRIQ_MSA_DEVELOPMENT_MODE ? MATRIQ_MSA_URL . 'assets/css/unminified/' : MATRIQ_MSA_URL . 'assets/css/minified/' );
		define( 'MATRIQ_MSA_JS_ASSETS_FOLDER', MATRIQ_MSA_DEVELOPMENT_MODE ? MATRIQ_MSA_URL . 'assets/js/unminified/' : MATRIQ_MSA_URL . 'assets/js/minified/' );

		// Include required functions.
		require_once 'inc/functions/functions.php';
		require_once 'inc/functions/operations.php';
	}

	/**
	 * Plugin Activation actions.
	 *
	 * @since 0.0.2
	 */
	public function activation_actions(): void {
		/**
		 * Reset rewrite rules to avoid go to permalinks page
		 * through deleting the database options to force WP to do it
		 * because of on activation not work well flush_rewrite_rules()
		 */
		delete_option( 'rewrite_rules' );

		// Orphan options from the predecessor `wc_sma_*` prefix. Pre-release rename: no data preservation needed.
		delete_option( 'wc_sma_settings' );
		delete_option( '__wc_sma_do_redirect' );
		delete_option( '__wc_sma_onboarding_completed' );
		delete_option( '__wc_sma_onboarding_skipped' );
		delete_option( 'wc_sma_saved_version' );
		delete_option( 'wc_sma_admin_settings' );

		update_option( 'matriq_msa_do_redirect', true );

		flush_rewrite_rules();

		// Create plugin database tables.
		Schema::get_instance()->install();
	}

	/**
	 * Plugin Deactivation actions.
	 *
	 * @since 0.0.2
	 */
	public function deactivation_actions(): void {
		// Unschedule all plugin cron jobs (preserve tables on deactivation).
		CronManager::unschedule_all();
	}

	/**
	 * Enqueue required classes after plugins loaded.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public function load_plugin(): void {
		/* Maintenance init */
		Maintenance::get_instance();

		/* API init */
		API::get_instance();

		/* --- Matriq Store Analytics v2: Revenue Diagnosis Engine --- */

		// Database schema check (auto-upgrade on version mismatch).
		Schema::get_instance()->maybe_upgrade();

		// WooCommerce order hooks (dirty date tracking).
		OrderHooks::get_instance();

		// Cron job manager (nightly snapshot + backfill).
		CronManager::get_instance();

		// Email digest mailer (listens for the post-snapshot hook).
		DigestMailer::get_instance();

		// Register the WC_Email subclass so the digest appears in WC -> Settings -> Emails.
		if ( class_exists( '\\WC_Email' ) ) {
			add_filter(
				'woocommerce_email_classes',
				static function ( $classes ) {
					if ( ! isset( $classes['DigestEmail'] ) ) {
						$classes['DigestEmail'] = new DigestEmail();
					}
					return $classes;
				}
			);
		}

		// REST API v2 controllers.
		Overview::get_instance();
		History::get_instance();
		CampaignsController::get_instance();
		SettingsController::get_instance();
		DataReadiness::get_instance();
		DigestController::get_instance();
		ImpactController::get_instance();

		if ( is_admin() ) {
			/* Admin Notices init */
			Notices::get_instance();

			/* Admin menu */
			Menu::get_instance();
		}

		/**
		 * WC Smart Analytics Init.
		 *
		 * Fires when WC Smart Analytics is instantiated.
		 *
		 * @since 0.0.2
		 */
		do_action( 'matriq_msa_init' );
	}

	/**
	 * Add meta link for the SureDash under the plugin description row.
	 *
	 * @param array<int,string> $links Array of plugin meta links.
	 * @param string            $file Plugin file path.
	 * @return array<int,string> Modified plugin meta links.
	 * @since 0.0.2
	 */
	public function add_meta_links( $links, $file ) {
		if ( $file === MATRIQ_MSA_BASE ) {
			$stars = '';
			for ( $indx = 0; $indx < 5; $indx++ ) {
				$stars .= '<span class="dashicons dashicons-star-filled" style="color: #ffb900; font-size: 16px; width: 16px; height: 16px; line-height: 1.2;" aria-hidden="true"></span>';
			}
			$links[] = sprintf(
				'<a href="%s" target="_blank" rel="noopener noreferrer" aria-label="%s" role="button">%s</a>',
				esc_url( 'https://wordpress.org/support/plugin/matriq-store-analytics/reviews/#new-post' ),
				esc_attr__( 'Rate our plugin', 'matriq-store-analytics' ),
				$stars
			);
		}

		return $links;
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Loader::get_instance();
