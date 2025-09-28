<?php
/**
 * Plugin functions.
 *
 * @package EC_Sales_Pulse
 * @since x.x.x
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use EC_Sales_Pulse\Inc\Services\Query;
use EC_Sales_Pulse\Inc\Services\Router;

/**
 * Check if pro version is active.
 *
 * @return bool
 * @since x.x.x
 */
function wc_sma_is_pro_active() {
	return defined( 'EC_Sales_Pulse_PRO_VER' );
}

/**
 * Clean variables using sanitize_text_field.
 *
 * @param mixed $var Data to sanitize.
 * @return mixed
 *
 * @since x.x.x
 */
function wc_sma_clean_data( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'wc_sma_clean_data', $var );
	}
	return is_scalar( $var ) ? sanitize_text_field( (string) $var ) : $var;
}

/**
 * Get the ORM query instance.
 *
 * @return Query
 */
function wc_sma_query() {
	return Query::init(); // @phpstan-ignore-line
}

/**
 * Get the Router instance.
 *
 * @return Router
 */
function wc_sma_route() {
	return Router::get_instance(); // @phpstan-ignore-line
}
