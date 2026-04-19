/**
 * ImpactBreakdownContent — revenue decomposition bars.
 *
 * Rendered inside an <InsightCard title="What changed">. This file owns just
 * the factor bars; the card chrome, header, and empty state are handled by
 * InsightCard.
 */
import React from 'react';
import { __ } from '@wordpress/i18n';
import { ArrowDownRight, ArrowUpRight } from 'lucide-react';
import classnames from '@Utils/classnames';
import { formatCurrency } from '@Utils/formatters';

const FACTOR_LABELS = {
	orders: __( 'Order volume', 'sales-pulse' ),
	items: __( 'Items per order', 'sales-pulse' ),
	price: __( 'Avg item price', 'sales-pulse' ),
};

export function ImpactBreakdownContent( { breakdown } ) {
	const entries = Object.entries( breakdown || {} )
		.map( ( [ key, value ] ) => ( { key, value: Number( value ) || 0, abs: Math.abs( Number( value ) || 0 ) } ) )
		.sort( ( a, b ) => b.abs - a.abs );

	if ( ! entries.length ) {
		return null;
	}

	const maxAbs = Math.max( ...entries.map( ( e ) => e.abs ), 1 );

	return (
		<div className="space-y-4">
			{ entries.map( ( factor ) => {
				const isPositive = factor.value >= 0;
				const widthPct = Math.max( ( factor.abs / maxAbs ) * 100, 8 );

				return (
					<div key={ factor.key } className="space-y-1.5">
						<div className="flex items-center justify-between text-sm">
							<span className="text-muted-foreground">
								{ FACTOR_LABELS[ factor.key ] || factor.key }
							</span>
							<span
								className={ classnames(
									'flex items-center gap-0.5 font-mono text-sm font-semibold tabular-nums',
									isPositive ? 'text-success' : 'text-destructive'
								) }
							>
								{ isPositive ? (
									<ArrowUpRight className="h-3.5 w-3.5" />
								) : (
									<ArrowDownRight className="h-3.5 w-3.5" />
								) }
								{ isPositive ? '+' : '' }
								{ formatCurrency( factor.value ) }
							</span>
						</div>
						<div className="h-2 overflow-hidden rounded-full bg-muted">
							<div
								className={ classnames(
									'h-full rounded-full transition-all duration-500',
									isPositive ? 'bg-success/60' : 'bg-destructive/60'
								) }
								style={ { width: `${ widthPct }%` } }
							/>
						</div>
					</div>
				);
			} ) }
		</div>
	);
}
