<?php
/**
 * Free Impact REST controller (Phase 6.2).
 *
 *   GET /matriq-store-analytics/v2/impact/summary
 *
 * Returns the data-foundation stats rendered by the free Impact tab.
 * Pro replaces this surface with attribution-driven numbers; the free
 * version stays focused on "your data foundation is solid."
 *
 * @package Matriq\MSA\Core\Controllers
 */

namespace Matriq\MSA\Core\Controllers;

use Matriq\MSA\Core\Services\ImpactSummary;

defined( 'ABSPATH' ) || exit;

/**
 * REST controller for the free Impact tab. Exposes a single read-only
 * `summary` endpoint backed by ImpactSummary; Pro overrides nothing here
 * and instead mounts its richer endpoints under `copilot/impact/*`.
 */
class ImpactController extends BaseController {
	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'impact';

	/**
	 * Register the free Impact REST routes.
	 *
	 * @return void
	 */
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

	/**
	 * GET /matriq-store-analytics/v2/impact/summary - returns the data-foundation stats
	 * payload assembled by ImpactSummary.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_summary(): \WP_REST_Response {
		return $this->success( ImpactSummary::get_instance()->build() );
	}
}
