<?php
/**
 * Digest History Database Model (Phase 6.0d).
 *
 * One row per morning-briefing send attempt. Powers the free Impact tab's
 * "Morning briefings delivered" stat by giving us a count of `sent` rows
 * over a date range, instead of the previous "last send only" pattern in
 * `system_state`.
 *
 * Status values: `sent`, `failed`, `skipped`.
 *
 * @package EC_Sales_Pulse\Core\Database
 */

namespace EC_Sales_Pulse\Core\Database;

use EC_Sales_Pulse\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

/**
 * `digest_history` table model. One row per morning-briefing send attempt;
 * powers the "Morning briefings delivered" stat on the free Impact tab.
 */
class DigestHistory extends Base {
	use Get_Instance;

	/**
	 * Table name without prefix.
	 *
	 * @var string
	 */
	protected $table_name = 'digest_history';

	/**
	 * CREATE TABLE SQL for the digest_history table.
	 *
	 * @return string
	 */
	public function get_schema(): string {
		$table   = $this->get_table_name();
		$charset = $this->get_charset_collate();

		return "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			sent_at DATETIME NOT NULL,
			recipient VARCHAR(190) NOT NULL,
			status VARCHAR(20) NOT NULL,
			error_text TEXT NULL,
			is_test TINYINT(1) NOT NULL DEFAULT 0,
			PRIMARY KEY (id),
			KEY sent_at_status_idx (sent_at, status)
		) {$charset};";
	}

	/**
	 * Record one send attempt.
	 *
	 * @param array<string, mixed> $data sent_at, recipient, status, error_text, is_test.
	 *
	 * @return int Insert id, or 0 on failure.
	 */
	public function record( array $data ): int {
		if ( empty( $data['sent_at'] ) ) {
			$data['sent_at'] = current_time( 'mysql' );
		}
		if ( empty( $data['status'] ) ) {
			$data['status'] = 'sent';
		}

		$id = $this->insert( $data );
		return is_int( $id ) ? $id : 0;
	}

	/**
	 * Count rows with a given status in a date range.
	 *
	 * @param string $status  'sent' | 'failed' | 'skipped'.
	 * @param string $from    Inclusive ISO datetime.
	 * @param string $to      Exclusive ISO datetime.
	 *
	 * @return int
	 */
	public function count_in_range( string $status, string $from, string $to ): int {
		$table = $this->get_table_name();

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM `{$table}` WHERE status = %s AND is_test = 0 AND sent_at >= %s AND sent_at < %s",
				$status,
				$from,
				$to
			)
		);
		// phpcs:enable
	}

	/**
	 * Total count for a given status across all time. Used by the all-time
	 * "Morning briefings delivered" stat in the free Impact tab.
	 *
	 * @param string $status 'sent' | 'failed' | 'skipped'.
	 *
	 * @return int
	 */
	public function count_total( string $status = 'sent' ): int {
		$table = $this->get_table_name();

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM `{$table}` WHERE status = %s AND is_test = 0",
				$status
			)
		);
		// phpcs:enable
	}

	/**
	 * Delete rows older than N days. Free plugin retention is bounded by
	 * the Pro plugin's impact_retention_days when available, otherwise a
	 * conservative 730 days (two years).
	 *
	 * @param int $days Number of days to keep.
	 *
	 * @return int Rows deleted.
	 */
	public function purge_older_than( int $days ): int {
		if ( $days <= 0 ) {
			return 0;
		}
		$table  = $this->get_table_name();
		$cutoff = ( new \DateTime( "-{$days} days", wp_timezone() ) )->format( 'Y-m-d H:i:s' );

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $this->wpdb->query(
			$this->wpdb->prepare(
				"DELETE FROM `{$table}` WHERE sent_at < %s",
				$cutoff
			)
		);
		// phpcs:enable
	}
}
