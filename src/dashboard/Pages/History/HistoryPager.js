/**
 * HistoryPager - pill pagination control for the diagnosis log.
 */
import React from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import classnames from '@Utils/classnames';

function PagerButton( { onClick, disabled, ariaLabel, children } ) {
	return (
		<button
			type="button"
			onClick={ onClick }
			disabled={ disabled }
			aria-label={ ariaLabel }
			className={ classnames(
				'inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border border-solid border-border bg-surface text-muted-foreground transition-all hover:border-border/80 hover:bg-surface-elevated hover:text-foreground focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse',
				disabled && 'cursor-not-allowed opacity-40 hover:border-border hover:bg-surface hover:text-muted-foreground'
			) }
		>
			{ children }
		</button>
	);
}

export function HistoryPager( { page, totalPages, total, onPrev, onNext } ) {
	if ( totalPages <= 1 ) {
		return null;
	}

	return (
		<div className="flex items-center justify-between px-6 py-4">
			<p className="m-0 text-xs text-muted-foreground">
				{ sprintf(
					/* translators: 1: current page, 2: total pages */
					__( 'Page %1$d of %2$d', 'sales-pulse' ),
					page,
					totalPages
				) }
				{ ' · ' }
				<span className="font-mono">
					{ sprintf(
						/* translators: %d: total days */
						__( '%d days', 'sales-pulse' ),
						total
					) }
				</span>
			</p>
			<div className="flex gap-2">
				<PagerButton
					onClick={ onPrev }
					disabled={ page <= 1 }
					ariaLabel={ __( 'Previous page', 'sales-pulse' ) }
				>
					<ChevronLeft className="h-4 w-4" />
				</PagerButton>
				<PagerButton
					onClick={ onNext }
					disabled={ page >= totalPages }
					ariaLabel={ __( 'Next page', 'sales-pulse' ) }
				>
					<ChevronRight className="h-4 w-4" />
				</PagerButton>
			</div>
		</div>
	);
}
