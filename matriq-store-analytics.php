<?php
/**
 * Plugin Name: Matriq Store Analytics
 * Description: Daily revenue diagnosis for WooCommerce. Explains why store revenue changed each day in plain language, with deterministic math and no AI guessing.
 * Plugin URI: https://matriq.in/matriq-store-analytics/
 * Author: Matriq
 * Author URI: https://matriq.in
 * Version: 0.0.2
 * License: GPLv2 or later
 * Requires at least: 6.7
 * Requires PHP: 7.4
 * Text Domain: matriq-store-analytics
 * Domain Path: /languages
 *
 * @package Matriq\MSA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Set constants
 */
define( 'MATRIQ_MSA_VER', '0.0.2' );
define( 'MATRIQ_MSA_FILE', __FILE__ );
define( 'MATRIQ_MSA_PRO_MINIMUM_VER', '0.0.1' );

require_once 'loader.php';
