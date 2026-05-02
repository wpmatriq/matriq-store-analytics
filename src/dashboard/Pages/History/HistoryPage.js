/**
 * History Page - 34 days of pulse readings.
 *
 * Paginated log of daily revenue diagnoses with change deltas and status flags.
 * Each row renders a trend-icon tile, date, diagnosis headline, change %,
 * revenue, orders, and a status pill. Export CSV is a placeholder until the
 * backend supports exports (see plan resolved decision #5).
 */
import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { __, sprintf } from '@wordpress/i18n';
import { AlertTriangle, Download } from 'lucide-react';
import { useHistory } from '@DashboardApp/hooks/useHistory';
import { PageHeader } from '@Components/pulse/PageHeader';
import { InsightCard } from '@Components/pulse/InsightCard';
import {
	Tooltip,
	TooltipContent,
	TooltipProvider,
	TooltipTrigger,
} from '@Components/ui/tooltip';
import { HistoryRow } from './HistoryRow';
import { HistoryPager } from './HistoryPager';

const EASE = [ 0.16, 1, 0.3, 1 ];

export default function HistoryPage() {
	const [ page, setPage ] = useState( 1 );
	const { data, isLoading, error, refetch } = useHistory( page );

	const total = data?.total || 0;
	const totalPages = data?.total_pages || 0;
	const items = data?.items || [];

	return (
		<motion.div
			initial={ { opacity: 0 } }
			animate={ { opacity: 1 } }
			transition={ { duration: 0.4, ease: EASE } }
			className="space-y-6"
		>
			<PageHeader
				eyebrow={ __( 'Diagnosis log', 'sales-pulse' ) }
				title={
					total > 0
						? sprintf(
							/* translators: %d: total days */
							__( '%d days of pulse readings.', 'sales-pulse' ),
							total
						  )
						: __( 'Your diagnosis log.', 'sales-pulse' )
				}
				subtitle={ __(
					'A complete record of daily revenue diagnoses, change deltas, and status flags.',
					'sales-pulse'
				) }
				actions={ <ExportCsvButton /> }
			/>

			{ error ? (
				<InsightCard
					icon={ <AlertTriangle className="h-4 w-4" /> }
					title={ __( 'Could not load history', 'sales-pulse' ) }
					accent="warning"
				>
					<div className="flex flex-col items-start gap-4">
						<p className="m-0 text-sm text-muted-foreground">
							{ __(
								'We hit an error fetching the diagnosis log. Retry, or refresh the page.',
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
				<div className="overflow-hidden rounded-2xl border border-solid border-border bg-card shadow-sm">
					<div className="overflow-x-auto">
						<table className="w-full border-collapse">
							<thead>
								<tr className="bg-muted/30 text-[10px] font-semibold uppercase tracking-[0.14em] text-muted-foreground">
									<th className="w-14 px-4 py-3 text-left">
										{ __( 'Trend', 'sales-pulse' ) }
									</th>
									<th className="w-28 px-4 py-3 text-left">
										{ __( 'Date', 'sales-pulse' ) }
									</th>
									<th className="px-4 py-3 text-left">
										{ __( 'Diagnosis', 'sales-pulse' ) }
									</th>
									<th className="w-24 px-4 py-3 text-right">
										{ __( 'Change', 'sales-pulse' ) }
									</th>
									<th className="w-28 px-4 py-3 text-right">
										{ __( 'Revenue', 'sales-pulse' ) }
									</th>
									<th className="w-20 px-4 py-3 text-right">
										{ __( 'Orders', 'sales-pulse' ) }
									</th>
									<th className="w-36 px-4 py-3 text-right">
										{ __( 'Status', 'sales-pulse' ) }
									</th>
								</tr>
							</thead>
							<tbody>
								{ isLoading ? (
									Array.from( { length: 7 } ).map( ( _, index ) => (
										<HistoryRowSkeleton key={ index } />
									) )
								) : items.length === 0 ? (
									<tr>
										<td
											colSpan={ 7 }
											className="px-6 py-16 text-center text-sm text-muted-foreground"
										>
											{ __(
												'No history data available yet. Snapshots will appear here after the first nightly run.',
												'sales-pulse'
											) }
										</td>
									</tr>
								) : (
									items.map( ( item ) => (
										<HistoryRow key={ item.date } item={ item } />
									) )
								) }
							</tbody>
						</table>
					</div>
					<HistoryPager
						page={ page }
						totalPages={ totalPages }
						total={ total }
						onPrev={ () => setPage( ( p ) => Math.max( 1, p - 1 ) ) }
						onNext={ () => setPage( ( p ) => Math.min( totalPages, p + 1 ) ) }
					/>
				</div>
			) }
		</motion.div>
	);
}

function ExportCsvButton() {
	return (
		<TooltipProvider delayDuration={ 200 }>
			<Tooltip>
				<TooltipTrigger asChild>
					<button
						type="button"
						disabled
						className="inline-flex cursor-not-allowed items-center gap-2 rounded-full border border-solid border-border bg-surface px-4 py-1.5 text-sm font-medium text-muted-foreground opacity-70"
					>
						<Download className="h-3.5 w-3.5" />
						{ __( 'Export CSV', 'sales-pulse' ) }
					</button>
				</TooltipTrigger>
				<TooltipContent>
					{ __( 'Coming soon', 'sales-pulse' ) }
				</TooltipContent>
			</Tooltip>
		</TooltipProvider>
	);
}

function HistoryRowSkeleton() {
	return (
		<tr className="border-t border-solid border-border/60">
			<td className="px-4 py-3.5">
				<span className="flex h-8 w-8 items-center justify-center rounded-full bg-muted shimmer" />
			</td>
			<td className="px-4 py-3.5">
				<span className="block h-3 w-20 rounded bg-muted shimmer" />
			</td>
			<td className="px-4 py-3.5">
				<span className="block h-3 w-full rounded bg-muted shimmer" />
			</td>
			<td className="px-4 py-3.5">
				<span className="block h-3 w-16 rounded bg-muted shimmer" />
			</td>
			<td className="px-4 py-3.5">
				<span className="block h-3 w-20 rounded bg-muted shimmer" />
			</td>
			<td className="px-4 py-3.5">
				<span className="block h-3 w-12 rounded bg-muted shimmer" />
			</td>
			<td className="px-4 py-3.5">
				<span className="ml-auto block h-6 w-24 rounded-full bg-muted shimmer" />
			</td>
		</tr>
	);
}
