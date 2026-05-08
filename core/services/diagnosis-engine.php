<?php
/**
 * Diagnosis Engine Service.
 *
 * Deterministic revenue decomposition engine.
 * Compares two periods and explains WHY revenue changed using midpoint decomposition math.
 * No AI - pure math and rules.
 *
 * Formula: Revenue = Orders x Items/Order x Avg Item Price
 *
 * @package EC_Sales_Pulse\Core\Services
 */

namespace EC_Sales_Pulse\Core\Services;

use EC_Sales_Pulse\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

/**
 * Deterministic revenue diagnosis.
 *
 * Compares a current period to a prior period, decomposes the revenue
 * delta into orders and AOV factors, weighs them against a sensitivity
 * threshold, and returns a "What changed and why" verdict. The output is
 * filterable via `salespulse_diagnosis_result` so Pro can layer LLM
 * explanations on top.
 */
class DiagnosisEngine {
	use Get_Instance;

	/**
	 * Minimum revenue change percentage to trigger diagnosis (base; scaled by sensitivity).
	 *
	 * @var float
	 */
	const CHANGE_THRESHOLD = 5.0;

	/**
	 * Absolute floor: anything below this is treated as "no revenue" rather
	 * than a real signal. Used by the new-store / dead-store edge cases.
	 *
	 * @var float
	 */
	const MIN_REVENUE_THRESHOLD = 1.0;

	/**
	 * Below this revenue, comparisons are statistically meaningless even
	 * when both days have orders. STRATEGY.md Section 6: "Suppress strong
	 * diagnosis, mark 'low sample size'." A jump from $7 to $76 is a
	 * one-order-vs-one-order spike, not a trend.
	 *
	 * @var float
	 */
	const LOW_SAMPLE_REVENUE_THRESHOLD = 50.0;

	/**
	 * Below this order count on either side, the diagnosis is downgraded to
	 * "low sample size" regardless of the dollar swing. Three orders is the
	 * minimum where a primary-factor decomposition starts to mean something.
	 *
	 * @var int
	 */
	const MIN_ORDERS_FOR_CONFIDENCE = 3;

	/**
	 * Multipliers applied to CHANGE_THRESHOLD for each sensitivity level.
	 *
	 * Calm    - larger threshold, only flag major shifts.
	 * Balanced - base threshold (5%).
	 * Vigilant - tighter threshold, surface smaller movements.
	 *
	 * @var array<string, float>
	 */
	const SENSITIVITY_MULTIPLIERS = [
		'calm'     => 1.5,
		'balanced' => 1.0,
		'vigilant' => 0.6,
	];

	/**
	 * Active change threshold. Updated per call via {@see diagnose()}.
	 *
	 * @var float
	 */
	private $change_threshold = self::CHANGE_THRESHOLD;

	/**
	 * Run full diagnosis comparing current vs previous period.
	 *
	 * @param \stdClass|array<string, mixed>|null $current     Current period metrics (from daily_stats or aggregated).
	 * @param \stdClass|array<string, mixed>|null $previous    Previous period metrics.
	 * @param string                              $sensitivity Diagnosis sensitivity (calm|balanced|vigilant).
	 * @return array<string, mixed> Diagnosis result.
	 */
	public function diagnose( $current, $previous, string $sensitivity = 'balanced' ): array {
		$diagnosis = $this->compute_diagnosis( $current, $previous, $sensitivity );

		/**
		 * Filter the deterministic diagnosis result before it is returned.
		 *
		 * Premium extensions (e.g. Store Copilot) hook here to enrich the
		 * diagnosis with AI-generated explanations or additional fields.
		 *
		 * @since x.x.x
		 *
		 * @param array<string, mixed> $diagnosis   The computed diagnosis result.
		 * @param mixed                $current     Current period metrics.
		 * @param mixed                $previous    Previous period metrics.
		 * @param string               $sensitivity Diagnosis sensitivity (calm|balanced|vigilant).
		 */
		return apply_filters( 'salespulse_diagnosis_result', $diagnosis, $current, $previous, $sensitivity );
	}

	/**
	 * Compute the deterministic diagnosis. Pure math, no filters - the public
	 * `diagnose()` wraps this with `salespulse_diagnosis_result`.
	 *
	 * @param mixed  $current     Current period metrics.
	 * @param mixed  $previous    Previous period metrics.
	 * @param string $sensitivity Diagnosis sensitivity.
	 * @return array<string, mixed>
	 */
	private function compute_diagnosis( $current, $previous, string $sensitivity ): array {
		$multiplier             = self::SENSITIVITY_MULTIPLIERS[ $sensitivity ] ?? 1.0;
		$this->change_threshold = self::CHANGE_THRESHOLD * $multiplier;

		$current  = $this->normalize_metrics( $current );
		$previous = $this->normalize_metrics( $previous );

		// Edge case: no data.
		if ( ! $current || ! $previous ) {
			return $this->build_result( 0, 0, 'stable', 'no_data', '', [], 0, 'Insufficient data for diagnosis.' );
		}

		// Edge case: zero previous revenue.
		if ( $previous['revenue'] < self::MIN_REVENUE_THRESHOLD && $current['revenue'] < self::MIN_REVENUE_THRESHOLD ) {
			return $this->build_result( 0, 0, 'stable', 'no_revenue', '', [], 0, 'No revenue recorded in either period.' );
		}

		// Edge case: zero previous (new store coming alive).
		if ( $previous['revenue'] < self::MIN_REVENUE_THRESHOLD ) {
			return $this->build_result(
				100,
				$current['revenue'],
				'growth',
				'new_revenue',
				'First revenue recorded.',
				[],
				1.0,
				'Revenue started flowing in this period.'
			);
		}

		// Edge case: zero current (store went dead).
		if ( $current['revenue'] < self::MIN_REVENUE_THRESHOLD && $previous['revenue'] > 0 ) {
			return $this->build_result(
				-100,
				-$previous['revenue'],
				'decline',
				'orders',
				'No orders were placed.',
				[ 'orders' => -$previous['revenue'] ],
				1.0,
				'Revenue dropped to zero because no orders were completed.'
			);
		}

		// Calculate revenue change.
		$revenue_change_pct    = $this->pct_change( $current['revenue'], $previous['revenue'] );
		$revenue_change_amount = $current['revenue'] - $previous['revenue'];
		$direction             = $this->get_direction( $revenue_change_pct );

		// Edge case: low sample size. Either day below the meaningful-revenue
		// floor OR fewer than the minimum order count means a single
		// transaction can swing the headline by hundreds of percent. Report
		// the truth in numbers but suppress the strong "Clear cause" framing.
		$low_sample = $previous['revenue'] < self::LOW_SAMPLE_REVENUE_THRESHOLD
			|| $current['revenue'] < self::LOW_SAMPLE_REVENUE_THRESHOLD
			|| (int) ( $previous['orders'] ?? 0 ) < self::MIN_ORDERS_FOR_CONFIDENCE
			|| (int) ( $current['orders'] ?? 0 ) < self::MIN_ORDERS_FOR_CONFIDENCE;

		if ( $low_sample ) {
			$headline = $direction === 'stable'
				? 'Revenue moved on a small base, no clear signal yet.'
				: sprintf(
					'Revenue %s on a small base, treat as low signal.',
					$direction === 'growth' ? 'rose' : 'dropped'
				);

			return $this->build_result(
				round( $revenue_change_pct, 1 ),
				round( $revenue_change_amount, 2 ),
				$direction,
				'low_sample',
				'Too few orders for a reliable comparison.',
				[],
				0.0,
				$headline
			);
		}

		// If change is below threshold, report stable.
		if ( abs( $revenue_change_pct ) < $this->change_threshold ) {
			return $this->build_result(
				round( $revenue_change_pct, 1 ),
				round( $revenue_change_amount, 2 ),
				'stable',
				'none',
				'',
				[],
				0,
				'Revenue remained stable compared to the previous period.'
			);
		}

		// Run decomposition.
		$decomposition = $this->decompose( $current, $previous );
		$primary       = $this->determine_primary_factor( $decomposition, $revenue_change_amount );
		$sub_cause     = $this->determine_sub_cause( $primary['factor'], $current, $previous );
		$confidence    = $this->calculate_confidence( $primary['impact'], $revenue_change_amount );
		$headline      = $this->compose_headline( $direction, $revenue_change_pct, $primary, $sub_cause, $confidence );

		return $this->build_result(
			round( $revenue_change_pct, 1 ),
			round( $revenue_change_amount, 2 ),
			$direction,
			$primary['factor'],
			$sub_cause['reason'] ?? '',
			[
				'orders' => round( $decomposition['orders_impact'], 2 ),
				'items'  => round( $decomposition['items_impact'], 2 ),
				'price'  => round( $decomposition['price_impact'], 2 ),
			],
			round( $confidence, 2 ),
			$headline
		);
	}

	/**
	 * Midpoint revenue decomposition.
	 *
	 * Uses midpoint method to fairly attribute revenue change to:
	 * - Order volume change
	 * - Items per order change
	 * - Average item price change
	 *
	 * @param array<string, float> $current  Current metrics.
	 * @param array<string, float> $previous Previous metrics.
	 * @return array<string, float> Impact breakdown.
	 */
	private function decompose( array $current, array $previous ): array {
		$o1 = $current['orders'];
		$o0 = $previous['orders'];
		$i1 = $current['items_per_order'];
		$i0 = $previous['items_per_order'];
		$p1 = $current['avg_item_price'];
		$p0 = $previous['avg_item_price'];

		$aov1 = $current['avg_order_value'];
		$aov0 = $previous['avg_order_value'];

		// Level 1: Orders vs AOV (midpoint decomposition).
		$orders_impact = ( $o1 - $o0 ) * ( ( $aov1 + $aov0 ) / 2 );
		$aov_impact    = ( $aov1 - $aov0 ) * ( ( $o1 + $o0 ) / 2 );

		// Level 2: Break AOV into items-per-order vs price.
		$items_impact = ( $i1 - $i0 ) * ( ( $p1 + $p0 ) / 2 ) * ( ( $o1 + $o0 ) / 2 );
		$price_impact = ( $p1 - $p0 ) * ( ( $i1 + $i0 ) / 2 ) * ( ( $o1 + $o0 ) / 2 );

		return [
			'orders_impact' => $orders_impact,
			'aov_impact'    => $aov_impact,
			'items_impact'  => $items_impact,
			'price_impact'  => $price_impact,
		];
	}

	/**
	 * Determine the primary factor driving revenue change.
	 *
	 * @param array<string, float> $decomposition Impact breakdown.
	 * @param float                $total_change  Total revenue change amount.
	 * @return array<string, mixed> Primary factor info.
	 */
	private function determine_primary_factor( array $decomposition, float $total_change ): array {
		$factors = [
			'orders' => abs( $decomposition['orders_impact'] ),
			'items'  => abs( $decomposition['items_impact'] ),
			'price'  => abs( $decomposition['price_impact'] ),
		];

		arsort( $factors );
		$primary_key = array_key_first( $factors );

		$impact_map = [
			'orders' => $decomposition['orders_impact'],
			'items'  => $decomposition['items_impact'],
			'price'  => $decomposition['price_impact'],
		];

		return [
			'factor' => $primary_key,
			'impact' => $impact_map[ $primary_key ],
			'abs'    => $factors[ $primary_key ],
		];
	}

	/**
	 * Determine sub-cause within the primary factor.
	 *
	 * @param string               $primary_factor Primary factor (orders, items, price).
	 * @param array<string, float> $current        Current metrics.
	 * @param array<string, float> $previous       Previous metrics.
	 * @return array<string, mixed> Sub-cause info.
	 */
	private function determine_sub_cause( string $primary_factor, array $current, array $previous ): array {
		if ( $primary_factor === 'orders' ) {
			$new_change = $this->pct_change( $current['new_customers'], $previous['new_customers'] );
			$ret_change = $this->pct_change( $current['returning_customers'], $previous['returning_customers'] );

			if ( abs( $ret_change ) > abs( $new_change ) ) {
				return [
					'type'   => 'returning_customers',
					'change' => round( $ret_change, 1 ),
					'reason' => sprintf( 'Returning customers changed %.1f%%', $ret_change ),
				];
			}

			return [
				'type'   => 'new_customers',
				'change' => round( $new_change, 1 ),
				'reason' => sprintf( 'New customers changed %.1f%%', $new_change ),
			];
		}

		if ( $primary_factor === 'items' ) {
			$change = $this->pct_change( $current['items_per_order'], $previous['items_per_order'] );
			return [
				'type'   => 'items_per_order',
				'change' => round( $change, 1 ),
				'reason' => sprintf( 'Items per order changed %.1f%%', $change ),
			];
		}

		// price.
		$change = $this->pct_change( $current['avg_item_price'], $previous['avg_item_price'] );
		return [
			'type'   => 'avg_item_price',
			'change' => round( $change, 1 ),
			'reason' => sprintf( 'Average item price changed %.1f%%', $change ),
		];
	}

	/**
	 * Calculate confidence score.
	 *
	 * @param float $primary_impact Largest single factor impact.
	 * @param float $total_change   Total revenue change.
	 * @return float Confidence between 0 and 1.
	 */
	private function calculate_confidence( float $primary_impact, float $total_change ): float {
		if ( abs( $total_change ) < 0.01 ) {
			return 0;
		}

		return min( 1.0, abs( $primary_impact ) / abs( $total_change ) );
	}

	/**
	 * Get human-readable confidence label.
	 *
	 * @param float $confidence Confidence score (0-1).
	 * @return string
	 */
	public function get_confidence_label( float $confidence ): string {
		if ( $confidence >= 0.6 ) {
			return __( 'Clear cause identified.', 'sales-pulse' );
		}
		if ( $confidence >= 0.4 ) {
			return __( 'Likely cause detected.', 'sales-pulse' );
		}
		return __( 'No strong single cause.', 'sales-pulse' );
	}

	/**
	 * Compose the headline sentence.
	 *
	 * @param string               $direction  Direction (growth, decline, stable).
	 * @param float                $pct_change Percentage change.
	 * @param array<string, mixed> $primary    Primary factor info.
	 * @param array<string, mixed> $sub_cause  Sub-cause info.
	 * @param float                $confidence Confidence score.
	 * @return string
	 */
	private function compose_headline( string $direction, float $pct_change, array $primary, array $sub_cause, float $confidence ): string {
		$abs_pct = abs( round( $pct_change, 1 ) );

		if ( $confidence < 0.4 ) {
			$verb = $direction === 'growth' ? 'increased' : 'decreased';
			return sprintf(
				/* translators: %1$s: verb, %2$s: percentage */
				__( 'Revenue %1$s %2$s%% due to multiple smaller changes in customer behavior.', 'sales-pulse' ),
				$verb,
				$abs_pct
			);
		}

		$factor = $primary['factor'];
		$cause  = '';

		switch ( $factor ) {
			case 'orders':
				$cause = $direction === 'decline'
					? __( 'fewer completed orders', 'sales-pulse' )
					: __( 'more completed orders', 'sales-pulse' );
				break;
			case 'items':
				$cause = $direction === 'decline'
					? __( 'customers buying fewer items per order', 'sales-pulse' )
					: __( 'larger baskets per order', 'sales-pulse' );
				break;
			case 'price':
				$cause = $direction === 'decline'
					? __( 'lower-priced products being purchased', 'sales-pulse' )
					: __( 'higher-value products being purchased', 'sales-pulse' );
				break;
		}

		$verb    = $direction === 'growth' ? 'increased' : 'decreased';
		$quality = $confidence >= 0.6 ? __( 'mainly due to', 'sales-pulse' ) : __( 'likely due to', 'sales-pulse' );

		return sprintf(
			/* translators: %1$s: verb, %2$s: percentage, %3$s: quality, %4$s: cause */
			__( 'Revenue %1$s %2$s%% %3$s %4$s.', 'sales-pulse' ),
			$verb,
			$abs_pct,
			$quality,
			$cause
		);
	}

	/**
	 * Normalize metrics from object to array with floats.
	 *
	 * @param \stdClass|array<string, mixed>|null $metrics Raw metrics.
	 * @return array<string, float>|null Normalized array or null.
	 */
	private function normalize_metrics( $metrics ) {
		if ( ! $metrics ) {
			return null;
		}

		$data = is_array( $metrics ) ? $metrics : (array) $metrics;

		return [
			'revenue'             => (float) ( $data['revenue'] ?? 0 ),
			'orders'              => (float) ( $data['orders'] ?? 0 ),
			'items_sold'          => (float) ( $data['items_sold'] ?? 0 ),
			'avg_order_value'     => (float) ( $data['avg_order_value'] ?? 0 ),
			'items_per_order'     => (float) ( $data['items_per_order'] ?? 0 ),
			'avg_item_price'      => (float) ( $data['avg_item_price'] ?? 0 ),
			'new_customers'       => (float) ( $data['new_customers'] ?? 0 ),
			'returning_customers' => (float) ( $data['returning_customers'] ?? 0 ),
			'discount_total'      => (float) ( $data['discount_total'] ?? 0 ),
			'refund_total'        => (float) ( $data['refund_total'] ?? 0 ),
		];
	}

	/**
	 * Calculate percentage change between two values.
	 *
	 * @param float $new New value.
	 * @param float $old Old value.
	 * @return float Percentage change.
	 */
	private function pct_change( float $new, float $old ): float {
		if ( abs( $old ) < 0.01 ) {
			return $new > 0 ? 100 : 0;
		}
		return ( ( $new - $old ) / $old ) * 100;
	}

	/**
	 * Get direction label from percentage change.
	 *
	 * @param float $pct_change Percentage change.
	 * @return string growth, decline, or stable.
	 */
	private function get_direction( float $pct_change ): string {
		if ( $pct_change > $this->change_threshold ) {
			return 'growth';
		}
		if ( $pct_change < -$this->change_threshold ) {
			return 'decline';
		}
		return 'stable';
	}

	/**
	 * Build a standardized diagnosis result array.
	 *
	 * @param float                $pct_change       Revenue change percentage.
	 * @param float                $amount_change    Revenue change amount.
	 * @param string               $direction        growth, decline, stable.
	 * @param string               $primary_factor   Primary cause factor.
	 * @param string               $sub_cause_reason Sub-cause explanation.
	 * @param array<string, float> $impact_breakdown Impact by factor.
	 * @param float                $confidence       Confidence score 0-1.
	 * @param string               $headline         Human-readable headline.
	 * @return array<string, mixed>
	 */
	private function build_result(
		float $pct_change,
		float $amount_change,
		string $direction,
		string $primary_factor,
		string $sub_cause_reason,
		array $impact_breakdown,
		float $confidence,
		string $headline
	): array {
		return [
			'revenue_change_percent' => $pct_change,
			'revenue_change_amount'  => $amount_change,
			'direction'              => $direction,
			'primary_factor'         => $primary_factor,
			'sub_cause'              => $sub_cause_reason,
			'impact_breakdown'       => $impact_breakdown,
			'confidence'             => $confidence,
			'confidence_label'       => $this->get_confidence_label( $confidence ),
			'headline'               => $headline,
		];
	}
}
