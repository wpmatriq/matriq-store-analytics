<?php
/**
 * System State Database Model.
 *
 * Key-value store for internal plugin state tracking.
 * Avoids polluting wp_options. Tracks backfill progress, snapshot dates, versions, etc.
 *
 * @package Matriq\MSA\Core\Database
 */

namespace Matriq\MSA\Core\Database;

use Matriq\MSA\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin-level key/value state store.
 *
 * Tracks one-shot markers like the last snapshot date, last digest send
 * timestamp, db_version, backfill cursor, and similar bookkeeping that
 * doesn't fit a per-day table.
 */
class SystemState extends Base {
	use Get_Instance;

	/**
	 * Known state keys.
	 */
	public const KEY_LAST_SNAPSHOT_DATE    = 'last_snapshot_date';
	public const KEY_BACKFILL_START        = 'backfill_start';
	public const KEY_BACKFILL_CURSOR       = 'backfill_cursor';
	public const KEY_BACKFILL_COMPLETE     = 'backfill_complete';
	public const KEY_DB_VERSION            = 'db_version';
	public const KEY_PLUGIN_VERSION        = 'plugin_version';
	public const KEY_LAST_DIGEST_SENT_DATE = 'last_digest_sent_date';
	public const KEY_LAST_DIGEST_SENT_AT   = 'last_digest_sent_at';

	/**
	 * Counters that replaced the `digest_history` table in v2 of the free
	 * schema. The table stored 1 row per send attempt purely to power the
	 * Free Impact tile's all-time "morning briefings delivered" count -
	 * incrementing two keys does the same job in 1/10th the storage.
	 */
	public const KEY_DIGEST_SENT_TOTAL   = 'digest_sent_total';
	public const KEY_DIGEST_FAILED_TOTAL = 'digest_failed_total';
	public const KEY_DIGEST_LAST_ERROR   = 'digest_last_error';

	/**
	 * Counter that replaced the `dirty_dates.resolved_at` count read in v3
	 * of the free schema. Powers the Free Impact "edits caught and repaired"
	 * tile without keeping a per-edit row forever.
	 */
	public const KEY_REPAIRED_TOTAL = 'repaired_total';

	/**
	 * Table name without prefix.
	 *
	 * @var string
	 */
	protected $table_name = 'system_state';

	/**
	 * Primary key column.
	 *
	 * @var string
	 */
	protected $primary_key = 'state_key';

	/**
	 * Get the CREATE TABLE SQL.
	 *
	 * @return string
	 */
	public function get_schema(): string {
		$table   = $this->get_table_name();
		$charset = $this->get_charset_collate();

		return "CREATE TABLE {$table} (
			state_key VARCHAR(100) NOT NULL,
			state_value TEXT NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY (state_key)
		) {$charset};";
	}

	/**
	 * Get a state value by key.
	 *
	 * @param string $key     State key.
	 * @param mixed  $default Default value if not found.
	 * @return mixed
	 */
	public function get( string $key, $default = null ) {
		if ( ! $this->table_exists() ) {
			return $default;
		}

		global $wpdb;

		$value = $this->wpdb->get_var(
			$wpdb->prepare(
				'SELECT state_value FROM %i WHERE state_key = %s',
				$this->get_table_name(),
				$key
			)
		);

		return $value !== null ? $value : $default;
	}

	/**
	 * Set a state value (insert or update).
	 *
	 * @param string $key   State key.
	 * @param string $value State value.
	 * @return bool
	 */
	public function set( string $key, string $value ): bool {
		$result = $this->replace(
			[
				'state_key'   => $key,
				'state_value' => $value,
				'updated_at'  => current_time( 'mysql' ),
			]
		);

		return $result !== false;
	}

	/**
	 * Increment an integer counter stored under `$key`. Treats a missing or
	 * non-numeric existing value as 0. Returns the new total.
	 *
	 * @param string $key  Counter key.
	 * @param int    $step Amount to add (default 1).
	 * @return int
	 */
	public function increment( string $key, int $step = 1 ): int {
		$current = (int) ( $this->get( $key, '0' ) ?? 0 );
		$next    = $current + $step;
		$this->set( $key, (string) $next );
		return $next;
	}

	/**
	 * Remove a state key.
	 *
	 * @param string $key State key.
	 * @return bool
	 */
	public function remove( string $key ): bool {
		$result = $this->delete( [ 'state_key' => $key ] );
		return $result !== false;
	}

	/**
	 * Check if backfill is complete.
	 *
	 * @return bool
	 */
	public function is_backfill_complete(): bool {
		return $this->get( self::KEY_BACKFILL_COMPLETE ) === 'yes';
	}

	/**
	 * Get the last snapshot date.
	 *
	 * @return string|null Date in Y-m-d format.
	 */
	public function get_last_snapshot_date() {
		return $this->get( self::KEY_LAST_SNAPSHOT_DATE );
	}

	/**
	 * Set the last snapshot date.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return bool
	 */
	public function set_last_snapshot_date( string $date ): bool {
		return $this->set( self::KEY_LAST_SNAPSHOT_DATE, $date );
	}

	/**
	 * Get the `updated_at` timestamp of the last-snapshot record.
	 *
	 * Used by the dashboard header to surface a LIVE vs STALE badge.
	 *
	 * @return string|null ISO8601-compatible MySQL datetime, or null if never set.
	 */
	public function get_last_snapshot_timestamp() {
		if ( ! $this->table_exists() ) {
			return null;
		}

		global $wpdb;

		$value = $this->wpdb->get_var(
			$wpdb->prepare(
				'SELECT updated_at FROM %i WHERE state_key = %s',
				$this->get_table_name(),
				self::KEY_LAST_SNAPSHOT_DATE
			)
		);

		return $value !== null ? $value : null;
	}
}
