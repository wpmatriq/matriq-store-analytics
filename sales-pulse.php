<?php
/**
 * Plugin Name: Sales Pulse
 * Description: Sales Pulse is a WordPress plugin that helps you to deep dive into your E-Commerce business.
 * Plugin URI: https://matriq.in/sales-pulse/
 * Author: Matriq
 * Author URI: https://matriq.in
 * Version: 0.0.1
 * License: GPLv2 or later
 * Requires at least: 6.7
 * Requires PHP: 7.4
 * Text Domain: sales-pulse
 *
 * @package EC_Sales_Pulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Set constants
 */
define( 'EC_SALES_PULSE_VER', '0.0.1' );
define( 'EC_SALES_PULSE_FILE', __FILE__ );
define( 'EC_SALES_PULSE_PRO_MINIMUM_VER', '0.0.1' );

require_once 'loader.php';
