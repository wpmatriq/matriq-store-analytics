<?php
/**
 * Trait.
 *
 * @package WC_Smart_Analytics
 * @since x.x.x
 */

namespace WC_Smart_Analytics\Inc\Traits;

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
	protected $namespace = 'wc-smart-analytics/v1';

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
