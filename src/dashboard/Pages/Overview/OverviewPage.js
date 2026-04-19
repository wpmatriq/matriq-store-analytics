/**
 * Overview Page — Morning Briefing.
 *
 * The primary dashboard view:
 *   1. PageHeader with period SegmentedControl.
 *   2. BriefingHero — big headline diagnosis.
 *   3. MetricCards — 4 KPI cards with sparklines.
 *   4. What changed / Suggested action side-by-side InsightCards.
 *   5. RevenueTrend — 7-day area chart.
 */
import React, { useMemo, useState } from 'react';
import { motion } from 'framer-motion';
import { __ } from '@wordpress/i18n';
import { AlertTriangle, BarChart3, Sparkles } from 'lucide-react';
import { useOverview, useTrend } from '@DashboardApp/hooks/useOverview';
import { PageHeader } from '@Components/pulse/PageHeader';
import { SegmentedControl } from '@Components/pulse/SegmentedControl';
import { BriefingHero } from '@Components/pulse/BriefingHero';
import { InsightCard } from '@Components/pulse/InsightCard';
import { RevenueTrend } from '@Components/pulse/RevenueTrend';
import { statusVariant } from '@DashboardApp/Utils/statusMap';
import { MetricCards } from './MetricCards';
import { ImpactBreakdownContent } from './ImpactBreakdownContent';
import { ActionCardContent } from './ActionCardContent';

const EASE = [ 0.16, 1, 0.3, 1 ];

const PERIOD_OPTIONS = [
	{ value: 'daily', label: __( 'Yesterday', 'sales-pulse' ) },
	{ value: 'weekly', label: __( 'Last 7 days', 'sales-pulse' ) },
	{ value: 'monthly', label: __( 'Last 30 days', 'sales-pulse' ) },
];

const PERIOD_SUBTITLES = {
	daily: __(
		'A daily diagnosis of your store performance — what changed, why it matters, and what to do next.',
		'sales-pulse'
	),
	weekly: __(
		'The last 7 days compared to the previous 7 — what shifted across the week.',
		'sales-pulse'
	),
	monthly: __(
		'The last 30 days compared to the previous 30 — your rolling monthly pulse.',
		'sales-pulse'
	),
};

export default function OverviewPage() {
	const [ period, setPeriod ] = useState( 'daily' );
	const { data, isLoading, error, refetch } = useOverview( period );
	const { data: trendData } = useTrend( 7 );

	const diagnosis = data?.diagnosis;
	const recommendation = data?.recommendation;
	const metricCards = data?.metric_cards;
	const campaign = data?.campaign;
	const currency = window.wc_sma_admin_data?.currency;

	const variant = useMemo( () => statusVariant( diagnosis?.severity ), [ diagnosis?.severity ] );
	const hasImpact =
		diagnosis?.impact_breakdown &&
		Object.values( diagnosis.impact_breakdown ).some( ( v ) => Math.abs( Number( v ) || 0 ) > 0.01 );
	const hasAction = !! recommendation?.recommendation;

	return (
		<motion.div
			initial={ { opacity: 0 } }
			animate={ { opacity: 1 } }
			transition={ { duration: 0.4, ease: EASE } }
			className="space-y-8"
		>
			<PageHeader
				eyebrow={ __( 'Morning briefing', 'sales-pulse' ) }
				title={ __( 'Yesterday at a glance.', 'sales-pulse' ) }
				subtitle={ PERIOD_SUBTITLES[ period ] }
				actions={
					<SegmentedControl
						value={ period }
						options={ PERIOD_OPTIONS }
						onChange={ setPeriod }
						ariaLabel={ __( 'Comparison period', 'sales-pulse' ) }
					/>
				}
			/>

			{ error ? (
				<InsightCard
					icon={ <AlertTriangle className="h-4 w-4" /> }
					title={ __( 'Could not load overview', 'sales-pulse' ) }
					accent="warning"
				>
					<div className="flex flex-col items-start gap-4">
						<p className="m-0 text-sm text-muted-foreground">
							{ __(
								'We hit an error fetching your diagnosis. Retry, or refresh the page.',
								'sales-pulse'
							) }
						</p>
						<button
							type="button"
							onClick={ () => refetch() }
							className="inline-flex cursor-pointer items-center gap-2 rounded-full border-0 bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition-all hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse"
						>
							{ __( 'Retry', 'sales-pulse' ) }
						</button>
					</div>
				</InsightCard>
			) : (
				<>
					<BriefingHero
						changePct={ Number( diagnosis?.revenue_change_percent ) || 0 }
						statusVariant={ variant }
						campaignName={ campaign?.name }
						diagnosis={ diagnosis?.headline }
						cause={ diagnosis?.confidence_label }
					/>

					<MetricCards cards={ metricCards } trend={ trendData?.trend } />

					<div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
						<InsightCard
							icon={ <BarChart3 className="h-4 w-4" /> }
							title={ __( 'What changed', 'sales-pulse' ) }
							emptyTitle={ __( 'No significant changes detected', 'sales-pulse' ) }
							emptyDescription={ __(
								'Revenue factors remained stable across the comparison window.',
								'sales-pulse'
							) }
							delay={ 0.1 }
						>
							{ hasImpact && ! isLoading ? (
								<ImpactBreakdownContent breakdown={ diagnosis.impact_breakdown } />
							) : null }
						</InsightCard>

						<InsightCard
							icon={ <Sparkles className="h-4 w-4" /> }
							title={ __( 'Suggested action', 'sales-pulse' ) }
							accent="success"
							emptyTitle={ __( 'No action needed right now', 'sales-pulse' ) }
							emptyDescription={ __(
								"Store performance is stable. We'll surface recommendations as soon as something shifts.",
								'sales-pulse'
							) }
							delay={ 0.15 }
						>
							{ hasAction && ! isLoading ? (
								<ActionCardContent recommendation={ recommendation } />
							) : null }
						</InsightCard>
					</div>

					<RevenueTrend trend={ trendData?.trend } currency={ currency } />
				</>
			) }
		</motion.div>
	);
}
