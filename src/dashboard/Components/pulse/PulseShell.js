/**
 * PulseShell — top-level dashboard layout.
 *
 * Sticky PulseHeader + editorial grid backdrop + constrained main + footer.
 */
import React from 'react';
import { __ } from '@wordpress/i18n';
import { PulseHeader } from './PulseHeader';

export function PulseShell( { activeTab = 'overview', children } ) {
	return (
		<div className="min-h-screen bg-canvas">
			<div className="bg-grid">
				<PulseHeader activeTab={ activeTab } />

				<main className="mx-auto max-w-[1280px] px-6 py-10 lg:px-10 lg:py-14">
					{ children }
				</main>

				<footer className="mx-auto max-w-[1280px] px-6 pb-10 lg:px-10">
					<div className="flex items-center justify-between border-t border-solid border-border/60 pt-6 text-xs text-muted-foreground">
						<span>
							{ __(
								'Sales Pulse · Built for WooCommerce stores that take revenue seriously.',
								'sales-pulse'
							) }
						</span>
						<span className="font-mono">
							{ `© ${ new Date().getFullYear() }` }
						</span>
					</div>
				</footer>
			</div>
		</div>
	);
}
