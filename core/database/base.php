<?php
/**
 * Abstract Base Database Model.
 *
 * All database table models extend this class.
 * Provides common CRUD operations, table name resolution, and schema management.
 *
 * @package EC_Sales_Pulse\Core\Database
 */

namespace EC_Sales_Pulse\Core\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Shared base for every Sales Pulse / Store Copilot DB table model.
 *
 * Concrete subclasses declare a table name and prefix; this class supplies
 * the wpdb connection, charset_collate, and standard insert/update/delete
 * helpers so subclasses focus on schema and domain queries.
 */
abstract class Base {
	/**
	 * Table name without prefix.
	 *
	 * @var string
	 */
	protected $table_name = '';

	/**
	 * Table prefix.
	 *
	 * @var string
	 */
	protected $prefix = 'salespulse_';

	/**
	 * Primary key column.
	 *
	 * @var string
	 */
	protected $primary_key = 'id';

	/**
	 * WordPress database instance.
	 *
	 * @var \wpdb
	 */
	protected $wpdb;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	/**
	 * Get the full table name with WordPress prefix.
	 *
	 * @return string
	 */
	public function get_table_name(): string {
		return $this->wpdb->prefix . $this->prefix . $this->table_name;
	}

	/**
	 * Check if the table exists in the database.
	 *
	 * @return bool
	 */
	public function table_exists(): bool {
		$table = $this->get_table_name();
		return $this->wpdb->get_var( $this->wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
	}

	/**
	 * Get the CREATE TABLE SQL for this model.
	 * Must be implemented by child classes.
	 *
	 * @return string SQL CREATE TABLE statement compatible with dbDelta().
	 */
	abstract public function get_schema(): string;

	/**
	 * Insert a row into the table.
	 *
	 * @param array<string, mixed> $data   Column => value pairs.
	 * @param array<string>        $format Optional format array (%s, %d, %f).
	 * @return int|false Insert ID on success, false on failure.
	 */
	public function insert( array $data, array $format = [] ) {
		$result = $this->wpdb->insert( $this->get_table_name(), $data, empty( $format ) ? null : $format );
		return $result !== false ? $this->wpdb->insert_id : false;
	}

	/**
	 * Update rows matching conditions.
	 *
	 * @param array<string, mixed> $data         Column => value pairs to update.
	 * @param array<string, mixed> $where        Column => value pairs for WHERE clause.
	 * @param array<string>        $format       Optional format for data.
	 * @param array<string>        $where_format Optional format for where.
	 * @return int|false Number of rows updated, or false on error.
	 */
	public function update( array $data, array $where, array $format = [], array $where_format = [] ) {
		return $this->wpdb->update(
			$this->get_table_name(),
			$data,
			$where,
			empty( $format ) ? null : $format,
			empty( $where_format ) ? null : $where_format
		);
	}

	/**
	 * Insert or update a row (REPLACE INTO).
	 *
	 * @param array<string, mixed> $data   Column => value pairs.
	 * @param array<string>        $format Optional format array.
	 * @return int|false Rows affected or false on error.
	 */
	public function replace( array $data, array $format = [] ) {
		return $this->wpdb->replace( $this->get_table_name(), $data, empty( $format ) ? null : $format );
	}

	/**
	 * Delete rows matching conditions.
	 *
	 * @param array<string, mixed> $where        Column => value pairs for WHERE clause.
	 * @param array<string>        $where_format Optional format for where.
	 * @return int|false Number of rows deleted, or false on error.
	 */
	public function delete( array $where, array $where_format = [] ) {
		return $this->wpdb->delete( $this->get_table_name(), $where, empty( $where_format ) ? null : $where_format );
	}

	/**
	 * Get a single row by primary key.
	 *
	 * @param mixed $id Primary key value.
	 * @return \stdClass|null Row object or null.
	 */
	public function find( $id ) {
		$table = $this->get_table_name();
		$key   = $this->primary_key;

		$row = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM `{$table}` WHERE `{$key}` = %s LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$id
			)
		);

		return $row instanceof \stdClass ? $row : null;
	}

	/**
	 * Get all rows, optionally ordered.
	 *
	 * @param string $order_by Column to order by.
	 * @param string $order    ASC or DESC.
	 * @param int    $limit    Max rows to return. 0 = unlimited.
	 * @return array<int, \stdClass>
	 */
	public function all( string $order_by = '', string $order = 'ASC', int $limit = 0 ): array {
		$table = $this->get_table_name();
		$sql   = "SELECT * FROM `{$table}`";

		if ( $order_by ) {
			$order = strtoupper( $order ) === 'DESC' ? 'DESC' : 'ASC';
			$sql  .= " ORDER BY `{$order_by}` {$order}";
		}

		if ( $limit > 0 ) {
			$sql .= $this->wpdb->prepare( ' LIMIT %d', $limit );
		}

		$rows = $this->wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return is_array( $rows ) ? $rows : [];
	}

	/**
	 * Count rows, optionally with conditions.
	 *
	 * @param array<string, mixed> $where Optional WHERE conditions.
	 * @return int
	 */
	public function count( array $where = [] ): int {
		$table = $this->get_table_name();
		$sql   = "SELECT COUNT(*) FROM `{$table}`";

		if ( ! empty( $where ) ) {
			$conditions = [];
			$values     = [];
			foreach ( $where as $col => $val ) {
				$conditions[] = "`{$col}` = %s";
				$values[]     = $val;
			}
			$sql .= ' WHERE ' . implode( ' AND ', $conditions );
			$sql  = $this->wpdb->prepare( $sql, ...$values ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		return (int) $this->wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Truncate the table.
	 *
	 * @return bool
	 */
	public function truncate(): bool {
		$table = $this->get_table_name();
		return $this->wpdb->query( "TRUNCATE TABLE `{$table}`" ) !== false; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Get the charset collate for table creation.
	 *
	 * @return string
	 */
	protected function get_charset_collate(): string {
		return $this->wpdb->get_charset_collate();
	}
}
