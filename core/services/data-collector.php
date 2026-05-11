<?php
/**
 * Data Collector Service.
 *
 * Reads from WooCommerce Analytics tables to collect daily store metrics.
 * This is the ONLY class that touches WC data tables - all other services
 * read from our snapshot tables.
 *
 * @package EC_Sales_Pulse\Core\Services
 */

namespace EC_Sales_Pulse\Core\Services;

use EC_Sales_Pulse\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

/**
 * Reads aggregate metrics from WooCommerce analytics tables for a given
 * date and returns them in the shape `daily_stats` expects. Wraps the WC
 * query so the SnapshotBuilder stays free of WC-table SQL.
 */
class DataCollector {
	use Get_Instance;

	/**
	 * WordPress database instance.
	 *
	 * @var \wpdb
	 */
	private $wpdb;

	/**
	 * Order statuses considered as valid revenue.
	 *
	 * @var array<string>
	 */
	private $valid_statuses = [ 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-refunded' ];

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	/**
	 * Check if WooCommerce Analytics tables exist and are usable.
	 *
	 * @return bool
	 */
	public function are_analytics_tables_available(): bool {
		$table = $this->wpdb->prefix . 'wc_order_stats';
		// WC core table; SHOW TABLES is a metadata query that can't be cached.
		return $this->wpdb->get_var( $this->wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table; // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
	}

	/**
	 * Get the oldest order date in WooCommerce.
	 *
	 * @return string|null Date in Y-m-d format, or null if no orders.
	 */
	public function get_oldest_order_date() {
		$table = $this->wpdb->prefix . 'wc_order_stats';

		// WC core table; nightly snapshot is the only reader.
		$date = $this->wpdb->get_var(
			"SELECT MIN(DATE(date_created)) FROM `{$table}` WHERE parent_id = 0" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		);

		return $date ? $date : null;
	}

	/**
	 * Get the total number of valid orders in WooCommerce.
	 *
	 * @return int
	 */
	public function get_total_order_count(): int {
		$table    = $this->wpdb->prefix . 'wc_order_stats';
		$statuses = $this->get_status_placeholders();

		// Placeholders are %s repeated to match valid_statuses count; sniff cannot resolve dynamic placeholder counts.
		return (int) $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM `{$table}` WHERE parent_id = 0 AND status IN ({$statuses})", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
				...$this->valid_statuses
			)
		);
	}

	/**
	 * Collect all metrics for a single day.
	 * This is the primary method called by SnapshotBuilder.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return array<string, mixed> Structured metrics array ready for daily_stats table.
	 */
	public function collect_day_metrics( string $date ): array {
		$start = $date . ' 00:00:00';
		$end   = $date . ' 23:59:59';

		$revenue_data  = $this->get_revenue_metrics( $start, $end );
		$customer_data = $this->get_customer_metrics( $start, $end );
		$refund_data   = $this->get_refund_metrics( $start, $end );

		$orders    = (int) ( $revenue_data->orders ?? 0 );
		$revenue   = (float) ( $revenue_data->revenue ?? 0 );
		$items     = (int) ( $revenue_data->items_sold ?? 0 );
		$discounts = (float) ( $revenue_data->discount_total ?? 0 );
		$refunds   = (float) ( $refund_data->refund_total ?? 0 );

		return [
			'stat_date'           => $date,
			'revenue'             => $revenue,
			'orders'              => $orders,
			'items_sold'          => $items,
			'avg_order_value'     => $orders > 0 ? round( $revenue / $orders, 2 ) : 0,
			'items_per_order'     => $orders > 0 ? round( $items / $orders, 2 ) : 0,
			'avg_item_price'      => $items > 0 ? round( $revenue / $items, 2 ) : 0,
			'new_customers'       => (int) ( $customer_data->new_customers ?? 0 ),
			'returning_customers' => (int) ( $customer_data->returning_customers ?? 0 ),
			'discount_total'      => $discounts,
			'refund_total'        => $refunds,
		];
	}

	/**
	 * Get core revenue metrics (orders, revenue, items, discounts) for a period.
	 *
	 * @param string $start Start datetime.
	 * @param string $end   End datetime.
	 * @return object
	 */
	private function get_revenue_metrics( string $start, string $end ) {
		$table    = $this->wpdb->prefix . 'wc_order_stats';
		$statuses = $this->get_status_placeholders();

		// Placeholders are %s repeated to match valid_statuses count; sniff cannot resolve dynamic placeholder counts.
		$result = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT
					COUNT(DISTINCT order_id) as orders,
					COALESCE(SUM(net_total), 0) as revenue,
					COALESCE(SUM(num_items_sold), 0) as items_sold,
					COALESCE(SUM(total_sales - net_total), 0) as discount_total
				FROM `{$table}`
				WHERE date_created BETWEEN %s AND %s
				AND parent_id = 0
				AND status IN ({$statuses})", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$start,
				$end,
				...$this->valid_statuses
			)
		);

		return $result ? $result : (object) [
			'orders'         => 0,
			'revenue'        => 0,
			'items_sold'     => 0,
			'discount_total' => 0,
		];
	}

	/**
	 * Get new vs returning customer counts for a period.
	 *
	 * @param string $start Start datetime.
	 * @param string $end   End datetime.
	 * @return object
	 */
	private function get_customer_metrics( string $start, string $end ) {
		$order_table = $this->wpdb->prefix . 'wc_order_stats';
		$statuses    = $this->get_status_placeholders();

		// A customer is "new" if this order's date matches their first order date.
		// Placeholders are %s repeated to match valid_statuses count; sniff cannot resolve dynamic placeholder counts.
		$result = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT
					COALESCE(SUM(CASE WHEN os.returning_customer = 0 THEN 1 ELSE 0 END), 0) as new_customers,
					COALESCE(SUM(CASE WHEN os.returning_customer = 1 THEN 1 ELSE 0 END), 0) as returning_customers
				FROM `{$order_table}` os
				WHERE os.date_created BETWEEN %s AND %s
				AND os.parent_id = 0
				AND os.status IN ({$statuses})", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$start,
				$end,
				...$this->valid_statuses
			)
		);

		return $result ? $result : (object) [
			'new_customers'       => 0,
			'returning_customers' => 0,
		];
	}

	/**
	 * Get refund totals for a period.
	 * Refunds are child orders with parent_id > 0 and negative net_total.
	 *
	 * @param string $start Start datetime.
	 * @param string $end   End datetime.
	 * @return object
	 */
	private function get_refund_metrics( string $start, string $end ) {
		$table = $this->wpdb->prefix . 'wc_order_stats';

		// WC core table; nightly snapshot is the only reader.
		$result = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT
					COALESCE(ABS(SUM(net_total)), 0) as refund_total
				FROM `{$table}`
				WHERE date_created BETWEEN %s AND %s
				AND parent_id > 0", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$start,
				$end
			)
		);

		return $result ? $result : (object) [ 'refund_total' => 0 ];
	}

	/**
	 * Generate SQL placeholders for valid statuses.
	 *
	 * @return string Comma-separated %s placeholders.
	 */
	private function get_status_placeholders(): string {
		return implode( ', ', array_fill( 0, count( $this->valid_statuses ), '%s' ) );
	}
}
