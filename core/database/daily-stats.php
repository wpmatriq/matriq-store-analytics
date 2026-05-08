<?php
/**
 * Daily Stats Database Model.
 *
 * Core brain of Sales Pulse - one row per day of store health.
 * Dashboard reads from this table only for instant performance.
 *
 * @package EC_Sales_Pulse\Core\Database
 */

namespace EC_Sales_Pulse\Core\Database;

use EC_Sales_Pulse\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

/**
 * `daily_stats` table model. One row per day with the rolled-up store
 * metrics (revenue, orders, items, AOV, refunds, new-vs-returning) that
 * every diagnosis, comparison, and chart reads from.
 */
class DailyStats extends Base {
	use Get_Instance;

	/**
	 * Table name without prefix.
	 *
	 * @var string
	 */
	protected $table_name = 'daily_stats';

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
			revenue DECIMAL(14,2) NOT NULL DEFAULT 0,
			orders INT UNSIGNED NOT NULL DEFAULT 0,
			items_sold INT UNSIGNED NOT NULL DEFAULT 0,
			avg_order_value DECIMAL(14,2) NOT NULL DEFAULT 0,
			items_per_order DECIMAL(10,2) NOT NULL DEFAULT 0,
			avg_item_price DECIMAL(14,2) NOT NULL DEFAULT 0,
			new_customers INT UNSIGNED NOT NULL DEFAULT 0,
			returning_customers INT UNSIGNED NOT NULL DEFAULT 0,
			discount_total DECIMAL(14,2) NOT NULL DEFAULT 0,
			refund_total DECIMAL(14,2) NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL,
			updated_at DATETIME DEFAULT NULL,
			PRIMARY KEY (stat_date),
			KEY revenue_idx (revenue),
			KEY orders_idx (orders)
		) {$charset};";
	}

	/**
	 * Get snapshot for a specific date.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return \stdClass|null
	 */
	public function get_by_date( string $date ) {
		return $this->find( $date );
	}

	/**
	 * Get snapshots for a date range.
	 *
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @return array<int, \stdClass>
	 */
	public function get_range( string $start_date, string $end_date ): array {
		$table = $this->get_table_name();

		$rows = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM `{$table}` WHERE stat_date BETWEEN %s AND %s ORDER BY stat_date ASC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$start_date,
				$end_date
			)
		);

		return is_array( $rows ) ? $rows : [];
	}

	/**
	 * Get aggregated metrics for a date range (for weekly/monthly comparison).
	 *
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @return \stdClass|null Aggregated metrics.
	 */
	public function get_aggregated( string $start_date, string $end_date ) {
		$table = $this->get_table_name();

		return $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT
					SUM(revenue) as revenue,
					SUM(orders) as orders,
					SUM(items_sold) as items_sold,
					SUM(new_customers) as new_customers,
					SUM(returning_customers) as returning_customers,
					SUM(discount_total) as discount_total,
					SUM(refund_total) as refund_total,
					CASE WHEN SUM(orders) > 0 THEN SUM(revenue) / SUM(orders) ELSE 0 END as avg_order_value,
					CASE WHEN SUM(orders) > 0 THEN SUM(items_sold) / SUM(orders) ELSE 0 END as items_per_order,
					CASE WHEN SUM(items_sold) > 0 THEN SUM(revenue) / SUM(items_sold) ELSE 0 END as avg_item_price
				FROM `{$table}`
				WHERE stat_date BETWEEN %s AND %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$start_date,
				$end_date
			)
		);
	}

	/**
	 * Upsert a daily snapshot (insert or update if date exists).
	 *
	 * @param array<string, mixed> $data Snapshot data.
	 * @return int|false
	 */
	public function upsert( array $data ) {
		$data['updated_at'] = current_time( 'mysql' );

		if ( ! isset( $data['created_at'] ) ) {
			$data['created_at'] = current_time( 'mysql' );
		}

		return $this->replace( $data );
	}

	/**
	 * Get the most recent snapshot date.
	 *
	 * @return string|null Date in Y-m-d format, or null.
	 */
	public function get_latest_date() {
		$table = $this->get_table_name();
		return $this->wpdb->get_var( "SELECT MAX(stat_date) FROM `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Get the oldest snapshot date.
	 *
	 * @return string|null Date in Y-m-d format, or null.
	 */
	public function get_oldest_date() {
		$table = $this->get_table_name();
		return $this->wpdb->get_var( "SELECT MIN(stat_date) FROM `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Check if a snapshot exists for a given date.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return bool
	 */
	public function has_snapshot( string $date ): bool {
		$table = $this->get_table_name();
		$count = (int) $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM `{$table}` WHERE stat_date = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$date
			)
		);
		return $count > 0;
	}

	/**
	 * Get paginated snapshots ordered by date descending.
	 *
	 * @param int $limit  Number of rows.
	 * @param int $offset Row offset.
	 * @return array<int, \stdClass>
	 */
	public function get_paginated( int $limit, int $offset = 0 ): array {
		$table = $this->get_table_name();

		$rows = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM `{$table}` ORDER BY stat_date DESC LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$limit,
				$offset
			)
		);

		return is_array( $rows ) ? $rows : [];
	}

	/**
	 * Get missing dates in a range (dates without snapshots).
	 *
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @return array<string> Array of date strings.
	 */
	public function get_missing_dates( string $start_date, string $end_date ): array {
		$table = $this->get_table_name();

		$existing = $this->wpdb->get_col(
			$this->wpdb->prepare(
				"SELECT stat_date FROM `{$table}` WHERE stat_date BETWEEN %s AND %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$start_date,
				$end_date
			)
		);

		$all_dates = [];
		$current   = new \DateTime( $start_date );
		$end       = new \DateTime( $end_date );

		while ( $current <= $end ) {
			$all_dates[] = $current->format( 'Y-m-d' );
			$current->modify( '+1 day' );
		}

		return array_values( array_diff( $all_dates, $existing ) );
	}
}
