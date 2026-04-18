/**
 * Metric Cards grid — 4 KPI cards.
 */
import React from 'react';
import { MetricCard } from '@Components/MetricCard';
import { formatMetricValue, formatPercent } from '@Utils/formatters';
import { DollarSign, ShoppingCart, Receipt, Package } from 'lucide-react';

const iconMap = {
	revenue: <DollarSign className="h-5 w-5 text-primary" />,
	orders: <ShoppingCart className="h-5 w-5 text-primary" />,
	avg_order_value: <Receipt className="h-5 w-5 text-primary" />,
	items_per_order: <Package className="h-5 w-5 text-primary" />,
};

export function MetricCards( { cards } ) {
	if ( ! cards || ! cards.length ) {
		return null;
	}

	return (
		<div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
			{ cards.map( ( card ) => (
				<MetricCard
					key={ card.key }
					title={ card.label }
					value={ formatMetricValue( card.current, card.format ) }
					change={ formatPercent( card.change ) }
					changeType={ card.change > 0 ? 'increase' : card.change < 0 ? 'decrease' : 'neutral' }
					icon={ iconMap[ card.key ] }
				/>
			) ) }
		</div>
	);
}
