<?php
/**
 * Dirty Dates Database Model.
 *
 * Tracks dates that need snapshot rebuild due to order edits, refunds, or status changes.
 * Nightly cron processes and clears these entries.
 *
 * @package EC_Sales_Pulse\Core\Database
 */

namespace EC_Sales_Pulse\Core\Database;

use EC_Sales_Pulse\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

class DirtyDates extends Base {
	use Get_Instance;

	/**
	 * Table name without prefix.
	 *
	 * @var string
	 */
	protected $table_name = 'dirty_dates';

	/**
	 * Primary key column.
	 *
	 * @var string
	 */
	protected $primary_key = 'stat_date';

	/**
	 * Get the CREATE TABLE SQL.
	 *
	 * @return string
	 */
	public function get_schema(): string {
		$table   = $this->get_table_name();
		$charset = $this->get_charset_collate();

		return "CREATE TABLE {$table} (
			stat_date DATE NOT NULL,
			reason VARCHAR(50) DEFAULT NULL,
			detected_at DATETIME NOT NULL,
			PRIMARY KEY (stat_date)
		) {$charset};";
	}

	/**
	 * Mark a date as dirty (needs rebuild).
	 * Uses INSERT IGNORE to avoid duplicates — very lightweight.
	 *
	 * @param string $date   Date in Y-m-d format.
	 * @param string $reason Reason for marking dirty (order_update, refund, status_change).
	 * @return bool
	 */
	public function mark_dirty( string $date, string $reason = 'order_update' ): bool {
		$table = $this->get_table_name();

		$result = $this->wpdb->query(
			$this->wpdb->prepare(
				"INSERT IGNORE INTO `{$table}` (stat_date, reason, detected_at) VALUES (%s, %s, %s)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$date,
				$reason,
				current_time( 'mysql' )
			)
		);

		return $result !== false;
	}

	/**
	 * Get all dirty dates that need processing.
	 *
	 * @param int $limit Max dates to return.
	 * @return array<object>
	 */
	public function get_pending( int $limit = 10 ): array {
		$table = $this->get_table_name();

		return $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM `{$table}` ORDER BY stat_date DESC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$limit
			)
		);
	}

	/**
	 * Remove a date from dirty list after successful rebuild.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return bool
	 */
	public function clear_date( string $date ): bool {
		$result = $this->delete( [ 'stat_date' => $date ] );
		return $result !== false;
	}

	/**
	 * Clear all dirty dates.
	 *
	 * @return bool
	 */
	public function clear_all(): bool {
		return $this->truncate();
	}
}
