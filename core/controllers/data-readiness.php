<?php
/**
 * Data Readiness Controller.
 *
 * Checks system prerequisites before showing the dashboard:
 * - WooCommerce active
 * - Analytics tables present
 * - Orders exist
 * - Plugin tables created
 * - Backfill status
 *
 * Also provides a manual snapshot trigger for admin use.
 *
 * @package EC_Sales_Pulse\Core\Controllers
 */

namespace EC_Sales_Pulse\Core\Controllers;

use EC_Sales_Pulse\Core\Database\DailyStats;
use EC_Sales_Pulse\Core\Database\Schema;
use EC_Sales_Pulse\Core\Database\SystemState;
use EC_Sales_Pulse\Core\Services\DataCollector;
use EC_Sales_Pulse\Core\Services\SnapshotBuilder;

defined( 'ABSPATH' ) || exit;

class DataReadiness extends BaseController {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'system';

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		// Data readiness check.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/readiness',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_readiness' ],
				'permission_callback' => [ $this, 'admin_permission_check' ],
			]
		);

		// Manual snapshot trigger.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/snapshot',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'trigger_snapshot' ],
				'permission_callback' => [ $this, 'admin_permission_check' ],
				'args'                => [
					'date' => [
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					],
					'days' => [
						'type'              => 'integer',
						'default'           => 14,
						'minimum'           => 1,
						'maximum'           => 30,
						'sanitize_callback' => 'absint',
					],
				],
			]
		);

		// Backfill status.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/backfill',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_backfill_status' ],
				'permission_callback' => [ $this, 'admin_permission_check' ],
			]
		);

		// Trigger backfill batch.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/backfill',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'trigger_backfill' ],
				'permission_callback' => [ $this, 'admin_permission_check' ],
			]
		);
	}

	/**
	 * Check data readiness — all prerequisites for dashboard to function.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_readiness( \WP_REST_Request $request ): \WP_REST_Response {
		$checks = [];

		// 1. WooCommerce active.
		$checks['woocommerce_active'] = class_exists( 'WooCommerce' );

		// 2. Analytics tables available.
		$collector                         = DataCollector::get_instance();
		$checks['analytics_tables_exist']  = $collector->are_analytics_tables_available();

		// 3. Orders exist.
		$checks['orders_exist'] = $checks['analytics_tables_exist']
			? $collector->get_total_order_count() > 0
			: false;

		// 4. Plugin tables created.
		$schema                       = Schema::get_instance();
		$checks['plugin_tables_exist'] = $schema->tables_exist();
		$checks['tables_status']       = $schema->get_tables_status();

		// 5. Snapshots available.
		$daily_stats               = DailyStats::get_instance();
		$checks['snapshot_count']  = $checks['plugin_tables_exist'] ? $daily_stats->count() : 0;
		$checks['has_data']        = $checks['snapshot_count'] > 0;

		// Dashboard needs at minimum 2 snapshots (yesterday + day-before) for daily view.
		$checks['dashboard_ready'] = $checks['snapshot_count'] >= 2;

		// 6. Backfill status.
		$state                         = SystemState::get_instance();
		$checks['backfill_complete']   = $checks['plugin_tables_exist'] && $state->is_backfill_complete();
		$checks['last_snapshot_date']  = $checks['plugin_tables_exist'] ? $state->get_last_snapshot_date() : null;

		// Overall readiness — require at least 2 snapshots so the dashboard can compare periods.
		$checks['ready'] = $checks['woocommerce_active']
			&& $checks['analytics_tables_exist']
			&& $checks['plugin_tables_exist']
			&& $checks['dashboard_ready'];

		return $this->success( $checks );
	}

	/**
	 * Manually trigger a snapshot for a specific date, or build an initial batch.
	 *
	 * - If `date` param is provided, builds that single date (existing behavior).
	 * - Otherwise, builds an initial batch of recent days for dashboard readiness.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function trigger_snapshot( \WP_REST_Request $request ): \WP_REST_Response {
		$date = $this->get_date_param( $request, 'date' );

		$builder = SnapshotBuilder::get_instance();

		// If a specific date is requested, build just that date.
		if ( $date ) {
			$result = $builder->build_snapshot( $date );
			return $this->success( [
				'date'    => $date,
				'success' => $result,
			] );
		}

		// Otherwise, build an initial batch of days.
		$days   = $this->get_int_param( $request, 'days', 14 );
		$result = $builder->build_initial_batch( $days );

		return $this->success( $result );
	}

	/**
	 * Get backfill progress.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_backfill_status( \WP_REST_Request $request ): \WP_REST_Response {
		$state = SystemState::get_instance();

		return $this->success( [
			'complete'       => $state->is_backfill_complete(),
			'cursor'         => $state->get( SystemState::KEY_BACKFILL_CURSOR ),
			'start'          => $state->get( SystemState::KEY_BACKFILL_START ),
			'snapshot_count' => DailyStats::get_instance()->count(),
		] );
	}

	/**
	 * Trigger a single backfill batch manually.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function trigger_backfill( \WP_REST_Request $request ): \WP_REST_Response {
		$builder = SnapshotBuilder::get_instance();
		$result  = $builder->run_backfill( 5 );

		return $this->success( $result );
	}
}
