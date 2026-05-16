/**
 * RevenueTrend - revenue area chart with Total / Peak / Avg summary.
 *
 * Title and subtitle are driven by the `period` prop (weekly | monthly), not
 * by the row count returned from the API - sparse data should not change the
 * stated window. Caller is responsible for hiding this on the daily tab.
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

function toDayLabel( isoDate, format = 'weekday' ) {
	if ( ! isoDate ) {
		return '';
	}
	try {
		const d = new Date( `${ isoDate }T00:00:00` );
		if ( format === 'monthday' ) {
			return d.toLocaleDateString( undefined, { month: 'short', day: 'numeric' } );
		}
		return d.toLocaleDateString( undefined, { weekday: 'short' } );
	} catch ( e ) {
		return isoDate;
	}
}

const PERIOD_CONFIG = {
	weekly: {
		days: 7,
		labelFormat: 'weekday',
		title: __( '7-Day Revenue Trend', 'matriq-store-analytics' ),
		subtitle: __( 'Daily net revenue across the week', 'matriq-store-analytics' ),
	},
	monthly: {
		days: 30,
		labelFormat: 'monthday',
		title: __( '30-Day Revenue Trend', 'matriq-store-analytics' ),
		subtitle: __( 'Daily net revenue across the month', 'matriq-store-analytics' ),
	},
};

export function RevenueTrend( { trend = [], currency, period = 'weekly' } ) {
	const config = PERIOD_CONFIG[ period ] || PERIOD_CONFIG.weekly;
	const data = ( trend || [] ).map( ( point ) => ( {
		day: toDayLabel( point.date, config.labelFormat ),
		revenue: Number( point.revenue ) || 0,
	} ) );
	const safeData = data.length
		? data
		: Array.from( { length: config.days }, () => ( { day: '', revenue: 0 } ) );
	const revenues = safeData.map( ( d ) => d.revenue );
	const total = revenues.reduce( ( s, n ) => s + n, 0 );
	const peak = revenues.length ? Math.max( ...revenues ) : 0;
	const avg = revenues.length ? total / revenues.length : 0;

	const fmt = ( n ) => formatCurrency( n, currency );
	const title = config.title;
	const subtitle = config.subtitle;

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
							{ title }
						</h3>
						<p className="m-0 text-xs text-muted-foreground">
							{ subtitle }
						</p>
					</div>
				</div>
				<div className="flex items-center gap-6">
					<Stat label={ __( 'Total', 'matriq-store-analytics' ) } value={ fmt( total ) } />
					<Stat label={ __( 'Peak', 'matriq-store-analytics' ) } value={ fmt( peak ) } />
					<Stat label={ __( 'Avg', 'matriq-store-analytics' ) } value={ fmt( avg ) } />
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
							interval={ period === 'monthly' ? 'preserveStartEnd' : 0 }
							minTickGap={ 16 }
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
							formatter={ ( v ) => [ fmt( Number( v ) ), __( 'Revenue', 'matriq-store-analytics' ) ] }
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
