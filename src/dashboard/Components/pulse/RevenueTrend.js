/**
 * RevenueTrend — 7-day revenue area chart with Total / Peak / Avg summary.
 *
 * Takes an array of `{ date, revenue }` points from the overview trend hook.
 * The day label is derived client-side from `date` (expected YYYY-MM-DD).
 */
import React from 'react';
import { motion } from 'framer-motion';
import { __ } from '@wordpress/i18n';
import { TrendingUp } from 'lucide-react';
import {
	Area,
	AreaChart,
	CartesianGrid,
	ResponsiveContainer,
	Tooltip,
	XAxis,
	YAxis,
} from 'recharts';
import { formatCurrency } from '@Utils/formatters';

const EASE = [ 0.16, 1, 0.3, 1 ];

function toDayLabel( isoDate ) {
	if ( ! isoDate ) {
		return '';
	}
	try {
		const d = new Date( `${ isoDate }T00:00:00` );
		return d.toLocaleDateString( undefined, { weekday: 'short' } );
	} catch ( e ) {
		return isoDate;
	}
}

export function RevenueTrend( { trend = [], currency } ) {
	const data = ( trend || [] ).map( ( point ) => ( {
		day: toDayLabel( point.date ),
		revenue: Number( point.revenue ) || 0,
	} ) );
	const safeData = data.length ? data : Array.from( { length: 7 }, () => ( { day: '', revenue: 0 } ) );
	const revenues = safeData.map( ( d ) => d.revenue );
	const total = revenues.reduce( ( s, n ) => s + n, 0 );
	const peak = revenues.length ? Math.max( ...revenues ) : 0;
	const avg = revenues.length ? total / revenues.length : 0;

	const fmt = ( n ) => formatCurrency( n, currency );

	return (
		<motion.section
			initial={ { opacity: 0, y: 14 } }
			animate={ { opacity: 1, y: 0 } }
			transition={ { duration: 0.5, delay: 0.4, ease: EASE } }
			className="rounded-2xl border border-solid border-border bg-card shadow-sm"
		>
			<header className="flex flex-wrap items-center justify-between gap-4 border-b border-solid border-border/70 px-6 py-5">
				<div className="flex items-center gap-3">
					<div className="flex h-8 w-8 items-center justify-center rounded-lg bg-pulse/10 text-pulse ring-1 ring-pulse/20">
						<TrendingUp className="h-4 w-4" strokeWidth={ 2.25 } />
					</div>
					<div>
						<h3 className="m-0 text-sm font-semibold text-foreground">
							{ __( '7-Day Revenue Trend', 'sales-pulse' ) }
						</h3>
						<p className="m-0 text-xs text-muted-foreground">
							{ __( 'Rolling daily net revenue', 'sales-pulse' ) }
						</p>
					</div>
				</div>
				<div className="flex items-center gap-6">
					<Stat label={ __( 'Total', 'sales-pulse' ) } value={ fmt( total ) } />
					<Stat label={ __( 'Peak', 'sales-pulse' ) } value={ fmt( peak ) } />
					<Stat label={ __( 'Avg', 'sales-pulse' ) } value={ fmt( avg ) } />
				</div>
			</header>

			<div className="px-2 pb-2 pt-4">
				<ResponsiveContainer width="100%" height={ 280 }>
					<AreaChart
						data={ safeData }
						margin={ { top: 12, right: 24, left: 12, bottom: 12 } }
					>
						<defs>
							<linearGradient id="revenueArea" x1="0" y1="0" x2="0" y2="1">
								<stop offset="0%" stopColor="oklch(0.65 0.2 165)" stopOpacity="0.35" />
								<stop offset="100%" stopColor="oklch(0.65 0.2 165)" stopOpacity="0" />
							</linearGradient>
						</defs>
						<CartesianGrid
							strokeDasharray="3 6"
							stroke="oklch(0.18 0.025 260 / 0.08)"
							vertical={ false }
						/>
						<XAxis
							dataKey="day"
							tick={ { fontSize: 12, fill: 'oklch(0.5 0.02 260)' } }
							axisLine={ false }
							tickLine={ false }
						/>
						<YAxis
							tick={ { fontSize: 12, fill: 'oklch(0.5 0.02 260)' } }
							axisLine={ false }
							tickLine={ false }
							tickFormatter={ fmt }
							width={ 60 }
						/>
						<Tooltip
							contentStyle={ {
								background: 'oklch(1 0 0)',
								border: '1px solid oklch(0.9 0.008 90)',
								borderRadius: '12px',
								fontSize: '12px',
								boxShadow: '0 8px 24px oklch(0.18 0.025 260 / 0.08)',
							} }
							labelStyle={ { color: 'oklch(0.5 0.02 260)', fontSize: '11px', fontWeight: 600 } }
							formatter={ ( v ) => [ fmt( Number( v ) ), __( 'Revenue', 'sales-pulse' ) ] }
						/>
						<Area
							type="monotone"
							dataKey="revenue"
							stroke="oklch(0.65 0.2 165)"
							strokeWidth={ 2.5 }
							fill="url(#revenueArea)"
							dot={ { fill: 'oklch(0.65 0.2 165)', r: 3, strokeWidth: 0 } }
							activeDot={ { r: 5, strokeWidth: 2, stroke: 'oklch(1 0 0)' } }
						/>
					</AreaChart>
				</ResponsiveContainer>
			</div>
		</motion.section>
	);
}

function Stat( { label, value } ) {
	return (
		<div className="flex flex-col leading-tight">
			<span className="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
				{ label }
			</span>
			<span className="font-mono text-sm font-semibold text-foreground">
				{ value }
			</span>
		</div>
	);
}
