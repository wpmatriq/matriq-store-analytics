/**
 * HistoryRow - single day row in the diagnosis log table.
 */
import React from 'react';
import classnames from '@Utils/classnames';
import { StatusBadge } from '@Components/pulse/StatusBadge';
import {
	statusVariant,
	trendIcon,
	trendTextColor,
} from '@DashboardApp/Utils/statusMap';
import {
	formatCurrency,
	formatDate,
	formatNumber,
	formatPercent,
} from '@Utils/formatters';

export function HistoryRow( { item } ) {
	const TrendIcon = trendIcon( item.direction );
	const trendColor = trendTextColor( item.direction );
	const tintClass =
		item.direction === 'growth'
			? 'bg-success/10'
			: item.direction === 'decline'
				? 'bg-destructive/10'
				: 'bg-secondary';
	const changeColor =
		item.change_percent > 0
			? 'text-success'
			: item.change_percent < 0
				? 'text-destructive'
				: 'text-muted-foreground';

	return (
		<tr className="border-t border-solid border-border/60 transition-colors hover:bg-muted/30">
			<td className="px-4 py-3.5">
				<span
					className={ classnames(
						'flex h-8 w-8 items-center justify-center rounded-full',
						tintClass
					) }
				>
					<TrendIcon className={ classnames( 'h-4 w-4', trendColor ) } />
				</span>
			</td>
			<td className="whitespace-nowrap px-4 py-3.5 text-sm font-medium text-foreground">
				{ formatDate( item.date ) }
			</td>
			<td className="px-4 py-3.5 text-sm text-muted-foreground">
				{ item.headline }
			</td>
			<td
				className={ classnames(
					'whitespace-nowrap px-4 py-3.5 text-right font-mono text-sm font-semibold tabular-nums',
					changeColor
				) }
			>
				{ formatPercent( item.change_percent ) }
			</td>
			<td className="whitespace-nowrap px-4 py-3.5 text-right font-mono text-sm tabular-nums text-foreground">
				{ formatCurrency( item.revenue ) }
			</td>
			<td className="whitespace-nowrap px-4 py-3.5 text-right font-mono text-sm tabular-nums text-muted-foreground">
				{ formatNumber( item.orders ) }
			</td>
			<td className="whitespace-nowrap px-4 py-3.5 text-right">
				<StatusBadge variant={ statusVariant( item.severity ) } />
			</td>
		</tr>
	);
}
