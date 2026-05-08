<?php
/**
 * Digest Mailer.
 *
 * Sends the morning briefing email after the nightly snapshot completes.
 * Combines yesterday + last 7 days + last 30 days insights into a single
 * email. Honors the email_enabled / email_address settings, persists a
 * once-per-day idempotency token, and surfaces failures via SettingsController.
 *
 * @package EC_Sales_Pulse\Core\Services
 */

namespace EC_Sales_Pulse\Core\Services;

use EC_Sales_Pulse\Core\Controllers\SettingsController;
use EC_Sales_Pulse\Core\Database\Campaigns;
use EC_Sales_Pulse\Core\Database\DailyStats;
use EC_Sales_Pulse\Core\Database\DigestHistory;
use EC_Sales_Pulse\Core\Database\SystemState;
use EC_Sales_Pulse\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

/**
 * Builds and sends the morning-briefing digest email. Composes the
 * payload from yesterday's snapshot, dispatches via WC_Email when
 * available (and falls back to wp_mail), and logs every send into
 * `digest_history` with `sent`/`failed`/`skipped` status.
 */
class DigestMailer {
	use Get_Instance;

	/**
	 * Constructor - listens for the nightly snapshot completion event.
	 */
	public function __construct() {
		add_action( 'salespulse_after_nightly_snapshot', [ $this, 'maybe_send_nightly' ] );
	}

	/**
	 * Decide whether to send today's nightly digest, and dispatch if so.
	 *
	 * Gates: snapshot built, toggle on, valid recipient, not already sent today.
	 *
	 * @param array<string, mixed> $summary Snapshot summary from SnapshotBuilder::run_nightly().
	 */
	public function maybe_send_nightly( $summary ): void {
		$summary = is_array( $summary ) ? $summary : [];

		if ( empty( $summary['yesterday_built'] ) ) {
			return;
		}
		if ( ! SettingsController::get( 'email_enabled' ) ) {
			return;
		}
		if ( ! $this->is_send_allowed_today() ) {
			return;
		}

		$this->send( null, false );
	}

	/**
	 * Send the digest immediately.
	 *
	 * @param string|null $override_recipient Optional recipient to use instead of the stored email_address.
	 * @param bool        $is_test            When true, bypass the once-per-day idempotency guard.
	 * @return array{sent:bool, recipient:string, reason:?string}
	 */
	public function send( ?string $override_recipient = null, bool $is_test = false ): array {
		$recipient = $override_recipient !== null && $override_recipient !== ''
			? $override_recipient
			: (string) SettingsController::get( 'email_address', '' );

		if ( $recipient === '' ) {
			$recipient = (string) get_option( 'admin_email', '' );
		}

		if ( $recipient === '' || ! is_email( $recipient ) ) {
			$reason = __( 'Recipient email is missing or invalid.', 'sales-pulse' );
			$this->record_error( $reason );
			$this->log_history( $recipient, 'failed', $reason, $is_test );
			return [
				'sent'      => false,
				'recipient' => $recipient,
				'reason'    => $reason,
			];
		}

		if ( ! $is_test && ! SettingsController::get( 'email_enabled' ) ) {
			$reason = __( 'Email digest is disabled.', 'sales-pulse' );
			$this->log_history( $recipient, 'skipped', $reason, $is_test );
			return [
				'sent'      => false,
				'recipient' => $recipient,
				'reason'    => $reason,
			];
		}

		$payload = $this->build_payload();
		$subject = $this->compose_subject( $payload );

		$sent = $this->dispatch_via_wc_email( $recipient, $subject, $payload );
		if ( ! $sent ) {
			$sent = $this->dispatch_via_wp_mail( $recipient, $subject, $payload );
		}

		if ( $sent ) {
			if ( ! $is_test ) {
				$this->mark_sent_today();
			}
			$this->record_sent_at();
			$this->clear_error();
			$this->log_history( $recipient, 'sent', null, $is_test );
			return [
				'sent'      => true,
				'recipient' => $recipient,
				'reason'    => null,
			];
		}

		$reason = __( 'Mail server rejected the message. Check your SMTP setup.', 'sales-pulse' );
		$this->record_error( $reason );
		$this->log_history( $recipient, 'failed', $reason, $is_test );
		return [
			'sent'      => false,
			'recipient' => $recipient,
			'reason'    => $reason,
		];
	}

	/**
	 * Build the data payload used by both subject composer and templates.
	 *
	 * @return array<string, mixed>
	 */
	public function build_payload(): array {
		$settings    = SettingsController::get_all();
		$sensitivity = (string) ( $settings['diagnosis_sensitivity'] ?? 'balanced' );

		$daily_stats = DailyStats::get_instance();
		$campaigns   = Campaigns::get_instance();
		$diag        = DiagnosisEngine::get_instance();
		$action      = ActionEngine::get_instance();

		$timezone     = wp_timezone();
		$yesterday    = ( new \DateTime( '-1 day', $timezone ) )->format( 'Y-m-d' );
		$active_camp  = $campaigns->get_active_for_date( $yesterday );
		$campaign_arr = $active_camp ? [
			'id'   => $active_camp->id,
			'name' => $active_camp->name,
			'goal' => $active_camp->goal,
		] : null;

		$daily_current  = $this->daily_metrics( $daily_stats, 0 );
		$daily_previous = $this->daily_metrics( $daily_stats, 1 );

		$weekly_current  = $this->rolling_metrics( $daily_stats, 0, 7 );
		$weekly_previous = $this->rolling_metrics( $daily_stats, 1, 7 );

		$monthly_current  = $this->rolling_metrics( $daily_stats, 0, 30 );
		$monthly_previous = $this->rolling_metrics( $daily_stats, 1, 30 );

		$build_section = function ( $current, $previous, $is_daily ) use ( $diag, $action, $sensitivity, $campaign_arr ) {
			$diagnosis      = $diag->diagnose( $current, $previous, $sensitivity );
			$recommendation = $action->recommend( $diagnosis, $is_daily ? $campaign_arr : null );

			return [
				'diagnosis'      => $diagnosis,
				'recommendation' => $recommendation,
				'metric_cards'   => $this->build_metric_cards( $current, $previous ),
				'current'        => is_array( $current ) ? $current : [],
				'previous'       => is_array( $previous ) ? $previous : [],
			];
		};

		return [
			'meta'    => [
				'date'            => $yesterday,
				'site_name'       => get_bloginfo( 'name' ),
				'currency'        => $settings['currency'] ?? 'USD',
				'currency_symbol' => $settings['currency_symbol'] ?? '$',
				'timezone'        => $settings['timezone'] ?? wp_timezone_string(),
				'campaign'        => $campaign_arr,
				'dashboard_url'   => admin_url( 'admin.php?page=sales-pulse' ),
			],
			'daily'   => $this->run_section( 'daily', $build_section, $daily_current, $daily_previous, true ),
			'weekly'  => $this->run_section( 'weekly', $build_section, $weekly_current, $weekly_previous, false ),
			'monthly' => $this->run_section( 'monthly', $build_section, $monthly_current, $monthly_previous, false ),
		];
	}

	/**
	 * Compose the subject line. Daily-first signal preference; falls back to 7d, then 30d.
	 *
	 * @param array<string, mixed> $payload Output of build_payload().
	 */
	public function compose_subject( array $payload ): string {
		$windows = [
			'daily'   => __( 'yesterday', 'sales-pulse' ),
			'weekly'  => __( 'this week', 'sales-pulse' ),
			'monthly' => __( 'this month', 'sales-pulse' ),
		];

		foreach ( $windows as $key => $when ) {
			$diagnosis = $payload[ $key ]['diagnosis'] ?? [];
			$direction = $diagnosis['direction'] ?? 'stable';

			if ( $direction === 'growth' ) {
				$pct = $this->format_change_pct( $diagnosis );
				/* translators: 1: percent change without sign, 2: window noun. */
				return sprintf( __( 'Sales Pulse: Revenue up %1$s%% %2$s', 'sales-pulse' ), $pct, $when );
			}
			if ( $direction === 'decline' ) {
				$factor = $this->primary_factor_label( $diagnosis );
				/* translators: 1: factor noun (Revenue/Orders/etc.), 2: window noun. */
				return sprintf( __( 'Sales Pulse: %1$s softened %2$s', 'sales-pulse' ), $factor, $when );
			}
		}

		// All three windows are stable - send a calm subject.
		$has_action_for_daily = ! empty( $payload['daily']['recommendation']['recommendation'] );
		if ( $has_action_for_daily ) {
			return __( 'Sales Pulse: Steady morning, one action ready', 'sales-pulse' );
		}

		$date = $payload['meta']['date'] ?? gmdate( 'Y-m-d' );
		/* translators: %s: friendly date like "May 1". */
		return sprintf( __( 'Sales Pulse: Steady morning, no alarms - %s', 'sales-pulse' ), $this->friendly_date( $date ) );
	}

	/**
	 * Append one row to the digest_history table for every send attempt.
	 *
	 * @param string      $recipient  Recipient email (may be empty if invalid).
	 * @param string      $status     'sent' | 'failed' | 'skipped'.
	 * @param string|null $error_text Optional error description.
	 * @param bool        $is_test    True when invoked from the test-send button.
	 */
	private function log_history( string $recipient, string $status, ?string $error_text, bool $is_test ): void {
		DigestHistory::get_instance()->record(
			[
				'sent_at'    => current_time( 'mysql' ),
				'recipient'  => $recipient,
				'status'     => $status,
				'error_text' => $error_text,
				'is_test'    => $is_test ? 1 : 0,
			]
		);
	}

	/**
	 * Run one section build with the period announced via the standard
	 * `salespulse_overview_period_resolved` action so premium extensions
	 * (e.g. Store Copilot) can scope per-window enrichment correctly.
	 *
	 * @param string                                                                               $period      Window name.
	 * @param callable(array<string,mixed>|null,array<string,mixed>|null,bool):array<string,mixed> $builder     The closure built in build_payload().
	 * @param array<string, mixed>|null                                                            $current     Current-period metrics.
	 * @param array<string, mixed>|null                                                            $previous    Previous-period metrics.
	 * @param bool                                                                                 $is_daily    Whether this is the daily-window build.
	 * @return array<string, mixed>
	 */
	private function run_section( string $period, callable $builder, $current, $previous, bool $is_daily ): array {
		do_action( 'salespulse_overview_period_resolved', $period );
		return $builder( $current, $previous, $is_daily );
	}

	/**
	 * Send via WC_Email subclass when WooCommerce is available.
	 *
	 * @param string               $recipient Recipient email address.
	 * @param string               $subject   Subject line.
	 * @param array<string, mixed> $payload   Pre-composed payload from build_payload().
	 *
	 * @return bool True when the WC mailer accepted the message.
	 */
	private function dispatch_via_wc_email( string $recipient, string $subject, array $payload ): bool {
		if ( ! class_exists( '\\WC_Email' ) || ! function_exists( 'WC' ) ) {
			return false;
		}

		$wc = WC();
		if ( ! $wc || ! method_exists( $wc, 'mailer' ) ) {
			return false;
		}

		$mailer = $wc->mailer();
		if ( ! $mailer ) {
			return false;
		}

		$emails = $mailer->get_emails();
		if ( empty( $emails['DigestEmail'] ) || ! is_object( $emails['DigestEmail'] ) ) {
			return false;
		}

		$email = $emails['DigestEmail'];
		if ( ! method_exists( $email, 'trigger_digest' ) ) {
			return false;
		}

		return (bool) $email->trigger_digest( $recipient, $subject, $payload );
	}

	/**
	 * Plain wp_mail fallback when WooCommerce isn't available.
	 *
	 * @param string               $recipient Recipient email address.
	 * @param string               $subject   Subject line.
	 * @param array<string, mixed> $payload   Pre-composed payload from build_payload().
	 *
	 * @return bool True when wp_mail() accepted the message.
	 */
	private function dispatch_via_wp_mail( string $recipient, string $subject, array $payload ): bool {
		$html_template = EC_SALES_PULSE_DIR . 'templates/email/digest-html.php';

		if ( ! is_readable( $html_template ) ) {
			return false;
		}

		$render = static function ( string $path, array $payload ): string {
			// Templates expect `$email->payload` - build a minimal stand-in.
			$email = (object) [ 'payload' => $payload ];
			ob_start();
			include $path;
			$out = ob_get_clean();
			return is_string( $out ) ? $out : '';
		};

		$html = $render( $html_template, $payload );

		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			'From: Sales Pulse <' . get_option( 'admin_email' ) . '>',
		];

		return (bool) wp_mail( $recipient, wp_specialchars_decode( $subject ), $html, $headers );
	}

	/* ---- Idempotency / state helpers -------------------------------------- */

	/**
	 * Has the digest already been sent today (in site timezone)?
	 *
	 * @return bool
	 */
	private function is_send_allowed_today(): bool {
		$today = ( new \DateTime( 'now', wp_timezone() ) )->format( 'Y-m-d' );
		$last  = (string) SystemState::get_instance()->get( SystemState::KEY_LAST_DIGEST_SENT_DATE, '' );
		return $last !== $today;
	}

	/**
	 * Stamp today's date as the last successful digest send.
	 *
	 * @return void
	 */
	private function mark_sent_today(): void {
		$today = ( new \DateTime( 'now', wp_timezone() ) )->format( 'Y-m-d' );
		SystemState::get_instance()->set( SystemState::KEY_LAST_DIGEST_SENT_DATE, $today );
	}

	/**
	 * Stamp the precise timestamp of the last successful send (ISO-8601).
	 *
	 * @return void
	 */
	private function record_sent_at(): void {
		$now = ( new \DateTime( 'now', wp_timezone() ) )->format( \DateTime::ATOM );
		SystemState::get_instance()->set( SystemState::KEY_LAST_DIGEST_SENT_AT, $now );
	}

	/**
	 * Persist the most recent send error so the Settings UI can surface it.
	 *
	 * @param string $reason Human-readable error description.
	 *
	 * @return void
	 */
	private function record_error( string $reason ): void {
		$saved = get_option( SettingsController::OPTION_KEY, [] );
		if ( ! is_array( $saved ) ) {
			$saved = [];
		}
		$saved['last_digest_error'] = sprintf(
			'%s|%s',
			( new \DateTime( 'now', wp_timezone() ) )->format( \DateTime::ATOM ),
			$reason
		);
		update_option( SettingsController::OPTION_KEY, $saved );

		// Mirror to PHP error log only when WP_DEBUG_LOG is on so production
		// hosts don't get noise; the persisted Settings entry is the
		// authoritative surface for the merchant either way.
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, Generic.PHP.ForbiddenFunctions.Found
			error_log( '[Sales Pulse] Digest send failed: ' . $reason );
		}
	}

	/**
	 * Clear any persisted send-error after a successful send.
	 *
	 * @return void
	 */
	private function clear_error(): void {
		$saved = get_option( SettingsController::OPTION_KEY, [] );
		if ( is_array( $saved ) && array_key_exists( 'last_digest_error', $saved ) ) {
			$saved['last_digest_error'] = null;
			update_option( SettingsController::OPTION_KEY, $saved );
		}
	}

	/* ---- Data helpers (mirror Overview controller dispatch logic) --------- */

	/**
	 * Look up the daily snapshot for an offset relative to today.
	 *
	 * @param DailyStats $stats  DailyStats handle.
	 * @param int        $offset 0 = yesterday, 1 = day-before-yesterday.
	 *
	 * @return array<string, mixed>|null Snapshot row as an array, or null when missing.
	 */
	private function daily_metrics( DailyStats $stats, int $offset ) {
		$days_ago = $offset + 1;
		$date     = ( new \DateTime( "-{$days_ago} days", wp_timezone() ) )->format( 'Y-m-d' );
		$row      = $stats->get_by_date( $date );
		return $row ? (array) $row : null;
	}

	/**
	 * Aggregate metrics across a rolling window.
	 *
	 * @param DailyStats $stats  DailyStats handle.
	 * @param int        $offset 0 = current window, 1 = previous window.
	 * @param int        $days   Window length in days.
	 *
	 * @return array<string, mixed>|null Aggregated metrics, or null when no data.
	 */
	private function rolling_metrics( DailyStats $stats, int $offset, int $days ) {
		$shift        = $offset * $days;
		$end_offset   = $shift + 1;
		$start_offset = $shift + $days;

		$end   = ( new \DateTime( "-{$end_offset} days", wp_timezone() ) )->format( 'Y-m-d' );
		$start = ( new \DateTime( "-{$start_offset} days", wp_timezone() ) )->format( 'Y-m-d' );

		$row = $stats->get_aggregated( $start, $end );
		return $row ? (array) $row : null;
	}

	/**
	 * Mirrors Overview::build_metric_cards() so the email matches the dashboard.
	 *
	 * @param array<string, mixed>|null $current  Current-period metrics row.
	 * @param array<string, mixed>|null $previous Prior-period metrics row.
	 *
	 * @return array<int, array<string, mixed>> KPI card definitions ready for templating.
	 */
	private function build_metric_cards( $current, $previous ): array {
		$current  = $current ? (array) $current : [];
		$previous = $previous ? (array) $previous : [];

		$metrics = [
			[
				'key'    => 'revenue',
				'label'  => __( 'Revenue', 'sales-pulse' ),
				'format' => 'currency',
			],
			[
				'key'    => 'orders',
				'label'  => __( 'Orders', 'sales-pulse' ),
				'format' => 'number',
			],
			[
				'key'    => 'avg_order_value',
				'label'  => __( 'Avg Order Value', 'sales-pulse' ),
				'format' => 'currency',
			],
			[
				'key'    => 'items_per_order',
				'label'  => __( 'Items per Order', 'sales-pulse' ),
				'format' => 'decimal',
			],
		];

		$cards = [];
		foreach ( $metrics as $metric ) {
			$curr_val = (float) ( $current[ $metric['key'] ] ?? 0 );
			$prev_val = (float) ( $previous[ $metric['key'] ] ?? 0 );
			$change   = $prev_val > 0 ? round( ( $curr_val - $prev_val ) / $prev_val * 100, 1 ) : 0;

			$cards[] = [
				'key'      => $metric['key'],
				'label'    => $metric['label'],
				'format'   => $metric['format'],
				'current'  => round( $curr_val, 2 ),
				'previous' => round( $prev_val, 2 ),
				'change'   => $change,
			];
		}

		return $cards;
	}

	/* ---- Subject helpers -------------------------------------------------- */

	/**
	 * Format the absolute revenue-change percent for the subject line.
	 *
	 * @param array<string, mixed> $diagnosis Diagnosis result.
	 *
	 * @return string
	 */
	private function format_change_pct( array $diagnosis ): string {
		$pct = (float) ( $diagnosis['revenue_change_percent'] ?? 0 );
		return number_format_i18n( abs( $pct ), abs( $pct ) >= 10 ? 0 : 1 );
	}

	/**
	 * Map a diagnosis primary-factor key to a translated label.
	 *
	 * @param array<string, mixed> $diagnosis Diagnosis result.
	 *
	 * @return string
	 */
	private function primary_factor_label( array $diagnosis ): string {
		$factor = (string) ( $diagnosis['primary_factor'] ?? 'none' );
		switch ( $factor ) {
			case 'orders':
				return __( 'Orders', 'sales-pulse' );
			case 'items':
				return __( 'Items per order', 'sales-pulse' );
			case 'price':
				return __( 'Price per item', 'sales-pulse' );
			default:
				return __( 'Revenue', 'sales-pulse' );
		}
	}

	/**
	 * Format an ISO date as a short "Mon J" string in the site timezone.
	 *
	 * @param string $iso_date Y-m-d date string.
	 *
	 * @return string
	 */
	private function friendly_date( string $iso_date ): string {
		try {
			$d         = new \DateTime( $iso_date, wp_timezone() );
			$formatted = wp_date( 'M j', $d->getTimestamp() );
			return is_string( $formatted ) ? $formatted : $iso_date;
		} catch ( \Exception $e ) {
			return $iso_date;
		}
	}
}
