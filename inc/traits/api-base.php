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
 * @since 0.0.2
 */
trait API_Base {
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'matriq-store-analytics/v1';

	/**
	 * Constructor
	 *
	 * @since 0.0.2
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
