/**
 * Metric Cards grid — 4 KPI cards (Revenue, Orders, AOV, Items per Order).
 *
 * Revenue uses the real 7-day revenue series for its sparkline. The other
 * three fall back to a flat baseline until the backend exposes per-metric
 * history — see resolved decision #3 in the plan.
 */
import React from 'react';
import { DollarSign, Package, Receipt, ShoppingCart } from 'lucide-react';
import { MetricCard } from '@Components/pulse/MetricCard';
import { formatMetricValue } from '@Utils/formatters';

const ICONS = {
	revenue: DollarSign,
	orders: ShoppingCart,
	avg_order_value: Receipt,
	items_per_order: Package,
};

function sparkForCard( key, trend ) {
	if ( key === 'revenue' && Array.isArray( trend ) && trend.length > 0 ) {
		return trend.map( ( point ) => Number( point.revenue ) || 0 );
	}
	if ( key === 'orders' && Array.isArray( trend ) && trend.length > 0 ) {
		return trend.map( ( point ) => Number( point.orders ) || 0 );
	}
	// Flat baseline until the backend exposes per-metric history.
	return [ 0, 0, 0, 0, 0, 0, 0 ];
}

export function MetricCards( { cards, trend } ) {
	if ( ! cards || ! cards.length ) {
		return (
			<div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
				{ Array.from( { length: 4 } ).map( ( _, index ) => (
					<MetricCardSkeleton key={ index } delay={ index * 0.05 } />
				) ) }
			</div>
		);
	}

	return (
		<div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
			{ cards.map( ( card, index ) => (
				<MetricCard
					key={ card.key }
					label={ card.label }
					value={ formatMetricValue( card.current, card.format ) }
					changePct={ Number( card.change ) || 0 }
					icon={ ICONS[ card.key ] }
					spark={ sparkForCard( card.key, trend ) }
					delay={ index * 0.05 }
				/>
			) ) }
		</div>
	);
}

function MetricCardSkeleton( { delay = 0 } ) {
	return (
		<div
			className="rounded-2xl border border-solid border-border bg-card p-6 shadow-sm"
			style={ { animationDelay: `${ delay }s` } }
		>
			<div className="mb-5 flex items-start justify-between">
				<div className="space-y-2">
					<div className="h-3 w-16 rounded-full bg-muted" />
					<div className="h-8 w-24 rounded-lg bg-muted" />
				</div>
				<div className="h-9 w-9 rounded-xl bg-muted" />
			</div>
			<div className="flex items-end justify-between">
				<div className="h-5 w-12 rounded-full bg-muted" />
				<div className="h-5 w-24 rounded bg-muted shimmer" />
			</div>
		</div>
	);
}
