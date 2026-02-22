/**
 * Impact Breakdown — what changed and by how much.
 *
 * Visualizes the revenue decomposition (orders, items, price impacts)
 * with horizontal bar charts showing relative contribution.
 */
import React from 'react';
import { __ } from '@wordpress/i18n';
import { Card } from '@Components/ui/card';
import { formatCurrency } from '@Utils/formatters';
import classnames from '@Utils/classnames';
import { ArrowUpRight, ArrowDownRight, BarChart3 } from 'lucide-react';

const factorLabels = {
	orders: __( 'Order Volume', 'sales-pulse' ),
	items: __( 'Items per Order', 'sales-pulse' ),
	price: __( 'Avg Item Price', 'sales-pulse' ),
};

const factorIcons = {
	orders: '🛒',
	items: '📦',
	price: '💰',
};

export function ImpactBreakdown( { diagnosis } ) {
	const breakdown = diagnosis?.impact_breakdown;
	const hasData = breakdown && Object.keys( breakdown ).length > 0;

	return (
		<Card className="border border-solid">
			<div className="p-5">
				<div className="flex items-center gap-2 mb-4">
					<BarChart3 className="h-4 w-4 text-muted-foreground" />
					<h3 className="text-sm font-semibold m-0">{ __( 'What Changed', 'sales-pulse' ) }</h3>
				</div>

				{ ! hasData ? (
					<div className="flex flex-col items-center justify-center py-6 text-center">
						<div className="h-10 w-10 rounded-full bg-muted flex items-center justify-center mb-2">
							<BarChart3 className="h-5 w-5 text-muted-foreground/50" />
						</div>
						<p className="text-sm text-muted-foreground">
							{ __( 'No significant changes detected.', 'sales-pulse' ) }
						</p>
						<p className="text-xs text-muted-foreground/60 mt-1">
							{ __( 'Revenue factors remained stable.', 'sales-pulse' ) }
						</p>
					</div>
				) : (
					<div className="space-y-3">
						{ Object.entries( breakdown )
							.map( ( [ key, value ] ) => ( { key, value, abs: Math.abs( value ) } ) )
							.sort( ( a, b ) => b.abs - a.abs )
							.map( ( factor ) => {
								const isPositive = factor.value >= 0;
								const maxAbs = Math.max( ...Object.values( breakdown ).map( Math.abs ) ) || 1;
								const widthPct = Math.max( ( factor.abs / maxAbs ) * 100, 8 );

								return (
									<div key={ factor.key } className="space-y-1.5">
										<div className="flex items-center justify-between text-sm">
											<span className="flex items-center gap-1.5 text-muted-foreground">
												<span>{ factorIcons[ factor.key ] || '' }</span>
												{ factorLabels[ factor.key ] || factor.key }
											</span>
											<span className={ classnames(
												'font-semibold tabular-nums flex items-center gap-0.5',
												isPositive ? 'text-success' : 'text-destructive'
											) }>
												{ isPositive
													? <ArrowUpRight className="h-3 w-3" />
													: <ArrowDownRight className="h-3 w-3" />
												}
												{ isPositive ? '+' : '' }{ formatCurrency( factor.value ) }
											</span>
										</div>
										<div className="h-2 bg-muted rounded-full overflow-hidden">
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
				) }
			</div>
		</Card>
	);
}
