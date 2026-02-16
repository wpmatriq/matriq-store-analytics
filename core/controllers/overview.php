<?php
/**
 * Overview Controller.
 *
 * Returns the "Morning Briefing" data:
 * - Revenue diagnosis (headline, primary cause, confidence)
 * - Metric cards (revenue, orders, AOV, items/order)
 * - Impact breakdown
 * - Action recommendation
 * - Mini trend data (7-day sparkline)
 *
 * Supports daily (yesterday vs day-before) and weekly (7d vs previous 7d) views.
 *
 * @package EC_Sales_Pulse\Core\Controllers
 */

namespace EC_Sales_Pulse\Core\Controllers;

use EC_Sales_Pulse\Core\Database\Campaigns;
use EC_Sales_Pulse\Core\Database\DailyStats;
use EC_Sales_Pulse\Core\Services\ActionEngine;
use EC_Sales_Pulse\Core\Services\DiagnosisEngine;

defined( 'ABSPATH' ) || exit;

class Overview extends BaseController {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'overview';

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_overview' ],
				'permission_callback' => [ $this, 'admin_permission_check' ],
				'args'                => [
					'period' => [
						'type'              => 'string',
						'default'           => 'daily',
						'enum'              => [ 'daily', 'weekly' ],
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/trend',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_trend' ],
				'permission_callback' => [ $this, 'admin_permission_check' ],
				'args'                => [
					'days' => [
						'type'              => 'integer',
						'default'           => 7,
						'minimum'           => 3,
						'maximum'           => 30,
						'sanitize_callback' => 'absint',
					],
				],
			]
		);
	}

	/**
	 * Get overview / morning briefing data.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_overview( \WP_REST_Request $request ): \WP_REST_Response {
		$period     = $request->get_param( 'period' ) ?? 'daily';
		$daily_stats = DailyStats::get_instance();

		if ( $period === 'weekly' ) {
			$current  = $this->get_weekly_metrics( $daily_stats, 0 );
			$previous = $this->get_weekly_metrics( $daily_stats, 1 );
		} else {
			$current  = $this->get_daily_metrics( $daily_stats, 0 );
			$previous = $this->get_daily_metrics( $daily_stats, 1 );
		}

		// Run diagnosis.
		$diagnosis_engine = DiagnosisEngine::get_instance();
		$diagnosis        = $diagnosis_engine->diagnose( $current, $previous );

		// Get active campaign for context.
		$campaigns = Campaigns::get_instance();
		$timezone  = wp_timezone();
		$today     = ( new \DateTime( 'now', $timezone ) )->format( 'Y-m-d' );
		$campaign  = $campaigns->get_active_for_date( $today );

		// Get action recommendation.
		$action_engine  = ActionEngine::get_instance();
		$recommendation = $action_engine->recommend( $diagnosis, $campaign );

		// Build metric cards.
		$metric_cards = $this->build_metric_cards( $current, $previous );

		return $this->success( [
			'period'         => $period,
			'diagnosis'      => $diagnosis,
			'recommendation' => $recommendation,
			'metric_cards'   => $metric_cards,
			'current'        => $current,
			'previous'       => $previous,
			'campaign'       => $campaign ? [
				'id'   => $campaign->id,
				'name' => $campaign->name,
				'goal' => $campaign->goal,
			] : null,
		] );
	}

	/**
	 * Get trend data for sparkline chart.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_trend( \WP_REST_Request $request ): \WP_REST_Response {
		$days     = $this->get_int_param( $request, 'days', 7 );
		$timezone = wp_timezone();
		$end      = ( new \DateTime( 'yesterday', $timezone ) )->format( 'Y-m-d' );
		$start    = ( new \DateTime( "-{$days} days", $timezone ) )->format( 'Y-m-d' );

		$daily_stats = DailyStats::get_instance();
		$rows        = $daily_stats->get_range( $start, $end );

		$trend = array_map(
			static function ( $row ) {
				return [
					'date'    => $row->stat_date,
					'revenue' => (float) $row->revenue,
					'orders'  => (int) $row->orders,
				];
			},
			$rows
		);

		return $this->success( [
			'days'  => $days,
			'start' => $start,
			'end'   => $end,
			'trend' => $trend,
		] );
	}

	/**
	 * Get daily metrics (single day offset from yesterday).
	 *
	 * @param DailyStats $daily_stats DailyStats instance.
	 * @param int        $offset      0 = yesterday, 1 = day before yesterday, etc.
	 * @return array<string, float>|null
	 */
	private function get_daily_metrics( DailyStats $daily_stats, int $offset ) {
		$timezone = wp_timezone();
		$days_ago = $offset + 1; // offset 0 = yesterday (1 day ago).
		$date     = ( new \DateTime( "-{$days_ago} days", $timezone ) )->format( 'Y-m-d' );
		$row      = $daily_stats->get_by_date( $date );

		return $row ? (array) $row : null;
	}

	/**
	 * Get weekly aggregated metrics.
	 *
	 * @param DailyStats $daily_stats DailyStats instance.
	 * @param int        $offset      0 = last 7 days, 1 = previous 7 days.
	 * @return array<string, float>|null
	 */
	private function get_weekly_metrics( DailyStats $daily_stats, int $offset ) {
		$timezone   = wp_timezone();
		$week_shift = $offset * 7;

		$end_offset   = $week_shift + 1; // End at yesterday for offset 0.
		$start_offset = $week_shift + 7;

		$end   = ( new \DateTime( "-{$end_offset} days", $timezone ) )->format( 'Y-m-d' );
		$start = ( new \DateTime( "-{$start_offset} days", $timezone ) )->format( 'Y-m-d' );

		$row = $daily_stats->get_aggregated( $start, $end );
		return $row ? (array) $row : null;
	}

	/**
	 * Build metric cards with current, previous, and change values.
	 *
	 * @param array|null $current  Current period metrics.
	 * @param array|null $previous Previous period metrics.
	 * @return array<int, array<string, mixed>>
	 */
	private function build_metric_cards( $current, $previous ): array {
		$current  = $current ? (array) $current : [];
		$previous = $previous ? (array) $previous : [];

		$metrics = [
			[
				'key'    => 'revenue',
				'label'  => __( 'Revenue', 'sales-pulse' ),
				'format' => 'currency',
			],
			[
				'key'    => 'orders',
				'label'  => __( 'Orders', 'sales-pulse' ),
				'format' => 'number',
			],
			[
				'key'    => 'avg_order_value',
				'label'  => __( 'Avg Order Value', 'sales-pulse' ),
				'format' => 'currency',
			],
			[
				'key'    => 'items_per_order',
				'label'  => __( 'Items per Order', 'sales-pulse' ),
				'format' => 'decimal',
			],
		];

		$cards = [];
		foreach ( $metrics as $metric ) {
			$curr_val = (float) ( $current[ $metric['key'] ] ?? 0 );
			$prev_val = (float) ( $previous[ $metric['key'] ] ?? 0 );
			$change   = $prev_val > 0 ? round( ( ( $curr_val - $prev_val ) / $prev_val ) * 100, 1 ) : 0;

			$cards[] = [
				'key'      => $metric['key'],
				'label'    => $metric['label'],
				'format'   => $metric['format'],
				'current'  => round( $curr_val, 2 ),
				'previous' => round( $prev_val, 2 ),
				'change'   => $change,
			];
		}

		return $cards;
	}
}
