/**
 * StatusBadge - pill indicator for diagnosis state.
 *
 * Variants map to backend severity semantics:
 *   stable     → "Stable"         (info / no meaningful change)
 *   surge      → "Surge"          (success / growth)
 *   attention  → "Needs Attention"(warning / danger)
 *
 * Consumers should supply `variant` directly when they have explicit UI state
 * (e.g. History row), or use `severity` + `statusVariant` from `statusMap.js`.
 */
import React from 'react';
import { __ } from '@wordpress/i18n';
import classnames from '@Utils/classnames';

const VARIANT_STYLES = {
	stable: 'border-border bg-surface text-muted-foreground',
	surge: 'border-success/30 bg-success/10 text-success-foreground',
	attention: 'border-warning/40 bg-warning/15 text-warning-foreground',
};

function defaultLabel( variant ) {
	switch ( variant ) {
		case 'surge':
			return __( 'Surge', 'matriq-store-analytics' );
		case 'attention':
			return __( 'Needs Attention', 'matriq-store-analytics' );
		case 'stable':
		default:
			return __( 'Stable', 'matriq-store-analytics' );
	}
}

export function StatusBadge( { variant = 'stable', label, className } ) {
	return (
		<span
			className={ classnames(
				'inline-flex items-center gap-1.5 rounded-full border border-solid px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wider',
				VARIANT_STYLES[ variant ] || VARIANT_STYLES.stable,
				className
			) }
		>
			<span className="h-1.5 w-1.5 rounded-full bg-current opacity-70" />
			{ label || defaultLabel( variant ) }
		</span>
	);
}
