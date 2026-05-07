/**
 * Status + severity mapping helpers.
 *
 * Central source of truth for translating backend `direction` / `severity`
 * values into UI labels, icons, and color tints used by BriefingHero,
 * HistoryRow, StatusBadge, and MetricCard.
 */
import { __ } from '@wordpress/i18n';
import { Minus, TrendingDown, TrendingUp } from 'lucide-react';

/**
 * Derive a trend direction from a signed percentage change.
 *
 * @param {number} changePct Signed percentage change.
 * @return {'up'|'down'|'flat'} Trend direction.
 */
export function directionFromChange( changePct ) {
	if ( changePct > 0.5 ) {
		return 'up';
	}
	if ( changePct < -0.5 ) {
		return 'down';
	}
	return 'flat';
}

/**
 * Icon component for a trend direction.
 *
 * @param {'up'|'down'|'flat'|'growth'|'decline'|'stable'} direction Direction signal.
 * @return {import('lucide-react').LucideIcon} Lucide icon component to render.
 */
export function trendIcon( direction ) {
	if ( direction === 'up' || direction === 'growth' ) {
		return TrendingUp;
	}
	if ( direction === 'down' || direction === 'decline' ) {
		return TrendingDown;
	}
	return Minus;
}

/**
 * Tailwind color classes for a trend direction.
 *
 * @param {'up'|'down'|'flat'|'growth'|'decline'|'stable'} direction Direction signal.
 * @return {string} Tailwind text-color class for the matching tone.
 */
export function trendTextColor( direction ) {
	if ( direction === 'up' || direction === 'growth' ) {
		return 'text-success';
	}
	if ( direction === 'down' || direction === 'decline' ) {
		return 'text-destructive';
	}
	return 'text-muted-foreground';
}

/**
 * Map backend severity to UI status label (human-readable).
 *
 * @param {'success'|'warning'|'info'|'danger'} severity Backend severity code.
 * @return {string} Translated, merchant-facing label.
 */
export function statusLabel( severity ) {
	switch ( severity ) {
		case 'success':
			return __( 'Surge', 'sales-pulse' );
		case 'warning':
		case 'danger':
			return __( 'Needs Attention', 'sales-pulse' );
		case 'info':
		default:
			return __( 'Stable', 'sales-pulse' );
	}
}

/**
 * Map backend severity to the visual variant used by StatusBadge.
 *
 * @param {'success'|'warning'|'info'|'danger'} severity Backend severity code.
 * @return {'stable'|'surge'|'attention'} StatusBadge variant slug for tone styling.
 */
export function statusVariant( severity ) {
	if ( severity === 'success' ) {
		return 'surge';
	}
	if ( severity === 'warning' || severity === 'danger' ) {
		return 'attention';
	}
	return 'stable';
}
