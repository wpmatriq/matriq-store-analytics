<?php
/**
 * Trait.
 *
 * @package Matriq\MSA
 * @since 0.0.2
 */

namespace Matriq\MSA\Inc\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Trait Get_Instance.
 *
 * @phpstan-consistent-constructor
 * @since 0.0.2
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
	 * @since 0.0.2
	 * @return static Initialized instance of the using class.
	 */
	public static function get_instance() {
		if ( static::$instance === null || ! ( static::$instance instanceof static ) ) {
			static::$instance = new static();
		}
		return static::$instance;
	}
}
