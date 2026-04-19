/**
 * PageHeader — editorial title block used at the top of every screen.
 *
 * Renders an eyebrow pill, a large display title, optional subtitle, and
 * an actions slot (usually a `SegmentedControl`, save button, etc.).
 */
import React from 'react';

export function PageHeader( { eyebrow, title, subtitle, actions } ) {
	return (
		<div className="mb-10 flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
			<div>
				{ eyebrow && (
					<div className="mb-3 inline-flex items-center gap-2 rounded-full border border-solid border-border bg-surface/70 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-muted-foreground">
						<span className="h-1.5 w-1.5 rounded-full bg-pulse" />
						{ eyebrow }
					</div>
				) }
				<h1 className="m-0 font-display text-5xl leading-[0.95] text-ink md:text-6xl">
					{ title }
				</h1>
				{ subtitle && (
					<p className="mt-3 max-w-xl text-base text-muted-foreground">
						{ subtitle }
					</p>
				) }
			</div>
			{ actions && (
				<div className="flex flex-wrap items-center gap-2">{ actions }</div>
			) }
		</div>
	);
}
