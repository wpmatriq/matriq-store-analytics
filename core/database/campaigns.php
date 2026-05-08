<?php
/**
 * Campaigns Database Model.
 *
 * Stores manual campaign context set by merchants.
 * Affects diagnosis tone/interpretation, NOT the data itself.
 *
 * @package EC_Sales_Pulse\Core\Database
 */

namespace EC_Sales_Pulse\Core\Database;

use EC_Sales_Pulse\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

/**
 * `campaigns` table model. Stores merchant-tagged date windows that adjust
 * the diagnosis tone but never the underlying numbers (a Black Friday sale
 * shouldn't read as an "anomalous spike", for example).
 */
class Campaigns extends Base {
	use Get_Instance;

	/**
	 * Table name without prefix.
	 *
	 * @var string
	 */
	protected $table_name = 'campaigns';

	/**
	 * Valid campaign goals.
	 */
	const GOAL_ORDERS    = 'orders';
	const GOAL_AOV       = 'aov';
	const GOAL_CLEARANCE = 'clearance';
	const GOAL_LAUNCH    = 'launch';

	/**
	 * Get the CREATE TABLE SQL.
	 *
	 * @return string
	 */
	public function get_schema(): string {
		$table   = $this->get_table_name();
		$charset = $this->get_charset_collate();

		return "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(150) NOT NULL,
			goal VARCHAR(20) NOT NULL DEFAULT 'orders',
			start_date DATE NOT NULL,
			end_date DATE DEFAULT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY start_date_idx (start_date),
			KEY end_date_idx (end_date)
		) {$charset};";
	}

	/**
	 * Get valid campaign goals.
	 *
	 * @return array<string, string>
	 */
	public static function get_valid_goals(): array {
		return [
			self::GOAL_ORDERS    => __( 'Increase Orders', 'sales-pulse' ),
			self::GOAL_AOV       => __( 'Increase AOV', 'sales-pulse' ),
			self::GOAL_CLEARANCE => __( 'Clearance / Liquidation', 'sales-pulse' ),
			self::GOAL_LAUNCH    => __( 'New Product Launch', 'sales-pulse' ),
		];
	}

	/**
	 * Get the currently active campaign (if any).
	 *
	 * @return \stdClass|null Campaign object or null.
	 */
	public function get_active() {
		$table = $this->get_table_name();
		$today = current_time( 'Y-m-d' );

		return $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM `{$table}`
				WHERE start_date <= %s
				AND (end_date IS NULL OR end_date >= %s)
				ORDER BY id DESC
				LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$today,
				$today
			)
		);
	}

	/**
	 * Check if a campaign is active for a specific date.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return \stdClass|null Active campaign or null.
	 */
	public function get_active_for_date( string $date ) {
		$table = $this->get_table_name();

		return $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM `{$table}`
				WHERE start_date <= %s
				AND (end_date IS NULL OR end_date >= %s)
				ORDER BY id DESC
				LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$date,
				$date
			)
		);
	}

	/**
	 * Create a new campaign.
	 *
	 * @param string      $name      Campaign name.
	 * @param string      $goal      Campaign goal (orders, aov, clearance, launch).
	 * @param string      $start_date Start date (Y-m-d).
	 * @param string|null $end_date   End date (Y-m-d) or null for ongoing.
	 * @return int|false Campaign ID or false.
	 */
	public function create( string $name, string $goal, string $start_date, ?string $end_date = null ) {
		$valid_goals = array_keys( self::get_valid_goals() );
		if ( ! in_array( $goal, $valid_goals, true ) ) {
			return false;
		}

		return $this->insert(
			[
				'name'       => sanitize_text_field( $name ),
				'goal'       => $goal,
				'start_date' => $start_date,
				'end_date'   => $end_date,
				'created_at' => current_time( 'mysql' ),
			] 
		);
	}

	/**
	 * End a campaign by setting its end date to today.
	 *
	 * @param int $campaign_id Campaign ID.
	 * @return int|false Rows affected or false.
	 */
	public function end_campaign( int $campaign_id ) {
		return $this->update(
			[ 'end_date' => current_time( 'Y-m-d' ) ],
			[ 'id' => $campaign_id ]
		);
	}

	/**
	 * Get all campaigns, ordered by most recent first.
	 *
	 * @param int $limit Max campaigns to return.
	 * @return array<int, \stdClass>
	 */
	public function get_all( int $limit = 50 ): array {
		return $this->all( 'created_at', 'DESC', $limit );
	}
}
