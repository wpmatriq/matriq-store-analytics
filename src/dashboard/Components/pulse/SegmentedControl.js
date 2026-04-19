/**
 * SegmentedControl — accessible pill group for toggle-like choices.
 *
 * Renders as `role="radiogroup"` with arrow-key navigation between options.
 * Used for period toggles and sensitivity selectors.
 */
import React, { useRef } from 'react';
import classnames from '@Utils/classnames';

export function SegmentedControl( {
	value,
	options,
	onChange,
	ariaLabel,
	className,
} ) {
	const listRef = useRef( null );

	const handleKeyDown = ( event, index ) => {
		const key = event.key;
		if ( key !== 'ArrowRight' && key !== 'ArrowLeft' && key !== 'Home' && key !== 'End' ) {
			return;
		}
		event.preventDefault();
		const list = listRef.current;
		if ( ! list ) {
			return;
		}
		const buttons = Array.from(
			list.querySelectorAll( 'button[role="radio"]' )
		);
		let nextIndex = index;
		if ( key === 'ArrowRight' ) {
			nextIndex = ( index + 1 ) % buttons.length;
		} else if ( key === 'ArrowLeft' ) {
			nextIndex = ( index - 1 + buttons.length ) % buttons.length;
		} else if ( key === 'Home' ) {
			nextIndex = 0;
		} else if ( key === 'End' ) {
			nextIndex = buttons.length - 1;
		}
		buttons[ nextIndex ]?.focus();
		onChange?.( options[ nextIndex ].value );
	};

	return (
		<div
			ref={ listRef }
			role="radiogroup"
			aria-label={ ariaLabel }
			className={ classnames(
				'inline-flex items-center gap-1 rounded-full border border-solid border-border/80 bg-surface/60 p-1 shadow-xs',
				className
			) }
		>
			{ options.map( ( option, index ) => {
				const selected = option.value === value;
				return (
					<button
						key={ option.value }
						type="button"
						role="radio"
						aria-checked={ selected }
						tabIndex={ selected ? 0 : -1 }
						onClick={ () => onChange?.( option.value ) }
						onKeyDown={ ( e ) => handleKeyDown( e, index ) }
						className={ classnames(
							'relative cursor-pointer rounded-full border-0 px-4 py-1.5 text-sm font-medium transition-all focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse',
							selected
								? 'bg-primary text-primary-foreground shadow-sm'
								: 'bg-transparent text-muted-foreground hover:text-foreground'
						) }
					>
						{ option.label }
					</button>
				);
			} ) }
		</div>
	);
}
