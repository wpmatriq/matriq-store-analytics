/**
 * ImpactPage (free) - Phase 6.2.
 *
 * Honest data-foundation stats: days of trustworthy data, order edits
 * caught and repaired, campaigns tracked, morning briefings delivered,
 * and the most recent data refresh. Closes with a calm upgrade card
 * that frames the Pro story as "revenue attributed, not just measured."
 *
 * When the Pro plugin is active, its registerTab() registers a richer
 * Impact component that the App.js PageRouter prefers; this free page
 * is the fallback.
 */
import React from 'react';
import { motion } from 'framer-motion';
import { __ } from '@wordpress/i18n';
import {
	BookOpenCheck,
	Calendar,
	Clock,
	Database,
	Mail,
	RefreshCw,
	Sparkles,
	Tag,
	Wrench,
} from 'lucide-react';
import { PageHeader } from '@Components/pulse/PageHeader';
import { useImpactSummary } from '@DashboardApp/hooks/useImpact';

const EASE = [ 0.16, 1, 0.3, 1 ];

// Placeholder product URL. Hooked through `matriq_msa_pro_product_url` so
// the marketing team can override at the PHP layer when the page exists.
const PRO_PRODUCT_URL = 'https://matriq.in/';

export default function ImpactPage() {
	const { data, isLoading, isError, refetch } = useImpactSummary();

	return (
		<motion.div
			initial={ { opacity: 0 } }
			animate={ { opacity: 1 } }
			transition={ { duration: 0.4, ease: EASE } }
			className="space-y-6"
		>
			<PageHeader
				eyebrow={ __( 'Impact', 'matriq-store-analytics' ) }
				title={ __( 'Your data foundation, day after day.', 'matriq-store-analytics' ) }
				subtitle={ __(
					'Matriq Store Analytics keeps your numbers trustworthy: clean snapshots, repaired drift, briefings on a steady cadence. The richer "what it earned" story unlocks with Store Copilot.',
					'matriq-store-analytics'
				) }
			/>

			{ isError ? (
				<ErrorCard onRetry={ refetch } />
			) : isLoading || ! data ? (
				<ImpactSkeleton />
			) : (
				<StatsGrid data={ data } />
			) }

			<UpgradeCard />
		</motion.div>
	);
}

function StatsGrid( { data } ) {
	const cards = [
		{
			icon: Database,
			label: __( 'Days of trustworthy data', 'matriq-store-analytics' ),
			value: data.days_of_data,
			hint: data.oldest_date
				? `${ __( 'Earliest snapshot', 'matriq-store-analytics' ) }: ${ data.oldest_date }`
				: __( 'Snapshots build daily once orders flow.', 'matriq-store-analytics' ),
		},
		{
			icon: Wrench,
			label: __( 'Order edits caught & repaired', 'matriq-store-analytics' ),
			value: data.order_edits,
			hint: __( 'Edits, refunds, and status changes that triggered a snapshot rebuild.', 'matriq-store-analytics' ),
		},
		{
			icon: Tag,
			label: __( 'Campaigns tracked', 'matriq-store-analytics' ),
			value: data.campaigns_tracked,
			hint: __( 'Tagged windows that adjust how the diagnosis reads your numbers.', 'matriq-store-analytics' ),
		},
	];

	const extraCards = [
		{
			icon: Mail,
			label: __( 'Morning briefings delivered', 'matriq-store-analytics' ),
			value: data.briefings_sent,
			hint: __( 'Each one a calm summary of what changed and why.', 'matriq-store-analytics' ),
		},
		{
			icon: RefreshCw,
			label: __( 'Latest data refresh', 'matriq-store-analytics' ),
			value: data.last_snapshot_at || '—',
			hint: data.latest_date
				? `${ __( 'Most recent snapshot date', 'matriq-store-analytics' ) }: ${ data.latest_date }`
				: __( 'Run a manual snapshot from Settings to seed this.', 'matriq-store-analytics' ),
		},
		{
			icon: Calendar,
			label: __( 'Yesterday at a glance', 'matriq-store-analytics' ),
			value: data.yesterday?.headline || __( 'Not enough recent data to compare.', 'matriq-store-analytics' ),
			hint: data.yesterday?.date ? data.yesterday.date : '',
			isText: true,
		},
	];

	return (
		<>
			<div className="flex gap-4">
				{ cards.map( ( card ) => (
					<StatCard key={ card.label } { ...card } />
				) ) }
			</div>
			<div className="flex gap-4">
				{ extraCards.map( ( extraCard ) => (
					<StatCard key={ extraCard.label } { ...extraCard } />
				) ) }
			</div>
		</>
	);
}

function StatCard( { icon: Icon, label, value, hint, isText } ) {
	return (
		<div className="flex w-full min-w-0 flex-col gap-2 rounded-2xl border border-solid border-border bg-card p-5 shadow-sm">
			<div className="flex items-center gap-2">
				<Icon className="h-4 w-4 shrink-0 text-muted-foreground" strokeWidth={ 2 } />
				<span className="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
					{ label }
				</span>
			</div>
			{ isText ? (
				<p className="m-0 text-base font-medium leading-snug text-foreground">{ value }</p>
			) : (
				<p className="m-0 font-display text-3xl text-foreground leading-none">{ value }</p>
			) }
			{ hint && (
				<p className="m-0 text-xs text-muted-foreground">{ hint }</p>
			) }
		</div>
	);
}

function UpgradeCard() {
	return (
		<div className="rounded-2xl border border-solid border-pulse/30 bg-pulse/5 p-6 shadow-sm">
			<div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
				<div className="flex items-start gap-3">
					<Sparkles className="mt-0.5 h-5 w-5 shrink-0 text-pulse" strokeWidth={ 2 } />
					<div className="min-w-0">
						<h3 className="m-0 text-sm font-semibold text-foreground">
							{ __( 'Pro shows revenue attributed, not just measured.', 'matriq-store-analytics' ) }
						</h3>
						<p className="m-0 mt-1 text-sm text-muted-foreground">
							{ __(
								'Store Copilot tracks emails clicked and converted, coupons redeemed, and the orders Copilot earned for you, alongside the AI economics behind each number.',
								'matriq-store-analytics'
							) }
						</p>
					</div>
				</div>
				<a
					href={ PRO_PRODUCT_URL }
					target="_blank"
					rel="noopener noreferrer"
					className="inline-flex shrink-0 cursor-pointer items-center gap-1.5 rounded-full border-0 bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground no-underline shadow-sm transition-all hover:bg-primary hover:text-primary-foreground hover:shadow-md focus:text-primary-foreground focus-visible:text-primary-foreground"
				>
					<BookOpenCheck className="h-3.5 w-3.5" />
					{ __( 'Learn more about Store Copilot', 'matriq-store-analytics' ) }
				</a>
			</div>
		</div>
	);
}

function ImpactSkeleton() {
	return (
		<>
			<div className="flex gap-4">
				{ [ 0, 1, 2 ].map( ( i ) => (
					<div key={ i } className="h-32 animate-pulse rounded-2xl bg-muted/30" />
				) ) }
			</div>
			<div className="flex gap-4">
				{ [ 0, 1, 2 ].map( ( i ) => (
					<div key={ i } className="h-32 animate-pulse rounded-2xl bg-muted/30" />
				) ) }
			</div>
		</>
	);
}

function ErrorCard( { onRetry } ) {
	return (
		<div className="rounded-2xl border border-solid border-warning/30 bg-warning/10 p-4 text-sm text-foreground">
			<p className="m-0">{ __( 'Could not load Impact data. Try again.', 'matriq-store-analytics' ) }</p>
			<button
				type="button"
				onClick={ () => onRetry() }
				className="mt-2 inline-flex cursor-pointer items-center gap-1 rounded-full border border-solid border-border bg-card px-3 py-1 text-xs font-medium text-foreground hover:bg-muted"
			>
				<Clock className="h-3 w-3" />
				{ __( 'Retry', 'matriq-store-analytics' ) }
			</button>
		</div>
	);
}
