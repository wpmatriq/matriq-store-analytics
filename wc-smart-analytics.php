<?php
/**
 * Plugin Name: WP Smart Analytics
 * Plugin URI: #
 * Description: WP Smart Analytics is a WordPress plugin that helps you understand your site traffic and visitors.
 * Author: SalesPulse
 * Author URI: #
 * Version: 0.0.1
 * License: GPL v2
 * Text Domain: wc-smart-analytics
 * Domain Path: /languages
 *
 * @package WC_Smart_Analytics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Set constants
 */
define( 'WC_SMART_ANALYTICS_VER', '0.0.1' );
define( 'WC_SMART_ANALYTICS_FILE', __FILE__ );
define( 'WC_SMART_ANALYTICS_PRO_MINIMUM_VER', '0.0.1' );

require_once 'loader.php';
