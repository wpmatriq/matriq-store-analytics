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

use EC_Sales_Pulse\Core\Controllers\SettingsController;
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
						'enum'              => [ 'daily', 'weekly', 'monthly' ],
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
		$period      = $request->get_param( 'period' ) ?? 'daily';
		$daily_stats = DailyStats::get_instance();

		/**
		 * Fires once the resolved period for this Overview request is known.
		 * Premium extensions listen here to scope per-window behaviour
		 * (e.g. only enrich diagnosis on the daily window).
		 *
		 * @since x.x.x
		 *
		 * @param string $period One of `daily` | `weekly` | `monthly`.
		 */
		do_action( 'salespulse_overview_period_resolved', $period );

		if ( $period === 'monthly' ) {
			$current  = $this->get_rolling_metrics( $daily_stats, 0, 30 );
			$previous = $this->get_rolling_metrics( $daily_stats, 1, 30 );
		} elseif ( $period === 'weekly' ) {
			$current  = $this->get_rolling_metrics( $daily_stats, 0, 7 );
			$previous = $this->get_rolling_metrics( $daily_stats, 1, 7 );
		} else {
			$current  = $this->get_daily_metrics( $daily_stats, 0 );
			$previous = $this->get_daily_metrics( $daily_stats, 1 );
		}

		// Run diagnosis with configured sensitivity.
		$sensitivity           = (string) SettingsController::get( 'diagnosis_sensitivity', 'balanced' );
		$diagnosis_engine      = DiagnosisEngine::get_instance();
		$diagnosis             = $diagnosis_engine->diagnose( $current, $previous, $sensitivity );
		$diagnosis['severity'] = $this->severity_from_diagnosis( $diagnosis );

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

		$response = [
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
		];

		/**
		 * Filter the Overview REST response payload.
		 *
		 * Premium extensions append keys such as `forecast`, `anomalies`, or
		 * `pro_recommendations` so the Overview page can render Pro insights
		 * inline without a second round-trip.
		 *
		 * @since x.x.x
		 *
		 * @param array<string, mixed> $response The response payload.
		 * @param string               $period   Period parameter (daily|weekly|monthly).
		 */
		$response = apply_filters( 'salespulse_overview_response', $response, $period );

		return $this->success( $response );
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

		return $this->success(
			[
				'days'  => $days,
				'start' => $start,
				'end'   => $end,
				'trend' => $trend,
			] 
		);
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
	 * Get aggregated metrics for a rolling window of N days.
	 *
	 * @param DailyStats $daily_stats DailyStats instance.
	 * @param int        $offset      0 = most recent window, 1 = previous window, etc.
	 * @param int        $days        Window length in days (7 for weekly, 30 for monthly).
	 * @return array<string, float>|null
	 */
	private function get_rolling_metrics( DailyStats $daily_stats, int $offset, int $days ) {
		$timezone = wp_timezone();
		$shift    = $offset * $days;

		$end_offset   = $shift + 1; // End at yesterday for offset 0.
		$start_offset = $shift + $days;

		$end   = ( new \DateTime( "-{$end_offset} days", $timezone ) )->format( 'Y-m-d' );
		$start = ( new \DateTime( "-{$start_offset} days", $timezone ) )->format( 'Y-m-d' );

		$row = $daily_stats->get_aggregated( $start, $end );
		return $row ? (array) $row : null;
	}

	/**
	 * Derive a UI-facing severity code from a diagnosis result.
	 *
	 * Mapping:
	 *   growth  → success (Surge)
	 *   decline → danger  (Needs Attention)
	 *   stable  → info    (Stable)
	 *
	 * @param array<string, mixed> $diagnosis Diagnosis array from DiagnosisEngine.
	 * @return string One of success|warning|info|danger.
	 */
	private function severity_from_diagnosis( array $diagnosis ): string {
		$direction = $diagnosis['direction'] ?? 'stable';
		$factor    = $diagnosis['primary_factor'] ?? 'none';

		// Low-sample comparisons (e.g. 1 order yesterday vs 1 the day before)
		// mathematically swing by hundreds of percent. Suppress the green/red
		// surge/decline treatment so the headline carries the truth without
		// the dashboard visually screaming about it.
		if ( $factor === 'low_sample' ) {
			return 'info';
		}

		if ( $direction === 'growth' ) {
			return 'success';
		}

		if ( $direction === 'decline' ) {
			return 'danger';
		}

		return 'info';
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
