/**
 * PulseShell - top-level dashboard layout.
 *
 * Sticky PulseHeader + editorial grid backdrop + constrained main + footer.
 */
import React from 'react';
import { __ } from '@wordpress/i18n';
import { PulseHeader } from './PulseHeader';

export function PulseShell( { activeTab = 'overview', children } ) {
	return (
		<div className="bg-canvas">
			{ /* Flex column with min-h-screen pins the footer to the
				 viewport bottom on short pages while still letting it
				 sit naturally under tall content. The bg-grid pattern
				 fills the whole column instead of clipping to content. */ }
			<div className="flex min-h-screen flex-col bg-grid">
				<PulseHeader activeTab={ activeTab } />

				<main className="mx-auto w-full max-w-[1280px] flex-1 px-6 py-10 lg:px-10 lg:py-14">
					{ children }
				</main>

				<footer className="mx-auto w-full max-w-[1280px] px-6 pb-10 lg:px-10">
					<div className="flex items-center justify-between border-t border-solid border-border/60 pt-6 text-xs text-muted-foreground">
						<span>
							{ __(
								'Built for WooCommerce stores that take revenue seriously.',
								'matriq-store-analytics'
							) }
						</span>
						<span className="font-mono">
							{ `Matriq Store Analytics · ${ new Date().getFullYear() }` }
						</span>
					</div>
				</footer>
			</div>
		</div>
	);
}
