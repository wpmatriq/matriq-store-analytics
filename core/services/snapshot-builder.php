<?php
/**
 * Snapshot Builder Service.
 *
 * Aggregates daily metrics from DataCollector and writes to daily_stats table.
 * Called by nightly cron and backfill runner.
 *
 * @package EC_Sales_Pulse\Core\Services
 */

namespace EC_Sales_Pulse\Core\Services;

use EC_Sales_Pulse\Core\Database\DailyStats;
use EC_Sales_Pulse\Core\Database\DirtyDates;
use EC_Sales_Pulse\Core\Database\SystemState;
use EC_Sales_Pulse\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

class SnapshotBuilder {
	use Get_Instance;

	/**
	 * Build and store a snapshot for a specific date.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return bool True on success, false on failure.
	 */
	public function build_snapshot( string $date ): bool {
		$collector = DataCollector::get_instance();

		if ( ! $collector->are_analytics_tables_available() ) {
			return false;
		}

		$metrics = $collector->collect_day_metrics( $date );

		if ( empty( $metrics ) ) {
			return false;
		}

		$daily_stats = DailyStats::get_instance();
		$result      = $daily_stats->upsert( $metrics );

		return $result !== false;
	}

	/**
	 * Build yesterday's snapshot (primary nightly operation).
	 *
	 * @return bool
	 */
	public function build_yesterday(): bool {
		$yesterday = $this->get_yesterday_date();
		$result    = $this->build_snapshot( $yesterday );

		if ( $result ) {
			$state = SystemState::get_instance();
			$state->set_last_snapshot_date( $yesterday );
		}

		return $result;
	}

	/**
	 * Process and repair all dirty dates.
	 *
	 * @param int $max_dates Maximum dirty dates to process per run.
	 * @return int Number of dates repaired.
	 */
	public function repair_dirty_dates( int $max_dates = 5 ): int {
		$dirty_dates = DirtyDates::get_instance();
		$pending     = $dirty_dates->get_pending( $max_dates );
		$repaired    = 0;

		foreach ( $pending as $dirty ) {
			$success = $this->build_snapshot( $dirty->stat_date );
			if ( $success ) {
				$dirty_dates->clear_date( $dirty->stat_date );
				++$repaired;
			}
		}

		return $repaired;
	}

	/**
	 * Run the full nightly snapshot process.
	 * Step 1: Build yesterday.
	 * Step 2: Repair dirty dates.
	 *
	 * @return array<string, mixed> Summary of operations.
	 */
	public function run_nightly(): array {
		$summary = [
			'yesterday_built' => false,
			'dirty_repaired'  => 0,
			'timestamp'       => current_time( 'mysql' ),
		];

		$summary['yesterday_built'] = $this->build_yesterday();
		$summary['dirty_repaired']  = $this->repair_dirty_dates();

		return $summary;
	}

	/**
	 * Backfill historical snapshots (reverse chronological).
	 * Processes a limited batch per call to avoid timeouts.
	 *
	 * @param int $batch_size Number of days to process per batch.
	 * @return array<string, mixed> Backfill progress info.
	 */
	public function run_backfill( int $batch_size = 3 ): array {
		$state     = SystemState::get_instance();
		$collector = DataCollector::get_instance();

		// Check if backfill is already complete.
		if ( $state->is_backfill_complete() ) {
			return [ 'status' => 'complete' ];
		}

		// Determine the backfill range.
		$oldest_order = $collector->get_oldest_order_date();
		if ( ! $oldest_order ) {
			$state->set( SystemState::KEY_BACKFILL_COMPLETE, 'yes' );
			return [ 'status' => 'no_orders' ];
		}

		// Limit backfill to 12 months.
		$twelve_months_ago = gmdate( 'Y-m-d', strtotime( '-12 months' ) );
		$backfill_start    = max( $oldest_order, $twelve_months_ago );

		$state->set( SystemState::KEY_BACKFILL_START, $backfill_start );

		// Find cursor (where we left off) — work backwards from yesterday.
		$cursor = $state->get( SystemState::KEY_BACKFILL_CURSOR );
		if ( ! $cursor ) {
			$cursor = $this->get_yesterday_date();
		}

		// Get missing dates from cursor working backwards.
		$daily_stats   = DailyStats::get_instance();
		$missing_dates = $daily_stats->get_missing_dates( $backfill_start, $cursor );

		// Sort descending (newest first — reverse chronological).
		rsort( $missing_dates );

		if ( empty( $missing_dates ) ) {
			$state->set( SystemState::KEY_BACKFILL_COMPLETE, 'yes' );
			return [ 'status' => 'complete' ];
		}

		// Process batch.
		$processed = 0;
		$start     = microtime( true );
		$max_time  = 15; // seconds.

		foreach ( $missing_dates as $date ) {
			if ( $processed >= $batch_size ) {
				break;
			}

			// Time guard — stop before hitting 15 seconds.
			if ( ( microtime( true ) - $start ) > $max_time ) {
				break;
			}

			$this->build_snapshot( $date );
			$state->set( SystemState::KEY_BACKFILL_CURSOR, $date );
			++$processed;
		}

		// Check if we're done now.
		$remaining = count( $missing_dates ) - $processed;
		if ( $remaining <= 0 ) {
			$state->set( SystemState::KEY_BACKFILL_COMPLETE, 'yes' );
		}

		return [
			'status'    => $remaining > 0 ? 'in_progress' : 'complete',
			'processed' => $processed,
			'remaining' => max( 0, $remaining ),
			'total'     => count( $missing_dates ),
		];
	}

	/**
	 * Check if yesterday's snapshot exists (for cron fallback).
	 *
	 * @return bool
	 */
	public function has_yesterday_snapshot(): bool {
		return DailyStats::get_instance()->has_snapshot( $this->get_yesterday_date() );
	}

	/**
	 * Get yesterday's date in store timezone.
	 *
	 * @return string Y-m-d format.
	 */
	private function get_yesterday_date(): string {
		$timezone  = wp_timezone();
		$yesterday = new \DateTime( 'yesterday', $timezone );
		return $yesterday->format( 'Y-m-d' );
	}
}
