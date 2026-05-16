<?php
/**
 * Schema Manager.
 *
 * Handles table creation, migrations, and version tracking.
 * Uses WordPress dbDelta() for safe schema updates.
 *
 * @package Matriq\MSA\Core\Database
 */

namespace Matriq\MSA\Core\Database;

use Matriq\MSA\Inc\Traits\Get_Instance;

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
	 * Bumps:
	 *   v3 - drops `digest_history` (replaced by two `system_state` counters
	 *        and a last-error key) and `dirty_dates` (replaced by an
	 *        option-backed set + repaired counter). Both tables existed
	 *        purely to power one all-time count tile each on the Free
	 *        Impact dashboard, which counters do at 1/10th the storage.
	 */
	public const DB_VERSION = 3;

	/**
	 * All table model class names.
	 *
	 * Each entry is a class extending Base AND using the Get_Instance trait,
	 * which is why we can call ::get_instance() on them. PHPStan can't
	 * intersect those constraints, so we leave it as a string list.
	 *
	 * @var array<int, string>
	 */
	private $table_models = [
		DailyStats::class,
		Campaigns::class,
		SystemState::class,
	];

	/**
	 * Tables removed in v3. Listed here so maybe_upgrade() can DROP them on
	 * upgrade after seeding the counters that replaced them.
	 *
	 * @var array<int, string>
	 */
	private $dropped_in_v3 = [
		'matriq_msa_digest_history',
		'matriq_msa_dirty_dates',
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
		$state->set( SystemState::KEY_PLUGIN_VERSION, MATRIQ_MSA_VER );
	}

	/**
	 * Check if schema needs update and run migrations.
	 *
	 * Pre-install hooks run version-targeted migrations (e.g. seeding the
	 * v3 digest-sent counter from the soon-to-be-dropped table) before
	 * dbDelta + the table drops.
	 *
	 * @return void
	 */
	public function maybe_upgrade(): void {
		$state           = SystemState::get_instance();
		$current_version = (int) $state->get( SystemState::KEY_DB_VERSION, '0' );

		if ( $current_version >= self::DB_VERSION ) {
			return;
		}

		// Seed the v3 counters from the legacy tables BEFORE dbDelta.
		// Idempotent: only seeds when the counter doesn't exist yet, so
		// re-running the upgrade is safe. The version-gate is structured
		// for forward-compat: when DB_VERSION climbs past 3, the same
		// hook still fires for v0..v2 upgrades.
		if ( $current_version < 3 ) { // @phpstan-ignore smaller.alwaysTrue
			$this->seed_v3_counters();
		}

		$this->install();

		if ( $current_version < 3 ) { // @phpstan-ignore smaller.alwaysTrue
			$this->drop_obsolete_tables();
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
			$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- intentional uninstall path; table name is plugin-controlled.
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

	/**
	 * V3 migration helper: seed the `digest_sent_total` and `repaired_total`
	 * `system_state` counters from the legacy tables. Idempotent - only
	 * writes when the key doesn't already exist.
	 */
	private function seed_v3_counters(): void {
		global $wpdb;
		$state = SystemState::get_instance();

		if ( $state->get( SystemState::KEY_DIGEST_SENT_TOTAL, null ) === null ) {
			$digest_table = $wpdb->prefix . 'matriq_msa_digest_history';
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $digest_table ) );
			$count  = 0;
			if ( $exists === $digest_table ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$digest_table}` WHERE status = 'sent'" );
			}
			$state->set( SystemState::KEY_DIGEST_SENT_TOTAL, (string) $count );
		}

		// `repaired_total` is the per-DirtyDates count of resolved rows -
		// see DirtyDates::count_resolved(). Replaces the table read.
		if ( $state->get( SystemState::KEY_REPAIRED_TOTAL, null ) === null ) {
			$dirty_table = $wpdb->prefix . 'matriq_msa_dirty_dates';
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $dirty_table ) );
			$count  = 0;
			if ( $exists === $dirty_table ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$dirty_table}` WHERE resolved_at IS NOT NULL" );
			}
			$state->set( SystemState::KEY_REPAIRED_TOTAL, (string) $count );
		}
	}

	/**
	 * V3 migration helper: drop every table that the v3 cleanup retired.
	 * Idempotent - DROP TABLE IF EXISTS makes re-runs safe.
	 */
	private function drop_obsolete_tables(): void {
		global $wpdb;

		foreach ( $this->dropped_in_v3 as $unprefixed ) {
			$table = $wpdb->prefix . $unprefixed;
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" );
		}

		// Drop dead indexes (no SELECT consumer; pay nightly maintenance cost
		// for zero read benefit). dbDelta won't remove them on its own; we
		// have to do it explicitly. Wrapped in IF EXISTS guard via prior
		// existence check so re-running is safe.
		$daily = $wpdb->prefix . 'matriq_msa_daily_stats';
		foreach ( [ 'revenue_idx', 'orders_idx' ] as $idx ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$exists = $wpdb->get_var( $wpdb->prepare( "SHOW INDEX FROM `{$daily}` WHERE Key_name = %s", $idx ) );
			if ( $exists ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$wpdb->query( "ALTER TABLE `{$daily}` DROP INDEX `{$idx}`" );
			}
		}
	}
}
