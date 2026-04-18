<?php
/**
 * Trait.
 *
 * @package EC_Sales_Pulse
 * @since x.x.x
 */

namespace EC_Sales_Pulse\Inc\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Trait Get_Instance.
 *
 * @since x.x.x
 */
trait Get_Instance {
	/**
	 * Instance object.
	 *
	 * @var object Class Instance.
	 */
	private static $instance = null;

	/**
	 * Initiator
	 *
	 * @since x.x.x
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( null === static::$instance || ! ( static::$instance instanceof static ) ) {
			static::$instance = new static();
		}
		return static::$instance;
	}
}
