/**
 * PulseHeader - sticky brand + nav header.
 *
 * Preserves sales-pulse tab-routing: each nav item is a plain <a> that
 * triggers a full WordPress admin reload. `activeTab` is supplied by App.js.
 *
 * The "LIVE" badge reflects snapshot freshness - `wc_sma_admin_data.last_snapshot_at`
 * is an ISO8601 string set by the PHP boot. Missing/older than 26h → "Stale" variant.
 */
import React from 'react';
import { __ } from '@wordpress/i18n';
import { Activity } from 'lucide-react';
import classnames from '@Utils/classnames';

const STALE_THRESHOLD_MS = 26 * 60 * 60 * 1000;

function getTabs() {
	return [
		{ slug: 'overview', label: __( 'Overview', 'sales-pulse' ), href: 'admin.php?page=sales-pulse' },
		{ slug: 'history', label: __( 'History', 'sales-pulse' ), href: 'admin.php?page=sales-pulse&tab=history' },
		{ slug: 'campaigns', label: __( 'Campaigns', 'sales-pulse' ), href: 'admin.php?page=sales-pulse&tab=campaigns' },
		{ slug: 'settings', label: __( 'Settings', 'sales-pulse' ), href: 'admin.php?page=sales-pulse&tab=settings' },
	];
}

function useLiveState() {
	const lastSnapshot = window.wc_sma_admin_data?.last_snapshot_at;
	if ( ! lastSnapshot ) {
		return 'stale';
	}
	const age = Date.now() - new Date( lastSnapshot ).getTime();
	return Number.isFinite( age ) && age < STALE_THRESHOLD_MS ? 'live' : 'stale';
}

export function PulseHeader( { activeTab = 'overview' } ) {
	const tabs = getTabs();
	const version = window.wc_sma_admin_data?.version || '';
	const liveState = useLiveState();

	const liveClasses =
		liveState === 'live'
			? 'border-success/30 bg-success/10 text-success-foreground/80'
			: 'border-warning/40 bg-warning/15 text-warning-foreground';
	const liveDotClasses =
		liveState === 'live' ? 'bg-success pulse-dot' : 'bg-warning';

	return (
		<header className="sticky top-0 z-30 border-b border-solid border-border/70 bg-background/85 backdrop-blur-xl">
			<div className="mx-auto flex max-w-[1280px] items-center justify-between gap-6 px-6 py-4 lg:px-10">
				<a
					href="admin.php?page=sales-pulse"
					className="group flex items-center gap-2.5 no-underline"
				>
					<div className="relative flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-ink shadow-md">
						<Activity className="h-[18px] w-[18px] text-pulse" strokeWidth={ 2.5 } />
						<span className="absolute -right-0.5 -top-0.5 h-2 w-2 rounded-full bg-pulse pulse-dot" />
					</div>
					<div className="flex flex-col leading-none">
						<span className="font-display text-lg text-ink">
							{ __( 'Sales Pulse', 'sales-pulse' ) }
						</span>
						<span className="text-[10px] font-medium uppercase tracking-[0.14em] text-muted-foreground">
							{ __( 'WooCommerce insights', 'sales-pulse' ) }
						</span>
					</div>
				</a>

				<nav
					role="tablist"
					aria-label={ __( 'Dashboard sections', 'sales-pulse' ) }
					className="hidden items-center gap-1 rounded-full border border-solid border-border/80 bg-surface/60 p-1 shadow-xs md:flex"
				>
					{ tabs.map( ( tab ) => {
						const active = activeTab === tab.slug;
						return (
							<a
								key={ tab.slug }
								href={ tab.href }
								role="tab"
								aria-selected={ active }
								className={ classnames(
									'relative rounded-full px-4 py-1.5 text-sm font-medium no-underline transition-all',
									active
										? 'bg-primary text-primary-foreground shadow-sm'
										: 'text-muted-foreground hover:text-foreground'
								) }
							>
								{ tab.label }
							</a>
						);
					} ) }
				</nav>

				<div className="flex items-center gap-3">
					{ version && (
						<span className="hidden font-mono text-xs text-muted-foreground lg:inline">
							{ `v${ version }` }
						</span>
					) }
					<div
						className={ classnames(
							'flex items-center gap-1.5 rounded-full border border-solid px-2.5 py-1',
							liveClasses
						) }
					>
						<span
							className={ classnames( 'h-1.5 w-1.5 rounded-full', liveDotClasses ) }
						/>
						<span className="text-[10px] font-semibold uppercase tracking-wider">
							{ liveState === 'live'
								? __( 'Live', 'sales-pulse' )
								: __( 'Stale', 'sales-pulse' ) }
						</span>
					</div>
				</div>
			</div>

			<nav
				aria-label={ __( 'Dashboard sections', 'sales-pulse' ) }
				className="flex items-center gap-1 overflow-x-auto px-4 pb-3 md:hidden"
			>
				{ tabs.map( ( tab ) => {
					const active = activeTab === tab.slug;
					return (
						<a
							key={ tab.slug }
							href={ tab.href }
							aria-current={ active ? 'page' : undefined }
							className={ classnames(
								'shrink-0 rounded-full px-3.5 py-1.5 text-sm font-medium no-underline transition-all',
								active
									? 'bg-primary text-primary-foreground'
									: 'border border-solid border-border bg-surface text-muted-foreground'
							) }
						>
							{ tab.label }
						</a>
					);
				} ) }
			</nav>
		</header>
	);
}
