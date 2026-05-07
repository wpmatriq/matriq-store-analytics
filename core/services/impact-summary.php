<?php
/**
 * Free Impact Summary (Phase 6.2).
 *
 * Read-only data-foundation stats that prove the free plugin is doing its
 * job: how many days of clean data we hold, how many order edits we've
 * repaired, how many campaigns are tagged, how many morning briefings
 * have shipped, and when the data last refreshed.
 *
 * Pro replaces this surface with attribution-driven numbers (revenue
 * recovered, coupons redeemed, AI ROI). The free version is the
 * "trustworthy data foundation" story; Pro is the "what that foundation
 * earned" story.
 *
 * @package EC_Sales_Pulse\Core\Services
 */

namespace EC_Sales_Pulse\Core\Services;

use EC_Sales_Pulse\Core\Database\Campaigns;
use EC_Sales_Pulse\Core\Database\DailyStats;
use EC_Sales_Pulse\Core\Database\DigestHistory;
use EC_Sales_Pulse\Core\Database\DirtyDates;
use EC_Sales_Pulse\Core\Database\SystemState;
use EC_Sales_Pulse\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

class ImpactSummary {
	use Get_Instance;

	/**
	 * Build the keyed stat payload consumed by the free Impact tab.
	 *
	 * @return array<string, mixed>
	 */
	public function build(): array {
		$daily_stats     = DailyStats::get_instance();
		$dirty_dates     = DirtyDates::get_instance();
		$campaigns       = Campaigns::get_instance();
		$digest_history  = DigestHistory::get_instance();
		$system_state    = SystemState::get_instance();

		$days_of_data       = (int) $daily_stats->count();
		$order_edits        = (int) $dirty_dates->count_resolved();
		$campaigns_tracked  = (int) $campaigns->count();
		$briefings_sent     = (int) $digest_history->count_total( 'sent' );
		$last_snapshot_at   = (string) $system_state->get( SystemState::KEY_LAST_SNAPSHOT_DATE, '' );

		return [
			'days_of_data'      => $days_of_data,
			'order_edits'       => $order_edits,
			'campaigns_tracked' => $campaigns_tracked,
			'briefings_sent'    => $briefings_sent,
			'last_snapshot_at'  => $last_snapshot_at !== '' ? $last_snapshot_at : null,
			'oldest_date'       => $daily_stats->get_oldest_date(),
			'latest_date'       => $daily_stats->get_latest_date(),
			'yesterday'         => $this->yesterday_highlight( $daily_stats ),
		];
	}

	/**
	 * One-line headline from yesterday's snapshot. Plain comparison vs the
	 * day before. Returns null when there isn't enough data.
	 *
	 * @param DailyStats $daily_stats Model handle.
	 *
	 * @return array{date: string, headline: string}|null
	 */
	private function yesterday_highlight( DailyStats $daily_stats ): ?array {
		try {
			$tz             = wp_timezone();
			$yesterday      = ( new \DateTime( '-1 day', $tz ) )->format( 'Y-m-d' );
			$day_before     = ( new \DateTime( '-2 days', $tz ) )->format( 'Y-m-d' );
			$yesterday_row  = $daily_stats->get_by_date( $yesterday );
			$day_before_row = $daily_stats->get_by_date( $day_before );
		} catch ( \Throwable $e ) {
			return null;
		}

		if ( ! $yesterday_row ) {
			return null;
		}

		$yesterday_revenue  = (float) ( $yesterday_row->revenue ?? 0 );
		$day_before_revenue = $day_before_row ? (float) ( $day_before_row->revenue ?? 0 ) : 0.0;

		if ( $yesterday_revenue <= 0 && $day_before_revenue <= 0 ) {
			return null;
		}

		if ( $day_before_revenue <= 0 ) {
			return [
				'date'     => $yesterday,
				/* translators: %s: yesterday revenue */
				'headline' => sprintf( __( 'Yesterday earned %s. No comparable activity the day before.', 'sales-pulse' ), $this->format_currency( $yesterday_revenue ) ),
			];
		}

		$delta_pct = ( ( $yesterday_revenue - $day_before_revenue ) / $day_before_revenue ) * 100;
		$direction = $delta_pct >= 0 ? __( 'up', 'sales-pulse' ) : __( 'down', 'sales-pulse' );

		return [
			'date'     => $yesterday,
			/* translators: 1: yesterday revenue, 2: direction (up/down), 3: percent change */
			'headline' => sprintf(
				__( 'Yesterday earned %1$s, %2$s %3$s%% vs the day before.', 'sales-pulse' ),
				$this->format_currency( $yesterday_revenue ),
				$direction,
				number_format( abs( $delta_pct ), 1 )
			),
		];
	}

	/**
	 * Lightweight money formatter matching the WC convention. Avoids loading
	 * the full WC currency stack inside REST callbacks.
	 *
	 * @param float $value Amount.
	 *
	 * @return string
	 */
	private function format_currency( float $value ): string {
		if ( function_exists( 'wc_price' ) ) {
			$html = (string) wc_price( $value );
			return wp_strip_all_tags( $html );
		}
		return '$' . number_format( $value, 2 );
	}
}
