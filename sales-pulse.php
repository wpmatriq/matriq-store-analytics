<?php
/**
 * Plugin Name: Sales Pulse
 * Plugin URI: matriq.in
 * Description: Sales Pulse is a WordPress plugin that helps you to deep dive into your E-Commerce business.
 * Author: Matriq
 * Author URI: matriq.in
 * Version: 0.0.1
 * License: GPL v2
 * Text Domain: sales-pulse
 * Domain Path: /languages
 *
 * @package EC_Sales_Pulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Set constants
 */
define( 'EC_Sales_Pulse_VER', '0.0.1' );
define( 'EC_Sales_Pulse_FILE', __FILE__ );
define( 'EC_Sales_Pulse_PRO_MINIMUM_VER', '0.0.1' );

require_once 'loader.php';
