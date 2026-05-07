<?php
/**
 * History Controller.
 *
 * Returns a paginated list of daily diagnosis results.
 * Each entry shows the date, headline, direction, and severity -
 * building trust through a visible track record.
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

/**
 * REST controller for the History tab. Returns paginated daily snapshots
 * with diagnoses so the merchant can scrub backwards through time.
 */
class History extends BaseController {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'history';

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_history' ],
				'permission_callback' => [ $this, 'admin_permission_check' ],
				'args'                => [
					'page'     => [
						'type'              => 'integer',
						'default'           => 1,
						'minimum'           => 1,
						'sanitize_callback' => 'absint',
					],
					'per_page' => [
						'type'              => 'integer',
						'default'           => 10,
						'minimum'           => 1,
						'maximum'           => 60,
						'sanitize_callback' => 'absint',
					],
				],
			]
		);
	}

	/**
	 * Get paginated history of daily diagnoses.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_history( \WP_REST_Request $request ): \WP_REST_Response {
		$page     = $this->get_int_param( $request, 'page', 1 );
		$per_page = $this->get_int_param( $request, 'per_page', 10 );

		$daily_stats = DailyStats::get_instance();
		$total       = $daily_stats->count();
		$total_pages = (int) ceil( $total / $per_page );

		// Get dates descending with pagination.
		$offset = ( $page - 1 ) * $per_page;
		$rows   = $daily_stats->get_paginated( $per_page, $offset );

		if ( empty( $rows ) ) {
			return $this->success(
				[
					'items'       => [],
					'total'       => $total,
					'total_pages' => $total_pages,
					'page'        => $page,
				] 
			);
		}

		/**
		 * History is a track record of past diagnoses, not a fresh briefing.
		 * Premium AI enrichers gate on this signal so they don't issue an LLM
		 * call per row (the Phase 2 enricher had us at ~21s/page before this).
		 *
		 * @param string $context Diagnosis context. `history` is non-`daily`,
		 *                        which is the existing skip signal.
		 */
		do_action( 'salespulse_overview_period_resolved', 'history' );

		// Batch the previous-day rows in one IN-list query instead of N
		// per-row lookups. Same shape as the per-row method; just keyed by date.
		$prev_dates = [];
		foreach ( $rows as $row ) {
			$prev_dates[] = gmdate( 'Y-m-d', strtotime( $row->stat_date . ' -1 day' ) );
		}
		$prev_by_date = $this->fetch_prev_rows_by_date( $daily_stats, $prev_dates );

		// Pull every campaign once, walk in PHP. Most stores have < 50 campaigns
		// total, so this is cheaper than running N range queries.
		$campaigns_index = $this->index_campaigns( Campaigns::get_instance()->get_all( 200 ) );

		$diagnosis_engine = DiagnosisEngine::get_instance();
		$action_engine    = ActionEngine::get_instance();
		$sensitivity      = (string) SettingsController::get( 'diagnosis_sensitivity', 'balanced' );

		$items = [];
		foreach ( $rows as $row ) {
			$prev_date = gmdate( 'Y-m-d', strtotime( $row->stat_date . ' -1 day' ) );
			$prev_row  = $prev_by_date[ $prev_date ] ?? null;

			$diagnosis      = $diagnosis_engine->diagnose( $row, $prev_row, $sensitivity );
			$campaign       = $this->campaign_for_date( $campaigns_index, $row->stat_date );
			$recommendation = $action_engine->recommend( $diagnosis, $campaign );

			$items[] = [
				'date'           => $row->stat_date,
				'revenue'        => (float) $row->revenue,
				'orders'         => (int) $row->orders,
				'direction'      => $diagnosis['direction'],
				'change_percent' => $diagnosis['revenue_change_percent'],
				'headline'       => $diagnosis['headline'],
				'primary_factor' => $diagnosis['primary_factor'],
				'confidence'     => $diagnosis['confidence'],
				'severity'       => $recommendation['severity'],
				'campaign'       => $campaign ? $campaign->name : null,
			];
		}

		return $this->success(
			[
				'items'       => $items,
				'total'       => $total,
				'total_pages' => $total_pages,
				'page'        => $page,
			] 
		);
	}

	/**
	 * Fetch a list of daily_stats rows in one query, keyed by stat_date.
	 *
	 * @param DailyStats    $daily_stats Database accessor.
	 * @param array<string> $dates       Y-m-d dates to fetch.
	 * @return array<string, object>
	 */
	private function fetch_prev_rows_by_date( DailyStats $daily_stats, array $dates ): array {
		$dates = array_values( array_unique( array_filter( $dates ) ) );
		if ( empty( $dates ) ) {
			return [];
		}

		// Cheapest correct: pick the min/max range and fetch once. Returns more
		// rows than needed if the dates are sparse, but the table is small and
		// the query stays under one round-trip.
		sort( $dates );
		$rows = $daily_stats->get_range( reset( $dates ), end( $dates ) );

		$by_date = [];
		foreach ( $rows as $row ) {
			$by_date[ (string) $row->stat_date ] = $row;
		}
		return $by_date;
	}

	/**
	 * Sort campaigns descending by id so the first match for a date is the
	 * most-recently-created active campaign (matches the per-row query's
	 * `ORDER BY id DESC LIMIT 1` semantics).
	 *
	 * @param array<object> $campaigns Raw rows from Campaigns::get_all.
	 * @return array<object>
	 */
	private function index_campaigns( array $campaigns ): array {
		usort(
			$campaigns,
			static fn( $a, $b ) => (int) $b->id - (int) $a->id
		);
		return $campaigns;
	}

	/**
	 * Find the active campaign for a date in the pre-fetched list. Mirrors
	 * `Campaigns::get_active_for_date()` semantics but in PHP.
	 *
	 * @param array<object> $campaigns Sorted descending by id.
	 * @param string        $date      Y-m-d.
	 * @return object|null
	 */
	private function campaign_for_date( array $campaigns, string $date ) {
		foreach ( $campaigns as $campaign ) {
			$start = (string) ( $campaign->start_date ?? '' );
			$end   = (string) ( $campaign->end_date ?? '' );
			if ( $start === '' || $start > $date ) {
				continue;
			}
			if ( $end === '' || $end >= $date ) {
				return $campaign;
			}
		}
		return null;
	}
}
