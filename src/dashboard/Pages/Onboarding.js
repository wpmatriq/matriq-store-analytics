/**
 * Onboarding — minimal welcome screen for the hidden sales-pulse-onboarding page.
 *
 * The real first-run data-readiness flow lives in `ReadinessGate` inside the
 * main dashboard. This screen is only reached via the hidden submenu and acts
 * as a friendly landing card that nudges the user back to the dashboard.
 */
import React from 'react';
import { motion } from 'framer-motion';
import { __ } from '@wordpress/i18n';
import { Activity, ArrowRight } from 'lucide-react';

const EASE = [ 0.16, 1, 0.3, 1 ];

const Onboarding = () => {
	const dashboardUrl =
		window.wc_sma_admin_data?.dashboard_url || 'admin.php?page=sales-pulse';

	return (
		<motion.div
			initial={ { opacity: 0, y: 12 } }
			animate={ { opacity: 1, y: 0 } }
			transition={ { duration: 0.6, ease: EASE } }
			className="mx-auto mt-10 max-w-xl"
		>
			<div className="relative overflow-hidden rounded-3xl border border-solid border-border bg-gradient-card p-8 shadow-md md:p-10">
				<div className="pointer-events-none absolute -right-20 -top-32 h-64 w-64 rounded-full bg-pulse/10 blur-3xl" />

				<div className="relative">
					<div className="mb-3 inline-flex items-center gap-2 rounded-full border border-solid border-border bg-surface/70 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-muted-foreground">
						<span className="h-1.5 w-1.5 rounded-full bg-pulse" />
						{ __( 'Welcome', 'sales-pulse' ) }
					</div>

					<h1 className="m-0 flex items-center gap-3 font-display text-4xl leading-tight text-ink md:text-5xl">
						<span className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-ink text-pulse shadow-md">
							<Activity className="h-5 w-5" strokeWidth={ 2.5 } />
						</span>
						{ __( "Let's take your pulse.", 'sales-pulse' ) }
					</h1>

					<p className="mt-3 max-w-md text-sm text-muted-foreground">
						{ __(
							'Sales Pulse runs a nightly diagnosis of your store so you always know what changed, why it matters, and what to do next.',
							'sales-pulse'
						) }
					</p>

					<a
						href={ dashboardUrl }
						className="mt-6 inline-flex cursor-pointer items-center gap-2 rounded-full border-0 bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground no-underline shadow-sm transition-all hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse"
					>
						{ __( 'Open dashboard', 'sales-pulse' ) }
						<ArrowRight className="h-4 w-4" />
					</a>
				</div>
			</div>
		</motion.div>
	);
};

export default Onboarding;
