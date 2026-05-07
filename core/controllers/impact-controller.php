<?php
/**
 * Free Impact REST controller (Phase 6.2).
 *
 *   GET /sales-pulse/v2/impact/summary
 *
 * Returns the data-foundation stats rendered by the free Impact tab.
 * Pro replaces this surface with attribution-driven numbers; the free
 * version stays focused on "your data foundation is solid."
 *
 * @package EC_Sales_Pulse\Core\Controllers
 */

namespace EC_Sales_Pulse\Core\Controllers;

use EC_Sales_Pulse\Core\Services\ImpactSummary;

defined( 'ABSPATH' ) || exit;

class ImpactController extends BaseController {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'impact';

	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/summary',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_summary' ],
				'permission_callback' => [ $this, 'admin_permission_check' ],
			]
		);
	}

	public function get_summary(): \WP_REST_Response {
		return $this->success( ImpactSummary::get_instance()->build() );
	}
}
