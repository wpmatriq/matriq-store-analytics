/**
 * History Page — daily diagnosis log.
 *
 * Paginated table of daily revenue explanations, building trust
 * through a visible track record.
 */
import React, { useState } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { useHistory } from '@DashboardApp/hooks/useHistory';
import { Card } from '@Components/ui/card';
import { SeverityBadge } from '@Components/SeverityBadge';
import { Button } from '@Components/ui/button';
import { formatDate, formatCurrency, formatPercent, formatNumber } from '@Utils/formatters';
import classnames from '@Utils/classnames';
import { RefreshCw, ChevronLeft, ChevronRight, TrendingUp, TrendingDown, Minus } from 'lucide-react';

const directionIcons = {
	growth: <TrendingUp className="h-4 w-4 text-success" />,
	decline: <TrendingDown className="h-4 w-4 text-destructive" />,
	stable: <Minus className="h-4 w-4 text-muted-foreground" />,
};

export default function HistoryPage() {
	const [ page, setPage ] = useState( 1 );
	const { data, isLoading } = useHistory( page );

	return (
		<div className="space-y-5">
			<div>
				<h1 className="text-xl font-semibold mb-2">{ __( 'History', 'sales-pulse' ) }</h1>
				<p className="text-sm text-muted-foreground mt-1">
					{ __( 'Daily revenue diagnosis log', 'sales-pulse' ) }
				</p>
			</div>

			<Card className="border border-solid overflow-hidden">
				{ isLoading ? (
					<div className="flex items-center justify-center py-16">
						<RefreshCw className="h-6 w-6 animate-spin text-muted-foreground" />
					</div>
				) : ! data?.items?.length ? (
					<div className="text-center py-16 text-muted-foreground text-sm">
						{ __( 'No history data available yet. Snapshots will appear here after the first nightly run.', 'sales-pulse' ) }
					</div>
				) : (
					<table className="w-full text-sm">
						<thead>
							<tr className="border-b border-solid bg-muted/40">
								<th className="w-10 px-3 py-2.5"></th>
								<th className="text-left font-medium text-xs text-muted-foreground px-4 py-2.5 w-24">{ __( 'Date', 'sales-pulse' ) }</th>
								<th className="text-left font-medium text-xs text-muted-foreground px-4 py-2.5">{ __( 'Diagnosis', 'sales-pulse' ) }</th>
								<th className="text-right font-medium text-xs text-muted-foreground px-4 py-2.5 w-24">{ __( 'Change', 'sales-pulse' ) }</th>
								<th className="text-right font-medium text-xs text-muted-foreground px-4 py-2.5 w-24">{ __( 'Revenue', 'sales-pulse' ) }</th>
								<th className="text-right font-medium text-xs text-muted-foreground px-4 py-2.5 w-20">{ __( 'Orders', 'sales-pulse' ) }</th>
								<th className="text-right font-medium text-xs text-muted-foreground px-4 py-2.5 w-28">{ __( 'Status', 'sales-pulse' ) }</th>
							</tr>
						</thead>
						<tbody className="divide-y">
							{ data.items.map( ( item ) => (
								<HistoryRow key={ item.date } item={ item } />
							) ) }
						</tbody>
					</table>
				) }
			</Card>

			{ /* Pagination */ }
			{ data?.total_pages > 1 && (
				<div className="flex items-center justify-between">
					<p className="text-xs text-muted-foreground">
						{ sprintf(
							/* translators: %1$d: current page, %2$d: total pages, %3$d: total days */
							__( 'Page %1$d of %2$d (%3$d days)', 'sales-pulse' ),
							page,
							data.total_pages,
							data.total
						) }
					</p>
					<div className="flex gap-2">
						<Button
							variant="outline"
							size="sm"
							disabled={ page <= 1 }
							onClick={ () => setPage( ( p ) => p - 1 ) }
						>
							<ChevronLeft className="h-4 w-4" />
						</Button>
						<Button
							variant="outline"
							size="sm"
							disabled={ page >= data.total_pages }
							onClick={ () => setPage( ( p ) => p + 1 ) }
						>
							<ChevronRight className="h-4 w-4" />
						</Button>
					</div>
				</div>
			) }
		</div>
	);
}

function HistoryRow( { item } ) {
	return (
		<tr className="hover:bg-muted/30 transition-colors">
			<td className="px-3 py-3 text-center">
				{ directionIcons[ item.direction ] || directionIcons.stable }
			</td>
			<td className="px-4 py-3 text-xs text-muted-foreground whitespace-nowrap">
				{ formatDate( item.date ) }
			</td>
			<td className="px-4 py-3">
				<span className="truncate">{ item.headline }</span>
			</td>
			<td className="px-4 py-3 text-right whitespace-nowrap">
				<span className={ classnames(
					'font-medium tabular-nums',
					item.change_percent > 0 ? 'text-success' : item.change_percent < 0 ? 'text-destructive' : 'text-muted-foreground'
				) }>
					{ formatPercent( item.change_percent ) }
				</span>
			</td>
			<td className="px-4 py-3 text-right text-muted-foreground tabular-nums whitespace-nowrap">
				{ formatCurrency( item.revenue ) }
			</td>
			<td className="px-4 py-3 text-right text-muted-foreground tabular-nums whitespace-nowrap">
				{ formatNumber( item.orders ) }
			</td>
			<td className="px-4 py-3 text-right">
				<SeverityBadge severity={ item.severity } />
			</td>
		</tr>
	);
}
