/**
 * MetricCard — KPI card with label, display value, change chip, and sparkline.
 *
 * Sparkline is drawn as an inline SVG area+line so the card stays cheap to
 * render. When `spark` has fewer than 2 data points, the sparkline degrades
 * gracefully to a flat baseline.
 */
import React, { useId } from 'react';
import { motion } from 'framer-motion';
import { ArrowDownRight, ArrowUpRight, Minus } from 'lucide-react';
import classnames from '@Utils/classnames';

const EASE = [ 0.16, 1, 0.3, 1 ];
const SPARK_WIDTH = 120;
const SPARK_HEIGHT = 36;

function buildSparkPaths( spark ) {
	const values = Array.isArray( spark ) && spark.length > 0 ? spark : [ 0, 0 ];
	const series = values.length === 1 ? [ values[ 0 ], values[ 0 ] ] : values;
	const min = Math.min( ...series );
	const max = Math.max( ...series );
	const range = max - min || 1;
	const points = series.map( ( v, i ) => {
		const x = ( i / ( series.length - 1 ) ) * SPARK_WIDTH;
		const y = SPARK_HEIGHT - ( ( v - min ) / range ) * SPARK_HEIGHT;
		return `${ x },${ y }`;
	} );
	const linePath = `M ${ points.join( ' L ' ) }`;
	const areaPath = `${ linePath } L ${ SPARK_WIDTH },${ SPARK_HEIGHT } L 0,${ SPARK_HEIGHT } Z`;
	return { linePath, areaPath };
}

export function MetricCard( {
	label,
	value,
	changePct = 0,
	icon: Icon,
	spark = [],
	delay = 0,
} ) {
	const direction = changePct > 0.5 ? 'up' : changePct < -0.5 ? 'down' : 'flat';
	const ChangeIcon = direction === 'up' ? ArrowUpRight : direction === 'down' ? ArrowDownRight : Minus;
	const changeClasses =
		direction === 'up'
			? 'text-success bg-success/10'
			: direction === 'down'
				? 'text-destructive bg-destructive/10'
				: 'text-muted-foreground bg-muted';

	const sparkColor =
		direction === 'down' ? 'oklch(0.6 0.22 25)' : 'oklch(0.65 0.2 165)';
	const { linePath, areaPath } = buildSparkPaths( spark );
	const gradientId = useId().replace( /:/g, '' );

	return (
		<motion.div
			initial={ { opacity: 0, y: 16 } }
			animate={ { opacity: 1, y: 0 } }
			transition={ { duration: 0.5, delay, ease: EASE } }
			className="group relative overflow-hidden rounded-2xl border border-solid border-border bg-card p-6 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md"
		>
			<div className="mb-5 flex items-start justify-between">
				<div>
					<p className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
						{ label }
					</p>
					<p className="mt-2 font-display text-4xl leading-none text-ink">
						{ value }
					</p>
				</div>
				{ Icon && (
					<div className="flex h-9 w-9 items-center justify-center rounded-xl bg-secondary text-muted-foreground transition-colors group-hover:bg-primary/5 group-hover:text-primary">
						<Icon className="h-[18px] w-[18px]" strokeWidth={ 2 } />
					</div>
				) }
			</div>

			<div className="flex items-end justify-between gap-4">
				<span
					className={ classnames(
						'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold',
						changeClasses
					) }
				>
					<ChangeIcon className="h-3 w-3" />
					{ `${ changePct > 0 ? '+' : '' }${ changePct.toFixed( 1 ) }%` }
				</span>

				<svg
					viewBox={ `0 0 ${ SPARK_WIDTH } ${ SPARK_HEIGHT }` }
					className="h-9 w-28"
					aria-hidden="true"
				>
					<defs>
						<linearGradient id={ `spark-${ gradientId }` } x1="0" y1="0" x2="0" y2="1">
							<stop offset="0%" stopColor={ sparkColor } stopOpacity="0.25" />
							<stop offset="100%" stopColor={ sparkColor } stopOpacity="0" />
						</linearGradient>
					</defs>
					<path d={ areaPath } fill={ `url(#spark-${ gradientId })` } />
					<path
						d={ linePath }
						fill="none"
						stroke={ sparkColor }
						strokeWidth="1.75"
						strokeLinecap="round"
						strokeLinejoin="round"
					/>
				</svg>
			</div>
		</motion.div>
	);
}
