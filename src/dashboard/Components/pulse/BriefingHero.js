/**
 * BriefingHero - editorial headline card for the Overview page.
 *
 * Shows the day's diagnosis at a glance: status pill, optional campaign tag,
 * large change %, headline, cause, and an animated pulse signature graphic.
 */
import React from 'react';
import { motion } from 'framer-motion';
import { Megaphone } from 'lucide-react';
import { StatusBadge } from './StatusBadge';
import { directionFromChange, trendIcon, trendTextColor } from '@DashboardApp/Utils/statusMap';
import classnames from '@Utils/classnames';

const EASE = [ 0.16, 1, 0.3, 1 ];

export function BriefingHero( {
	changePct = 0,
	statusVariant = 'stable',
	campaignName,
	diagnosis,
	cause,
} ) {
	const direction = directionFromChange( changePct );
	const TrendIcon = trendIcon( direction );
	const trendColor = trendTextColor( direction );
	const displayChange = `${ changePct > 0 ? '+' : '' }${ changePct.toFixed( 1 ) }`;

	return (
		<motion.div
			initial={ { opacity: 0, y: 12 } }
			animate={ { opacity: 1, y: 0 } }
			transition={ { duration: 0.6, ease: EASE } }
			className="relative overflow-hidden rounded-3xl border border-solid border-border bg-gradient-card shadow-md"
		>
			<div className="pointer-events-none absolute -right-20 -top-32 h-64 w-64 rounded-full bg-pulse/10 blur-3xl" />
			<div className="pointer-events-none absolute -bottom-32 -left-20 h-64 w-64 rounded-full bg-chart-1/5 blur-3xl" />

			<div className="relative grid gap-8 p-8 md:p-10 lg:grid-cols-[1.4fr_1fr]">
				<div>
					<div className="mb-6 flex flex-wrap items-center gap-2">
						<StatusBadge variant={ statusVariant } />
						{ campaignName && (
							<span className="inline-flex items-center gap-1.5 rounded-full border border-solid border-chart-1/30 bg-chart-1/10 px-2.5 py-1 text-[11px] font-semibold text-chart-1">
								<Megaphone className="h-3 w-3" />
								{ campaignName }
							</span>
						) }
					</div>

					<div className="flex items-baseline gap-3">
						<TrendIcon
							className={ classnames( 'h-9 w-9', trendColor ) }
							strokeWidth={ 2.25 }
						/>
						<span className="font-display text-7xl leading-none text-ink md:text-8xl">
							{ displayChange }
							<span className="text-4xl text-muted-foreground">%</span>
						</span>
					</div>

					{ diagnosis && (
						<p className="mt-6 max-w-lg text-lg leading-snug text-foreground/90">
							{ diagnosis }
						</p>
					) }
					{ cause && (
						<p className="mt-2 text-sm text-muted-foreground">{ cause }</p>
					) }
				</div>

				<div className="relative flex items-center justify-center">
					<PulseGraphic direction={ direction } />
				</div>
			</div>
		</motion.div>
	);
}

function PulseGraphic( { direction } ) {
	const path =
		direction === 'up'
			? 'M0 80 L40 78 L60 75 L80 76 L100 60 L120 65 L140 40 L160 45 L180 20 L220 25'
			: direction === 'down'
				? 'M0 30 L40 32 L60 28 L80 35 L100 50 L120 45 L140 65 L160 60 L180 80 L220 78'
				: 'M0 50 L40 48 L60 52 L80 49 L100 51 L120 50 L140 52 L160 48 L180 51 L220 50';
	const endY = direction === 'up' ? 25 : direction === 'down' ? 78 : 50;

	return (
		<svg viewBox="0 0 220 100" className="h-40 w-full max-w-sm" aria-hidden="true">
			<defs>
				<linearGradient id="pulseLine" x1="0" y1="0" x2="1" y2="0">
					<stop offset="0%" stopColor="oklch(0.65 0.2 165)" stopOpacity="0.2" />
					<stop offset="50%" stopColor="oklch(0.65 0.2 165)" stopOpacity="1" />
					<stop offset="100%" stopColor="oklch(0.55 0.18 200)" stopOpacity="1" />
				</linearGradient>
				<linearGradient id="pulseFill" x1="0" y1="0" x2="0" y2="1">
					<stop offset="0%" stopColor="oklch(0.65 0.2 165)" stopOpacity="0.18" />
					<stop offset="100%" stopColor="oklch(0.65 0.2 165)" stopOpacity="0" />
				</linearGradient>
			</defs>
			<path d={ `${ path } L220 100 L0 100 Z` } fill="url(#pulseFill)" />
			<motion.path
				d={ path }
				fill="none"
				stroke="url(#pulseLine)"
				strokeWidth="2.5"
				strokeLinecap="round"
				strokeLinejoin="round"
				initial={ { pathLength: 0 } }
				animate={ { pathLength: 1 } }
				transition={ { duration: 1.6, ease: EASE } }
			/>
			<circle cx="220" cy={ endY } r="5" fill="oklch(0.65 0.2 165)">
				<animate
					attributeName="r"
					values="5;8;5"
					dur="1.6s"
					repeatCount="indefinite"
				/>
				<animate
					attributeName="opacity"
					values="1;0.4;1"
					dur="1.6s"
					repeatCount="indefinite"
				/>
			</circle>
		</svg>
	);
}
