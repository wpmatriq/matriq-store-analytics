/**
 * OptionCard — selectable tile used in groups (Campaign goals, Revenue basis,
 * Diagnosis sensitivity).
 *
 * Renders as `role="radio"`. Parent container should provide the radiogroup
 * wrapper (`OptionCardGroup`) so keyboard navigation is scoped correctly.
 */
import React, { useCallback } from 'react';
import classnames from '@Utils/classnames';

export function OptionCard( {
	selected = false,
	title,
	description,
	icon,
	onSelect,
	disabled = false,
	className,
} ) {
	const handleKeyDown = useCallback(
		( event ) => {
			if ( event.key === ' ' || event.key === 'Enter' ) {
				event.preventDefault();
				onSelect?.();
			}
		},
		[ onSelect ]
	);

	return (
		<button
			type="button"
			role="radio"
			aria-checked={ selected }
			tabIndex={ selected ? 0 : -1 }
			disabled={ disabled }
			onClick={ () => ! disabled && onSelect?.() }
			onKeyDown={ handleKeyDown }
			className={ classnames(
				'group relative flex w-full cursor-pointer flex-col items-start gap-1 rounded-xl border border-solid bg-surface px-4 py-3 text-left transition-all focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse',
				selected
					? 'border-pulse bg-pulse/5 shadow-sm ring-1 ring-pulse/30'
					: 'border-border hover:border-border/80 hover:bg-surface-elevated',
				disabled && 'cursor-not-allowed opacity-60',
				className
			) }
		>
			<div className="flex w-full items-center gap-2">
				{ icon && (
					<span
						className={ classnames(
							'flex h-5 w-5 items-center justify-center rounded-full border-2 border-solid',
							selected ? 'border-pulse bg-pulse' : 'border-border bg-transparent'
						) }
					>
						{ selected && (
							<span className="h-1.5 w-1.5 rounded-full bg-primary-foreground" />
						) }
					</span>
				) }
				<span className="font-semibold text-foreground">{ title }</span>
			</div>
			{ description && (
				<span className="text-sm text-muted-foreground">{ description }</span>
			) }
		</button>
	);
}

/**
 * OptionCardGroup — radiogroup wrapper with arrow-key navigation.
 *
 * @param {Object}   props
 * @param {string}   [props.label]
 * @param {string}   props.value
 * @param {Function} props.onChange
 * @param {Array}    props.options  `{ value, title, description }`.
 * @param {string}   [props.gridClassName]
 */
export function OptionCardGroup( {
	label,
	value,
	onChange,
	options,
	gridClassName = 'grid grid-cols-1 gap-3 md:grid-cols-2',
} ) {
	const wrapperRef = React.useRef( null );

	const handleKeyDown = ( event, index ) => {
		const key = event.key;
		if ( key !== 'ArrowRight' && key !== 'ArrowLeft' && key !== 'ArrowUp' && key !== 'ArrowDown' ) {
			return;
		}
		event.preventDefault();
		const buttons = Array.from(
			wrapperRef.current?.querySelectorAll( 'button[role="radio"]' ) || []
		);
		let nextIndex = index;
		if ( key === 'ArrowRight' || key === 'ArrowDown' ) {
			nextIndex = ( index + 1 ) % buttons.length;
		} else {
			nextIndex = ( index - 1 + buttons.length ) % buttons.length;
		}
		buttons[ nextIndex ]?.focus();
		onChange?.( options[ nextIndex ].value );
	};

	return (
		<div ref={ wrapperRef } role="radiogroup" aria-label={ label }>
			<div className={ gridClassName }>
				{ options.map( ( option, index ) => (
					<div
						key={ option.value }
						onKeyDown={ ( e ) => handleKeyDown( e, index ) }
					>
						<OptionCard
							selected={ option.value === value }
							title={ option.title }
							description={ option.description }
							icon={ option.icon ?? true }
							onSelect={ () => onChange?.( option.value ) }
						/>
					</div>
				) ) }
			</div>
		</div>
	);
}
