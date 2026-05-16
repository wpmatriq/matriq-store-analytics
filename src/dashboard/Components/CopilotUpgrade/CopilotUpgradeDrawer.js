/**
 * CopilotUpgradeDrawer - free-side upgrade pitch surface.
 *
 * Slides in from the right when CopilotUpgradeTrigger fires the
 * `matriq_msa:copilot-upgrade-toggle` event. Mirrors Pro's ChatDrawer
 * geometry (480px wide on lg+, fixed-position right edge, AnimatePresence
 * slide) so the free→Pro upgrade transition feels like the same product
 * gaining capability rather than swapping screens.
 *
 * Self-gates on Pro absence. When Pro is active, Pro's ChatDrawer
 * renders into the same `app-overlay` slot and this returns null.
 *
 * Voice: Calm Intelligence — observation, cause, guidance. No marketing
 * hyperbole. Five concrete capabilities, one CTA.
 */
import React, { useEffect, useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { __ } from '@wordpress/i18n';
import {
	AlertTriangle,
	ArrowUpRight,
	BarChart3,
	MessageSquare,
	Sparkles,
	Target,
	Workflow,
	X,
} from 'lucide-react';
import { isProActive } from './proActive';

const PRO_PRODUCT_URL = 'https://wpsalespulse.com/copilot';
const EASE = [ 0.16, 1, 0.3, 1 ];

const FEATURES = [
	{
		icon: MessageSquare,
		title: __( 'Conversational analyst', 'matriq-store-analytics' ),
		body: __(
			'Ask anything about your store in plain language. Answers are grounded in your real data, never invented.',
			'matriq-store-analytics'
		),
	},
	{
		icon: Sparkles,
		title: __( 'AI-tailored diagnoses', 'matriq-store-analytics' ),
		body: __(
			'Plain-language explanations of why revenue moved, layered on top of the deterministic morning briefing.',
			'matriq-store-analytics'
		),
	},
	{
		icon: AlertTriangle,
		title: __( 'Proactive anomaly alerts', 'matriq-store-analytics' ),
		body: __(
			'Revenue cliffs, refund spikes, and AOV crashes surface the moment they happen. Slack and webhook delivery included.',
			'matriq-store-analytics'
		),
	},
	{
		icon: Target,
		title: __( 'Revenue attribution', 'matriq-store-analytics' ),
		body: __(
			'See exactly which campaigns and coupons earned each order. Email clicks, conversions, and AI economics in one dashboard.',
			'matriq-store-analytics'
		),
	},
	{
		icon: Workflow,
		title: __( 'Automated playbooks', 'matriq-store-analytics' ),
		body: __(
			'Win-back, Refund-triage, Flash-sale-recovery and more. Approve actions on a queue or let safe ones run on a schedule.',
			'matriq-store-analytics'
		),
	},
];

function useDrawerToggle() {
	const [ open, setOpen ] = useState( false );

	useEffect( () => {
		const onToggle = () => setOpen( ( v ) => ! v );
		const onClose = () => setOpen( false );
		window.addEventListener( 'matriq_msa:copilot-upgrade-toggle', onToggle );
		window.addEventListener( 'matriq_msa:copilot-upgrade-close', onClose );
		return () => {
			window.removeEventListener( 'matriq_msa:copilot-upgrade-toggle', onToggle );
			window.removeEventListener( 'matriq_msa:copilot-upgrade-close', onClose );
		};
	}, [] );

	useEffect( () => {
		if ( ! open ) {
			return undefined;
		}
		const onKey = ( e ) => {
			if ( e.key === 'Escape' ) {
				setOpen( false );
			}
		};
		window.addEventListener( 'keydown', onKey );
		return () => window.removeEventListener( 'keydown', onKey );
	}, [ open ] );

	return [ open, setOpen ];
}

export function CopilotUpgradeDrawer() {
	const [ open, setOpen ] = useDrawerToggle();

	if ( isProActive() ) {
		return null;
	}

	const handleClose = () => setOpen( false );

	return (
		<AnimatePresence>
			{ open && (
				<>
					<motion.div
						key="upgrade-backdrop"
						initial={ { opacity: 0 } }
						animate={ { opacity: 1 } }
						exit={ { opacity: 0 } }
						transition={ { duration: 0.2 } }
						className="fixed inset-0 z-[99998] hidden bg-foreground/30 backdrop-blur-sm lg:block"
						onClick={ handleClose }
						aria-hidden="true"
					/>
					<motion.aside
						key="upgrade-panel"
						initial={ { x: '100%' } }
						animate={ { x: 0 } }
						exit={ { x: '100%' } }
						transition={ { duration: 0.3, ease: EASE } }
						className="fixed right-0 top-0 z-[99999] hidden h-screen w-[480px] flex-col bg-background shadow-2xl lg:flex !m-0"
						aria-label={ __( 'Discover Store Copilot', 'matriq-store-analytics' ) }
					>
						<DrawerBody onClose={ handleClose } />
					</motion.aside>
				</>
			) }
		</AnimatePresence>
	);
}

function DrawerBody( { onClose } ) {
	return (
		<div className="flex h-full flex-col">
			<header className="flex items-start justify-between gap-3 border-b border-solid border-border/60 px-6 py-5">
				<div className="flex min-w-0 items-start gap-3">
					<div className="flex h-9 w-9 items-center justify-center rounded-xl bg-pulse/10 ring-1 ring-pulse/20">
						<Sparkles className="h-4 w-4 text-pulse" strokeWidth={ 2.25 } />
					</div>
					<div className="min-w-0">
						<h2 className="m-0 font-display text-lg leading-tight text-ink">
							{ __( 'Your store, with an analyst on call.', 'matriq-store-analytics' ) }
						</h2>
						<p className="m-0 mt-1 text-xs text-muted-foreground">
							{ __( 'Matriq Store Analytics measures. Copilot acts.', 'matriq-store-analytics' ) }
						</p>
					</div>
				</div>
				<button
					type="button"
					onClick={ onClose }
					className="inline-flex shrink-0 cursor-pointer items-center justify-center rounded-full border-0 bg-transparent p-1.5 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse"
					aria-label={ __( 'Close', 'matriq-store-analytics' ) }
					title={ __( 'Close', 'matriq-store-analytics' ) }
				>
					<X className="h-4 w-4" />
				</button>
			</header>

			<div className="flex-1 overflow-y-auto px-6 py-5">
				<p className="m-0 text-sm leading-relaxed text-muted-foreground">
					{ __(
						'Store Copilot layers conversational analysis, proactive anomaly alerts, revenue attribution, and one-click automation onto the morning briefing you already trust.',
						'matriq-store-analytics'
					) }
				</p>

				<ul className="m-0 mt-5 flex list-none flex-col gap-3 p-0">
					{ FEATURES.map( ( feature ) => {
						const Icon = feature.icon;
						return (
							<li
								key={ feature.title }
								className="flex items-start gap-3 rounded-2xl border border-solid border-border/60 bg-card/60 p-4"
							>
								<div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-pulse/10 text-pulse ring-1 ring-pulse/20">
									<Icon className="h-4 w-4" strokeWidth={ 2 } />
								</div>
								<div className="min-w-0">
									<p className="m-0 text-sm font-semibold text-foreground">
										{ feature.title }
									</p>
									<p className="m-0 mt-1 text-xs leading-relaxed text-muted-foreground">
										{ feature.body }
									</p>
								</div>
							</li>
						);
					} ) }
				</ul>
			</div>

			<footer className="border-t border-solid border-border/60 bg-muted/30 px-6 py-4">
				<a
					href={ PRO_PRODUCT_URL }
					target="_blank"
					rel="noopener noreferrer"
					className="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-full border-0 bg-primary py-3 text-sm font-semibold text-primary-foreground no-underline shadow-sm transition-all hover:text-primary-foreground hover:shadow-md focus:text-primary-foreground focus-visible:text-primary-foreground focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse"
				>
					<BarChart3 className="h-4 w-4" />
					{ __( 'Upgrade to Store Copilot', 'matriq-store-analytics' ) }
					<ArrowUpRight className="h-4 w-4" />
				</a>
				<p className="m-0 mt-2 text-center text-[11px] text-muted-foreground">
					{ __( 'Pro adds AI. The free brain stays exactly as it is.', 'matriq-store-analytics' ) }
				</p>
			</footer>
		</div>
	);
}
