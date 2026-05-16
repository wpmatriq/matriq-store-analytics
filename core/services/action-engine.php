<?php
/**
 * Action Recommendation Engine.
 *
 * Converts diagnosis results into context-aware, actionable recommendations.
 * Rule-based - no AI. Campaign-aware for tone adjustment.
 *
 * @package Matriq\MSA\Core\Services
 */

namespace Matriq\MSA\Core\Services;

use Matriq\MSA\Core\Database\Campaigns;
use Matriq\MSA\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

/**
 * Deterministic action recommender.
 *
 * Given a diagnosis + optional active campaign, picks one of a curated
 * set of action scenarios (winback, AOV-booster, abandonment-recovery,
 * etc.) and returns the merchant-facing recommendation. No AI calls;
 * the Pro plugin extends this to layer LLM-tailored copy on top.
 */
class ActionEngine {
	use Get_Instance;

	/**
	 * Scenario definitions: condition → recommendation.
	 *
	 * @var array<string, array<string, string>>
	 */
	private $scenarios = [];

	/**
	 * Constructor - register scenarios.
	 */
	public function __construct() {
		$this->register_scenarios();
	}

	/**
	 * Get action recommendation from diagnosis result.
	 *
	 * @param array<string, mixed>                $diagnosis Diagnosis result from DiagnosisEngine.
	 * @param \stdClass|array<string, mixed>|null $campaign  Active campaign (if any).
	 * @return array<string, string> Action recommendation.
	 */
	public function recommend( array $diagnosis, $campaign = null ): array {
		$direction = $diagnosis['direction'] ?? 'stable';
		$factor    = $diagnosis['primary_factor'] ?? 'none';
		$sub_cause = $diagnosis['sub_cause'] ?? '';

		// Stable - no action needed.
		if ( $direction === 'stable' || $factor === 'none' || $factor === 'no_data' || $factor === 'no_revenue' ) {
			$scenario = [
				'scenario'       => 'stable',
				'recommendation' => __( 'No clear issue detected. Monitor the next day before making changes.', 'matriq-store-analytics' ),
				'severity'       => 'info',
			];
		} elseif ( $factor === 'low_sample' ) {
			// Single-order-vs-single-order comparisons: do not surface a confident action.
			$scenario = [
				'scenario'       => 'low_sample',
				'recommendation' => __( 'Comparison ran on too few orders to be reliable. Wait for a few more days of activity before acting on this signal.', 'matriq-store-analytics' ),
				'severity'       => 'info',
			];
		} else {
			$scenario = $this->match_scenario( $direction, $factor, $sub_cause );

			if ( $campaign ) {
				$scenario = $this->apply_campaign_context( $scenario, $campaign, $direction );
			}
		}

		/**
		 * Filter the action recommendation before it is returned.
		 *
		 * Premium extensions hook here to replace generic copy with AI-tailored
		 * next steps grounded in the merchant's actual catalog and customer base.
		 *
		 * @since 0.0.2
		 *
		 * @param array<string, mixed>                $scenario  The matched recommendation scenario.
		 * @param array<string, mixed>                $diagnosis The diagnosis the recommendation reacts to.
		 * @param \stdClass|array<string, mixed>|null $campaign  Active campaign (if any).
		 */
		return apply_filters( 'matriq_msa_action_recommendation', $scenario, $diagnosis, $campaign );
	}

	/**
	 * Match diagnosis to a scenario and return recommendation.
	 *
	 * @param string $direction Direction (growth, decline).
	 * @param string $factor    Primary factor (orders, items, price).
	 * @param string $sub_cause Sub-cause reason string.
	 * @return array<string, string>
	 */
	private function match_scenario( string $direction, string $factor, string $sub_cause ): array {
		// Decline scenarios.
		if ( $direction === 'decline' ) {
			switch ( $factor ) {
				case 'orders':
					if ( strpos( $sub_cause, 'Returning' ) !== false ) {
						return [
							'scenario'       => 'retention_drop',
							'recommendation' => __( 'Fewer repeat customers purchased than usual. This often happens after delivery issues or poor product experience. Review recent customer feedback.', 'matriq-store-analytics' ),
							'severity'       => 'warning',
						];
					}
					if ( strpos( $sub_cause, 'New' ) !== false ) {
						return [
							'scenario'       => 'acquisition_drop',
							'recommendation' => __( 'Fewer new customers are making first purchases. Review advertising campaigns, referral traffic, or tracking setup.', 'matriq-store-analytics' ),
							'severity'       => 'warning',
						];
					}
					return [
						'scenario'       => 'conversion_drop',
						'recommendation' => __( 'Customers are reaching your store but fewer are completing purchase. Check payment gateway logs, shipping costs, or recent checkout changes.', 'matriq-store-analytics' ),
						'severity'       => 'warning',
					];

				case 'items':
					return [
						'scenario'       => 'basket_drop',
						'recommendation' => __( 'Shoppers are adding fewer items to each order. Verify cross-sell widgets and product recommendations visibility.', 'matriq-store-analytics' ),
						'severity'       => 'warning',
					];

				case 'price':
					return [
						'scenario'       => 'price_drop',
						'recommendation' => __( 'Customers are choosing lower-priced items than usual. Review stock levels of premium products or active discounts.', 'matriq-store-analytics' ),
						'severity'       => 'warning',
					];
			}
		}

		// Growth scenarios.
		if ( $direction === 'growth' ) {
			switch ( $factor ) {
				case 'orders':
					return [
						'scenario'       => 'order_growth',
						'recommendation' => __( 'More customers completed purchases. This is a positive trend worth sustaining.', 'matriq-store-analytics' ),
						'severity'       => 'success',
					];

				case 'items':
					return [
						'scenario'       => 'basket_growth',
						'recommendation' => __( 'Customers are adding more items per order. Cross-selling appears to be working well.', 'matriq-store-analytics' ),
						'severity'       => 'success',
					];

				case 'price':
					return [
						'scenario'       => 'price_growth',
						'recommendation' => __( 'Revenue improved from higher-value purchases. Consider highlighting premium products while demand is strong.', 'matriq-store-analytics' ),
						'severity'       => 'success',
					];
			}
		}

		// Fallback.
		return [
			'scenario'       => 'mixed',
			'recommendation' => __( 'Revenue changed due to multiple smaller factors. No single strong cause was detected. Monitor the next day before making changes.', 'matriq-store-analytics' ),
			'severity'       => 'info',
		];
	}

	/**
	 * Adjust recommendation tone when a campaign is active.
	 *
	 * @param array<string, string>               $scenario  Matched scenario.
	 * @param \stdClass|array<string, mixed>|null $campaign  Active campaign.
	 * @param string                              $direction Change direction.
	 * @return array<string, string> Adjusted scenario.
	 */
	private function apply_campaign_context( array $scenario, $campaign, string $direction ): array {
		if ( $campaign === null ) {
			return $scenario;
		}

		$campaign_arr = is_array( $campaign ) ? $campaign : (array) $campaign;
		$goal         = $campaign_arr['goal'] ?? '';

		// Suppression rules: during campaigns, suppress false alarms.
		$suppress_map = [
			Campaigns::GOAL_ORDERS    => [ 'price_drop', 'basket_drop' ],
			Campaigns::GOAL_AOV       => [ 'conversion_drop', 'acquisition_drop' ],
			Campaigns::GOAL_CLEARANCE => [ 'price_drop' ],
			Campaigns::GOAL_LAUNCH    => [ 'conversion_drop', 'basket_drop' ],
		];

		$suppressed = $suppress_map[ $goal ] ?? [];

		if ( in_array( $scenario['scenario'], $suppressed, true ) ) {
			$campaign_name              = esc_html( (string) ( $campaign_arr['name'] ?? '' ) );
			$scenario['recommendation'] = sprintf(
				/* translators: %1$s: campaign name, %2$s: original recommendation */
				__( 'During your campaign "%1$s": This pattern is expected and not a concern. %2$s', 'matriq-store-analytics' ),
				$campaign_name,
				$scenario['recommendation']
			);
			$scenario['severity'] = 'info';
		} elseif ( $direction === 'growth' ) {
			$scenario['recommendation'] = sprintf(
				/* translators: %s: original recommendation */
				__( 'During your campaign: %s', 'matriq-store-analytics' ),
				$scenario['recommendation']
			);
		}

		return $scenario;
	}

	/**
	 * Register all scenario definitions.
	 *
	 * @return void
	 */
	private function register_scenarios(): void {
		// Scenarios are matched dynamically in match_scenario().
		// This method exists as an extension point for future scenario plugins.
		$this->scenarios = apply_filters( 'matriq_msa_action_scenarios', $this->scenarios );
	}
}
