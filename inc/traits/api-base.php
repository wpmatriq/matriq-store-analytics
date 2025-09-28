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
trait API_Base {
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'sales-pulse/v1';

	/**
	 * Constructor
	 *
	 * @since x.x.x
	 */
	public function __construct() {
	}

	/**
	 * Register API routes.
	 *
	 * @return string
	 */
	public function get_api_namespace() {
		return $this->namespace;
	}
}
