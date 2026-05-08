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
 * @phpstan-consistent-constructor
 * @since x.x.x
 */
trait Get_Instance {
	/**
	 * Instance object.
	 *
	 * Protected (not private) so `static::$instance` late-binding works
	 * correctly with subclassing. Pro extends some of these singletons
	 * via subclass + factory swap.
	 *
	 * @var static|null
	 */
	protected static $instance = null;

	/**
	 * Initiator.
	 *
	 * @since x.x.x
	 * @return static Initialized instance of the using class.
	 */
	public static function get_instance() {
		if ( null === static::$instance || ! ( static::$instance instanceof static ) ) {
			static::$instance = new static();
		}
		return static::$instance;
	}
}
