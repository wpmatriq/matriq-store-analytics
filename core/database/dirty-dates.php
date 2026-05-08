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

/**
 * `dirty_dates` table model. Tracks dates whose `daily_stats` row is stale
 * because of an order edit, refund, or status change, so the nightly cron
 * can rebuild only the affected days. Once a date is repaired, the row
 * keeps a `resolved_at` stamp so the Impact tab can count repairs.
 */
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
			resolved_at DATETIME NULL,
			PRIMARY KEY (stat_date),
			KEY resolved_at_idx (resolved_at)
		) {$charset};";
	}

	/**
	 * Mark a date as dirty (needs rebuild).
	 *
	 * Idempotent on the (stat_date) primary key: an already-pending row stays
	 * pending; an already-resolved row is reopened so the next nightly run
	 * picks it up and the audit trail advances.
	 *
	 * @param string $date   Date in Y-m-d format.
	 * @param string $reason Reason for marking dirty (order_update, refund, status_change).
	 * @return bool
	 */
	public function mark_dirty( string $date, string $reason = 'order_update' ): bool {
		$table = $this->get_table_name();

		$result = $this->wpdb->query(
			$this->wpdb->prepare(
				"INSERT INTO `{$table}` (stat_date, reason, detected_at, resolved_at)
				 VALUES (%s, %s, %s, NULL)
				 ON DUPLICATE KEY UPDATE
					reason = VALUES(reason),
					detected_at = VALUES(detected_at),
					resolved_at = NULL", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$date,
				$reason,
				current_time( 'mysql' )
			)
		);

		return $result !== false;
	}

	/**
	 * Get dirty dates still pending repair.
	 *
	 * @param int $limit Max dates to return.
	 * @return array<int, \stdClass>
	 */
	public function get_pending( int $limit = 10 ): array {
		$table = $this->get_table_name();

		return $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM `{$table}` WHERE resolved_at IS NULL ORDER BY stat_date DESC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$limit
			)
		);
	}

	/**
	 * Mark a date as repaired.
	 *
	 * The row is kept (with resolved_at stamped) so the Impact dashboard can
	 * count repaired dates as a free-plugin "data foundation" stat.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return bool
	 */
	public function mark_resolved( string $date ): bool {
		$result = $this->update(
			[ 'resolved_at' => current_time( 'mysql' ) ],
			[ 'stat_date' => $date ]
		);
		return $result !== false;
	}

	/**
	 * Count dates ever repaired in a date range. Used by the Impact summary.
	 *
	 * @param string $from Inclusive ISO datetime (resolved_at >=).
	 * @param string $to   Exclusive ISO datetime (resolved_at <).
	 * @return int
	 */
	public function count_resolved_in_range( string $from, string $to ): int {
		$table = $this->get_table_name();

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM `{$table}` WHERE resolved_at IS NOT NULL AND resolved_at >= %s AND resolved_at < %s",
				$from,
				$to
			)
		);
		// phpcs:enable
	}

	/**
	 * Total count of repaired dates (for "all-time" stats).
	 *
	 * @return int
	 */
	public function count_resolved(): int {
		$table = $this->get_table_name();

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $this->wpdb->get_var(
			"SELECT COUNT(*) FROM `{$table}` WHERE resolved_at IS NOT NULL"
		);
		// phpcs:enable
	}

	/**
	 * Clear all dirty dates. Reserved for uninstall paths.
	 *
	 * @return bool
	 */
	public function clear_all(): bool {
		return $this->truncate();
	}
}
