<?php
/**
 * History Controller.
 *
 * Returns a paginated list of daily diagnosis results.
 * Each entry shows the date, headline, direction, and severity —
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
						'default'           => 14,
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
		$per_page = $this->get_int_param( $request, 'per_page', 14 );

		$daily_stats = DailyStats::get_instance();
		$total       = $daily_stats->count();
		$total_pages = (int) ceil( $total / $per_page );

		// Get dates descending with pagination.
		$offset = ( $page - 1 ) * $per_page;
		$rows   = $daily_stats->get_paginated( $per_page, $offset );

		if ( empty( $rows ) ) {
			return $this->success( [
				'items'       => [],
				'total'       => $total,
				'total_pages' => $total_pages,
				'page'        => $page,
			] );
		}

		// For each day, we need the previous day to run diagnosis.
		$diagnosis_engine = DiagnosisEngine::get_instance();
		$action_engine    = ActionEngine::get_instance();
		$campaigns_db     = Campaigns::get_instance();
		$sensitivity      = (string) SettingsController::get( 'diagnosis_sensitivity', 'balanced' );

		$items = [];
		foreach ( $rows as $row ) {
			$prev_date = gmdate( 'Y-m-d', strtotime( $row->stat_date . ' -1 day' ) );
			$prev_row  = $daily_stats->get_by_date( $prev_date );

			$diagnosis      = $diagnosis_engine->diagnose( $row, $prev_row, $sensitivity );
			$campaign        = $campaigns_db->get_active_for_date( $row->stat_date );
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

		return $this->success( [
			'items'       => $items,
			'total'       => $total,
			'total_pages' => $total_pages,
			'page'        => $page,
		] );
	}
}
