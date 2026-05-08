<?php
/**
 * Dirty-dates store (option-backed in v3).
 *
 * Tracks dates whose `daily_stats` snapshot is stale because an underlying
 * WC order was edited / refunded / status-changed. The snapshot rebuilder
 * drains the pending set on every nightly tick and increments the repaired
 * counter so the Free Impact tile can show "edits caught & repaired".
 *
 * Originally a custom DB table; consolidated into a single `wp_options`
 * row + a `system_state` counter in v3 because the table peaked at ~30
 * pending rows on a typical store and the only `resolved_at` reader was
 * one all-time count tile - well below the threshold where a dedicated
 * table earns its weight.
 *
 * Concurrency: order-edit storms could race the read-modify-write on the
 * pending set. The plan accepts the race - if a date is dropped, the next
 * order on it will mark it dirty again, and the cron rebuild is idempotent
 * either way.
 *
 * @package EC_Sales_Pulse\Core\Database
 */

namespace EC_Sales_Pulse\Core\Database;

use EC_Sales_Pulse\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

/**
 * Option-backed persistence for the dirty-dates repair queue. Public API
 * mirrors the table-era model so callers (`OrderHooks`, `SnapshotBuilder`,
 * `ImpactSummary`) work unchanged.
 *
 * @phpstan-consistent-constructor
 */
class DirtyDates {
	use Get_Instance;

	/**
	 * `wp_options` row that holds the pending date set as a `[ date => reason ]`
	 * map. Non-autoloaded - hooked path only.
	 */
	public const OPTION_KEY = 'salespulse_dirty_dates';

	/**
	 * Returns an empty string. Retained so anything that asks the model for
	 * its CREATE TABLE statement (Schema iterator, accidental typing) gets a
	 * no-op instead of a fatal.
	 *
	 * @return string
	 */
	public function get_schema(): string {
		return '';
	}

	/**
	 * Mark a date as dirty so the nightly rebuilder picks it up. Idempotent:
	 * a date already in the set keeps its earlier `detected_at` to satisfy
	 * "first time we noticed" semantics; reason is overwritten with the
	 * latest signal.
	 *
	 * @param string $date   YYYY-MM-DD.
	 * @param string $reason Why it's dirty (order_update | refund | status_change).
	 * @return bool True on successful persist.
	 */
	public function mark_dirty( string $date, string $reason = 'order_update' ): bool {
		if ( $date === '' ) {
			return false;
		}

		$pending = $this->load();

		if ( ! isset( $pending[ $date ] ) ) {
			$pending[ $date ] = [
				'reason'      => $reason,
				'detected_at' => current_time( 'mysql' ),
			];
		} else {
			$pending[ $date ]['reason'] = $reason;
		}

		return $this->save( $pending );
	}

	/**
	 * Pending dates in detection order, capped at `$limit`. Returned as
	 * stdClass casts so callers can read `$row->stat_date` / `->reason` /
	 * `->detected_at` as before.
	 *
	 * @param int $limit Max rows to return.
	 * @return array<int, \stdClass>
	 */
	public function get_pending( int $limit = 10 ): array {
		$pending = $this->load();
		if ( $limit > 0 ) {
			$pending = array_slice( $pending, 0, $limit, true );
		}

		$out = [];
		foreach ( $pending as $date => $meta ) {
			$out[] = (object) [
				'stat_date'   => (string) $date,
				'reason'      => (string) ( $meta['reason'] ?? 'order_update' ),
				'detected_at' => (string) ( $meta['detected_at'] ?? '' ),
			];
		}
		return $out;
	}

	/**
	 * Drain a single date from the pending set + increment the repaired
	 * counter so the Free Impact tile reflects the repair.
	 *
	 * @param string $date YYYY-MM-DD.
	 * @return bool True if the date was previously in the set.
	 */
	public function mark_resolved( string $date ): bool {
		$pending = $this->load();
		if ( ! isset( $pending[ $date ] ) ) {
			return false;
		}

		unset( $pending[ $date ] );
		$saved = $this->save( $pending );

		if ( $saved ) {
			SystemState::get_instance()->increment( SystemState::KEY_REPAIRED_TOTAL );
		}

		return $saved;
	}

	/**
	 * Count of all-time repaired edits. Reads the `system_state` counter
	 * that replaces the v2-era `WHERE resolved_at IS NOT NULL` table scan.
	 *
	 * @return int
	 */
	public function count_resolved(): int {
		return (int) ( SystemState::get_instance()->get( SystemState::KEY_REPAIRED_TOTAL, '0' ) ?? 0 );
	}

	/**
	 * Memoised option fetch. Returns the persisted pending map; default
	 * empty array if the option is missing or malformed.
	 *
	 * @return array<string, array<string, string>>
	 */
	private function load(): array {
		$stored = get_option( self::OPTION_KEY, [] );
		if ( ! is_array( $stored ) ) {
			return [];
		}

		$out = [];
		foreach ( $stored as $date => $meta ) {
			$key = (string) $date;
			if ( $key === '' ) {
				continue;
			}
			if ( is_array( $meta ) ) {
				$out[ $key ] = [
					'reason'      => (string) ( $meta['reason'] ?? 'order_update' ),
					'detected_at' => (string) ( $meta['detected_at'] ?? '' ),
				];
			}
		}
		return $out;
	}

	/**
	 * Persist the pending map. Non-autoloaded so order-hook bursts on a
	 * page load don't autoload-grow.
	 *
	 * @param array<string, array<string, string>> $pending Full pending map.
	 * @return bool
	 */
	private function save( array $pending ): bool {
		$ok = update_option( self::OPTION_KEY, $pending, false );
		// `update_option` returns false on a no-op write (data unchanged);
		// treat that as success because the caller's intent is satisfied.
		return $ok || get_option( self::OPTION_KEY ) === $pending;
	}
}
