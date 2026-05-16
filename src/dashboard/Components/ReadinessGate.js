/**
 * ReadinessGate - blocks the dashboard until data prerequisites are met.
 *
 * Renders a pulse-styled setup card that walks the user through WooCommerce
 * activation, schema checks, and the initial snapshot backfill. Once the
 * system reports `ready: true`, children render unchanged.
 */
import React from 'react';
import { motion } from 'framer-motion';
import { __, sprintf } from '@wordpress/i18n';
import {
	AlertCircle,
	CheckCircle2,
	Database,
	RefreshCw,
} from 'lucide-react';
import {
	useReadiness,
	useTriggerBackfill,
	useTriggerSnapshot,
} from '@DashboardApp/hooks/useReadiness';
import { Progress } from '@Components/ui/progress';

const EASE = [ 0.16, 1, 0.3, 1 ];

export function ReadinessGate( { children } ) {
	const { data, isLoading, error } = useReadiness();
	const triggerSnapshot = useTriggerSnapshot();
	const triggerBackfill = useTriggerBackfill();

	if ( isLoading ) {
		return <LoadingState />;
	}

	if ( error ) {
		return <ErrorState />;
	}

	if ( data?.ready ) {
		return children;
	}

	return (
		<SetupCard
			data={ data }
			triggerSnapshot={ triggerSnapshot }
			triggerBackfill={ triggerBackfill }
		/>
	);
}

function LoadingState() {
	return (
		<div className="flex min-h-[400px] items-center justify-center">
			<div className="space-y-3 text-center">
				<RefreshCw className="mx-auto h-8 w-8 animate-spin text-muted-foreground" />
				<p className="text-sm text-muted-foreground">
					{ __( 'Checking data readiness…', 'matriq-store-analytics' ) }
				</p>
			</div>
		</div>
	);
}

function ErrorState() {
	return (
		<motion.div
			initial={ { opacity: 0, y: 12 } }
			animate={ { opacity: 1, y: 0 } }
			transition={ { duration: 0.5, ease: EASE } }
			className="mx-auto mt-12 max-w-lg rounded-2xl border border-solid border-border bg-card p-8 text-center shadow-sm"
		>
			<div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-destructive/10 text-destructive ring-1 ring-destructive/20">
				<AlertCircle className="h-6 w-6" />
			</div>
			<p className="mt-4 text-base font-medium text-foreground">
				{ __( 'Could not check system status', 'matriq-store-analytics' ) }
			</p>
			<p className="mt-1 text-sm text-muted-foreground">
				{ __( 'Refresh the page to try again.', 'matriq-store-analytics' ) }
			</p>
		</motion.div>
	);
}

function SetupCard( { data, triggerSnapshot, triggerBackfill } ) {
	const showAnalyzeAction =
		data?.plugin_tables_exist &&
		! data?.dashboard_ready &&
		data?.orders_exist;
	const showBackfillProgress = ! data?.backfill_complete && data?.has_data;

	return (
		<motion.div
			initial={ { opacity: 0, y: 12 } }
			animate={ { opacity: 1, y: 0 } }
			transition={ { duration: 0.6, ease: EASE } }
			className="mx-auto mt-10 max-w-xl"
		>
			<div className="relative overflow-hidden rounded-3xl border border-solid border-border bg-gradient-card p-8 shadow-md md:p-10">
				<div className="pointer-events-none absolute -right-20 -top-32 h-64 w-64 rounded-full bg-pulse/10 blur-3xl" />

				<div className="relative">
					<div className="mb-3 inline-flex items-center gap-2 rounded-full border border-solid border-border bg-surface/70 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-muted-foreground">
						<span className="h-1.5 w-1.5 rounded-full bg-pulse" />
						{ __( 'Setup', 'matriq-store-analytics' ) }
					</div>

					<h2 className="m-0 flex items-center gap-3 font-display text-3xl leading-tight text-ink md:text-4xl">
						<span className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-ink text-pulse shadow-md">
							<Database className="h-5 w-5" strokeWidth={ 2.25 } />
						</span>
						{ __( 'Setting up Matriq Store Analytics', 'matriq-store-analytics' ) }
					</h2>

					<p className="mt-3 max-w-md text-sm text-muted-foreground">
						{ __(
							'We\'ll run through your store data once before the dashboard unlocks. This usually takes a few seconds.',
							'matriq-store-analytics'
						) }
					</p>

					<ul className="mt-6 space-y-3 p-0">
						<CheckItem
							label={ __( 'WooCommerce active', 'matriq-store-analytics' ) }
							checked={ data?.woocommerce_active }
						/>
						<CheckItem
							label={ __( 'Analytics tables available', 'matriq-store-analytics' ) }
							checked={ data?.analytics_tables_exist }
						/>
						<CheckItem
							label={ __( 'Orders found', 'matriq-store-analytics' ) }
							checked={ data?.orders_exist }
						/>
						<CheckItem
							label={ __( 'Plugin tables created', 'matriq-store-analytics' ) }
							checked={ data?.plugin_tables_exist }
						/>
						<CheckItem
							label={ __( 'Snapshot data available', 'matriq-store-analytics' ) }
							checked={ data?.dashboard_ready }
						/>
					</ul>

					{ showAnalyzeAction && (
						<div className="mt-6 space-y-3">
							<p className="m-0 text-sm text-muted-foreground">
								{ __(
									'Your store has orders ready to analyze.',
									'matriq-store-analytics'
								) }
							</p>
							<button
								type="button"
								onClick={ () =>
									triggerSnapshot.mutate( { days: 14 } )
								}
								disabled={ triggerSnapshot.isPending }
								className="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-full border-0 bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm transition-all hover:shadow-md disabled:cursor-not-allowed disabled:opacity-60 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse"
							>
								{ triggerSnapshot.isPending ? (
									<>
										<RefreshCw className="h-4 w-4 animate-spin" />
										{ __( 'Analyzing your recent sales…', 'matriq-store-analytics' ) }
									</>
								) : (
									__( 'Analyze your store', 'matriq-store-analytics' )
								) }
							</button>
						</div>
					) }

					{ showBackfillProgress && (
						<div className="mt-6 space-y-3">
							<div className="flex items-center justify-between text-xs text-muted-foreground">
								<span>
									{ __( 'Backfilling historical data…', 'matriq-store-analytics' ) }
								</span>
								<span className="font-mono">
									{ sprintf(
										/* translators: %d: number of days */
										__( '%d days', 'matriq-store-analytics' ),
										data?.snapshot_count || 0
									) }
								</span>
							</div>
							<Progress
								value={
									data?.snapshot_count > 0
										? Math.min( ( data.snapshot_count / 365 ) * 100, 95 )
										: 5
								}
							/>
							<button
								type="button"
								onClick={ () => triggerBackfill.mutate() }
								disabled={ triggerBackfill.isPending }
								className="inline-flex cursor-pointer items-center gap-2 rounded-full border border-solid border-border bg-surface px-4 py-1.5 text-xs font-medium text-foreground transition-all hover:border-border/80 hover:bg-surface-elevated disabled:cursor-not-allowed disabled:opacity-60 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse"
							>
								{ triggerBackfill.isPending
									? __( 'Processing…', 'matriq-store-analytics' )
									: __( 'Speed up backfill', 'matriq-store-analytics' ) }
							</button>
						</div>
					) }
				</div>
			</div>
		</motion.div>
	);
}

function CheckItem( { label, checked } ) {
	return (
		<li className="flex items-center gap-2.5 text-sm">
			{ checked ? (
				<CheckCircle2 className="h-4 w-4 shrink-0 text-success" />
			) : (
				<span className="h-4 w-4 shrink-0 rounded-full border-2 border-solid border-muted-foreground/30" />
			) }
			<span
				className={
					checked
						? 'text-foreground'
						: 'text-muted-foreground'
				}
			>
				{ label }
			</span>
		</li>
	);
}
