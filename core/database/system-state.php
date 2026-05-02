<?php
/**
 * System State Database Model.
 *
 * Key-value store for internal plugin state tracking.
 * Avoids polluting wp_options. Tracks backfill progress, snapshot dates, versions, etc.
 *
 * @package EC_Sales_Pulse\Core\Database
 */

namespace EC_Sales_Pulse\Core\Database;

use EC_Sales_Pulse\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

class SystemState extends Base {
	use Get_Instance;

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
	 * Known state keys.
	 */
	const KEY_LAST_SNAPSHOT_DATE     = 'last_snapshot_date';
	const KEY_BACKFILL_START         = 'backfill_start';
	const KEY_BACKFILL_CURSOR        = 'backfill_cursor';
	const KEY_BACKFILL_COMPLETE      = 'backfill_complete';
	const KEY_DB_VERSION             = 'db_version';
	const KEY_PLUGIN_VERSION         = 'plugin_version';
	const KEY_LAST_DIGEST_SENT_DATE  = 'last_digest_sent_date';
	const KEY_LAST_DIGEST_SENT_AT    = 'last_digest_sent_at';

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
		$table = $this->get_table_name();

		if ( ! $this->table_exists() ) {
			return $default;
		}

		$value = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT state_value FROM `{$table}` WHERE state_key = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
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
		$result = $this->replace( [
			'state_key'   => $key,
			'state_value' => $value,
			'updated_at'  => current_time( 'mysql' ),
		] );

		return $result !== false;
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
		$table = $this->get_table_name();

		if ( ! $this->table_exists() ) {
			return null;
		}

		$value = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT updated_at FROM `{$table}` WHERE state_key = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				self::KEY_LAST_SNAPSHOT_DATE
			)
		);

		return $value !== null ? $value : null;
	}
}
