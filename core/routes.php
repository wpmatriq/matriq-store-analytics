<?php
/**
 * Define the REST API routes.
 *
 * @package EC_Sales_Pulse
 */

namespace EC_Sales_Pulse\Core;

use EC_Sales_Pulse\Core\Routers\Misc as MiscRoute;
use EC_Sales_Pulse\Inc\Services\Router;
use EC_Sales_Pulse\Inc\Traits\Get_Instance;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class CPTs.
 */
class Routes {
	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->initialize_actions();

		add_action(
			'rest_api_init',
			static function (): void {
				if ( method_exists( Router::get_instance(), 'registerRoutes' ) ) {
					Router::get_instance()->registerRoutes();
				}
			}
		);
	}

	/**
	 * Init Hooks.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function initialize_actions(): void {
		$this->register_rest_routes();
	}

	/**
	 * Return the rest response.
	 *
	 * @param mixed $response The response.
	 * @param int   $status The status code.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public static function rest_response( $response, $status = 200 ) {
		if ( empty( $response ) ) {
			return new \WP_Error( 'no_space_found', __( 'Oops! Something wrong here...', 'suredash' ), [ 'status' => 404 ] );
		}

		$response = rest_ensure_response( $response );

		// Only call set_status if the response is a WP_REST_Response instance.
		if ( $response instanceof \WP_REST_Response ) {
			$response->set_status( $status );
		}

		return $response;
	}

	/**
	 * Get SureDash routes.
	 *
	 * @return array<string, array<string, array<int, callable>>>
	 */
	public function get_wc_sma_routes(): array {
		return apply_filters(
			'wc_sma_rest_routes',
			[
				'/submit-topic/' => [
					'method'              => 'POST',
					'callback'            => [ MiscRoute::get_instance(), 'submit_topic' ],
					'permission_callback' => 'admin',
				],
			]
		);
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_rest_routes(): void {
		$wc_sma_routes = $this->get_wc_sma_routes();

		foreach ( $wc_sma_routes as $route => $route_data ) {
			$method              = $route_data['method'] ?? 'POST';
			$callback            = $route_data['callback'] ?? null;
			$permission_callback = $route_data['permission_callback'] ?? '';
			$this->register_route( $method, $route, $callback, $permission_callback ); // @phpstan-ignore-line
		}
	}

	/**
	 * Register route.
	 *
	 * @param string $method Method.
	 * @param string $route Route.
	 * @param array  $callback Callback.
	 * @param bool   $permission_callback Permission callback.
	 * @return void
	 * @since 0.0.2
	 * @phpstan-ignore-next-line
	 */
	public function register_route( $method, $route, $callback, $permission_callback = '' ): void {
		wc_sma_route()->addRoute(
			$method,
			$route,
			static function ( $request ) use ( $callback ) {
				// @phpstan-ignore-next-line
				return self::rest_response( call_user_func_array( $callback, [ $request ] ) );
			},
			$permission_callback // @phpstan-ignore-line
		);
	}
}
