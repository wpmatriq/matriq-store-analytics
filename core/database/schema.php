<?php
/**
 * Schema Manager.
 *
 * Handles table creation, migrations, and version tracking.
 * Uses WordPress dbDelta() for safe schema updates.
 *
 * @package EC_Sales_Pulse\Core\Database
 */

namespace EC_Sales_Pulse\Core\Database;

use EC_Sales_Pulse\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin schema manager. Owns DB_VERSION + the list of table model classes,
 * runs `dbDelta()` for each one on install/upgrade, and stamps the version
 * in `system_state`.
 */
class Schema {
	use Get_Instance;

	/**
	 * Current database schema version.
	 *
	 * @var int
	 */
	const DB_VERSION = 2;

	/**
	 * All table model class names.
	 *
	 * @var array<string>
	 */
	private $table_models = [
		DailyStats::class,
		DirtyDates::class,
		Campaigns::class,
		SystemState::class,
		DigestHistory::class,
	];

	/**
	 * Create or update all plugin tables.
	 *
	 * @return void
	 */
	public function install(): void {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		foreach ( $this->table_models as $model_class ) {
			$model = $model_class::get_instance();
			dbDelta( $model->get_schema() );
		}

		// Store the current DB version.
		$state = SystemState::get_instance();
		$state->set( SystemState::KEY_DB_VERSION, (string) self::DB_VERSION );
		$state->set( SystemState::KEY_PLUGIN_VERSION, EC_SALES_PULSE_VER );
	}

	/**
	 * Check if schema needs update and run migrations.
	 *
	 * @return void
	 */
	public function maybe_upgrade(): void {
		$state           = SystemState::get_instance();
		$current_version = (int) $state->get( SystemState::KEY_DB_VERSION, '0' );

		if ( $current_version < self::DB_VERSION ) {
			$this->install();
		}
	}

	/**
	 * Check if all required tables exist.
	 *
	 * @return bool
	 */
	public function tables_exist(): bool {
		foreach ( $this->table_models as $model_class ) {
			$model = $model_class::get_instance();
			if ( ! $model->table_exists() ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Drop all plugin tables.
	 * Only call during uninstall, NOT deactivation.
	 *
	 * @return void
	 */
	public function uninstall(): void {
		global $wpdb;

		foreach ( $this->table_models as $model_class ) {
			$model = $model_class::get_instance();
			$table = $model->get_table_name();
			$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange -- intentional uninstall path; table name is plugin-controlled.
		}
	}

	/**
	 * Get status of all tables (for data readiness check).
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_tables_status(): array {
		$status = [];

		foreach ( $this->table_models as $model_class ) {
			$model            = $model_class::get_instance();
			$exists           = $model->table_exists();
			$class            = ( new \ReflectionClass( $model_class ) )->getShortName();
			$status[ $class ] = [
				'table'  => $model->get_table_name(),
				'exists' => $exists,
				'rows'   => $exists ? $model->count() : 0,
			];
		}

		return $status;
	}
}
