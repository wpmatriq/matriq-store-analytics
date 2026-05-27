<?php
/**
 * Plugin Name: Store Analytics by Matriq
 * Description: Daily revenue diagnosis for WooCommerce. Explains why store revenue changed each day in plain language, with deterministic math and no AI guessing.
 * Plugin URI: https://matriq.in/
 * Author: Matriq
 * Author URI: https://matriq.in/
 * Version: 1.0.0
 * License: GPLv2 or later
 * Requires at least: 6.7
 * Requires PHP: 7.4
 * Text Domain: matriq-store-analytics
 * Domain Path: /languages
 * Tested up to: 7.0
 * WC requires at least: 7.0
 * WC tested up to: 10.8.0
 *
 * @package Matriq\MSA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Set constants
 */
define( 'MATRIQ_MSA_VER', '1.0.0' );
define( 'MATRIQ_MSA_FILE', __FILE__ );
define( 'MATRIQ_MSA_PRO_MINIMUM_VER', '1.0.0' );

/**
 * Declare compatibility with WooCommerce's custom order tables feature, if WooCommerce is active and the feature is available.
 * This ensures that when WooCommerce enables the custom order tables feature flag, our plugin will be marked as compatible and won't be paused.
 */
add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', MATRIQ_MSA_FILE, true );
		}
	}
);

/**
 * Let's load the plugin gracefully.
 */
require_once 'loader.php';
