<?php
/**
 * Cron Manager.
 *
 * Registers and manages all scheduled jobs:
 * - Nightly snapshot (build yesterday + repair dirty dates)
 * - Backfill runner (progressive historical data fill)
 *
 * @package EC_Sales_Pulse\Core\Cron
 */

namespace EC_Sales_Pulse\Core\Cron;

use EC_Sales_Pulse\Core\Database\SystemState;
use EC_Sales_Pulse\Core\Services\SnapshotBuilder;
use EC_Sales_Pulse\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

class CronManager {
	use Get_Instance;

	/**
	 * Hook names.
	 */
	const HOOK_NIGHTLY  = 'salespulse_nightly_snapshot';
	const HOOK_BACKFILL = 'salespulse_backfill_runner';

	/**
	 * Constructor — register cron hooks and schedules.
	 */
	public function __construct() {
		// Register custom cron interval.
		add_filter( 'cron_schedules', [ $this, 'add_cron_schedules' ] );

		// Cron action handlers.
		add_action( self::HOOK_NIGHTLY, [ $this, 'run_nightly_snapshot' ] );
		add_action( self::HOOK_BACKFILL, [ $this, 'run_backfill' ] );

		// Schedule jobs on init (only if not already scheduled).
		add_action( 'init', [ $this, 'schedule_jobs' ] );

		// Admin fallback: if yesterday's snapshot is missing, build it on admin visit.
		add_action( 'admin_init', [ $this, 'maybe_fallback_snapshot' ] );
	}

	/**
	 * Add custom cron schedules.
	 *
	 * @param array<string, array<string, mixed>> $schedules Existing schedules.
	 * @return array<string, array<string, mixed>>
	 */
	public function add_cron_schedules( array $schedules ): array {
		$schedules['salespulse_five_minutes'] = [
			'interval' => 300,
			'display'  => __( 'Every 5 Minutes (Sales Pulse)', 'sales-pulse' ),
		];

		return $schedules;
	}

	/**
	 * Schedule all cron jobs if not already scheduled.
	 */
	public function schedule_jobs(): void {
		// Nightly snapshot: daily at 02:10 AM in the site timezone.
		if ( ! wp_next_scheduled( self::HOOK_NIGHTLY ) ) {
			$timestamp = $this->get_next_scheduled_time( 2, 10 );
			wp_schedule_event( $timestamp, 'daily', self::HOOK_NIGHTLY );
		}

		// Backfill runner: every 5 minutes during active backfill.
		$state = SystemState::get_instance();
		if ( ! $state->is_backfill_complete() && ! wp_next_scheduled( self::HOOK_BACKFILL ) ) {
			wp_schedule_event( time(), 'salespulse_five_minutes', self::HOOK_BACKFILL );
		}
	}

	/**
	 * Run the nightly snapshot job.
	 * Builds yesterday's snapshot and repairs dirty dates.
	 */
	public function run_nightly_snapshot(): void {
		$builder = SnapshotBuilder::get_instance();
		$builder->run_nightly();
	}

	/**
	 * Run the backfill job.
	 * Processes a batch of historical dates.
	 */
	public function run_backfill(): void {
		$builder = SnapshotBuilder::get_instance();
		$result  = $builder->run_backfill();

		// If backfill is complete, unschedule the recurring job.
		if ( ( $result['status'] ?? '' ) === 'complete' || ( $result['status'] ?? '' ) === 'no_orders' ) {
			$this->unschedule_backfill();
		}
	}

	/**
	 * Fallback: if admin visits and yesterday's snapshot is missing, trigger build.
	 * Only runs once per admin session using a transient guard.
	 */
	public function maybe_fallback_snapshot(): void {
		// Only check once per hour to avoid repeated queries.
		if ( get_transient( 'salespulse_fallback_checked' ) ) {
			return;
		}

		set_transient( 'salespulse_fallback_checked', 1, HOUR_IN_SECONDS );

		$builder = SnapshotBuilder::get_instance();
		if ( ! $builder->has_yesterday_snapshot() ) {
			$builder->build_yesterday();
		}
	}

	/**
	 * Unschedule the backfill runner.
	 */
	public function unschedule_backfill(): void {
		$timestamp = wp_next_scheduled( self::HOOK_BACKFILL );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::HOOK_BACKFILL );
		}
	}

	/**
	 * Unschedule all plugin cron jobs.
	 * Called on plugin deactivation.
	 */
	public static function unschedule_all(): void {
		$hooks = [ self::HOOK_NIGHTLY, self::HOOK_BACKFILL ];

		foreach ( $hooks as $hook ) {
			$timestamp = wp_next_scheduled( $hook );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, $hook );
			}
		}
	}

	/**
	 * Get the next occurrence of a specific time in the site timezone.
	 *
	 * @param int $hour   Hour (0-23).
	 * @param int $minute Minute (0-59).
	 * @return int Unix timestamp.
	 */
	private function get_next_scheduled_time( int $hour, int $minute ): int {
		$timezone = wp_timezone();
		$now      = new \DateTime( 'now', $timezone );
		$target   = new \DateTime( 'today', $timezone );
		$target->setTime( $hour, $minute );

		// If the target time has already passed today, schedule for tomorrow.
		if ( $now > $target ) {
			$target->modify( '+1 day' );
		}

		return $target->getTimestamp();
	}
}
