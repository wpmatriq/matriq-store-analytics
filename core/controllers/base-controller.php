<?php
/**
 * Base REST Controller.
 *
 * Abstract base for all Matriq Store Analytics v2 REST controllers.
 * Provides permission checks, response helpers, and parameter sanitizers.
 *
 * @package Matriq\MSA\Core\Controllers
 */

namespace Matriq\MSA\Core\Controllers;

use Matriq\MSA\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

/**
 * Shared base for every Matriq Store Analytics REST controller.
 *
 * Centralises the `matriq-store-analytics/v2` namespace, the `manage_woocommerce`
 * permission check, and the success/error response envelope so subclasses
 * only need to declare a `rest_base` and register their routes.
 */
abstract class BaseController {
	use Get_Instance;

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'matriq-store-analytics/v2';

	/**
	 * Route base (override in each controller).
	 *
	 * @var string
	 */
	protected $rest_base = '';

	/**
	 * Constructor - hook into rest_api_init.
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register controller routes. Must be implemented by each controller.
	 *
	 * @return void
	 */
	abstract public function register_routes(): void;

	/**
	 * Permission check: manage_woocommerce capability.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool|\WP_Error
	 */
	public function admin_permission_check( $request ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to access this resource.', 'matriq-store-analytics' ),
				[ 'status' => 403 ]
			);
		}

		return true;
	}

	/**
	 * Success response helper.
	 *
	 * @param mixed $data    Response data.
	 * @param int   $status  HTTP status code.
	 * @return \WP_REST_Response
	 */
	protected function success( $data, int $status = 200 ): \WP_REST_Response {
		return new \WP_REST_Response(
			[
				'success' => true,
				'data'    => $data,
			],
			$status
		);
	}

	/**
	 * Error response helper.
	 *
	 * @param string $message Error message.
	 * @param int    $status  HTTP status code.
	 * @return \WP_REST_Response
	 */
	protected function error( string $message, int $status = 400 ): \WP_REST_Response {
		return new \WP_REST_Response(
			[
				'success' => false,
				'message' => $message,
			],
			$status
		);
	}

	/**
	 * Get sanitized date parameter from request.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @param string           $key     Parameter key.
	 * @param string           $default Default value.
	 * @return string Date in Y-m-d format.
	 */
	protected function get_date_param( \WP_REST_Request $request, string $key, string $default = '' ): string {
		$date = sanitize_text_field( $request->get_param( $key ) ?? $default );

		if ( $date && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return $default;
		}

		return $date;
	}

	/**
	 * Get sanitized integer parameter from request.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @param string           $key     Parameter key.
	 * @param int              $default Default value.
	 * @return int
	 */
	protected function get_int_param( \WP_REST_Request $request, string $key, int $default = 0 ): int {
		return absint( $request->get_param( $key ) ?? $default );
	}
}
