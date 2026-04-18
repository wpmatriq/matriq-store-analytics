/**
 * Formatting utilities for Sales Pulse dashboard.
 */

/**
 * Format a number as currency using WooCommerce settings.
 *
 * @param {number} value    - Numeric value.
 * @param {string} currency - Currency code (default from localized data).
 * @return {string} Formatted currency string.
 */
export function formatCurrency( value, currency ) {
	const code = currency || window.wc_sma_admin_data?.currency || 'USD';

	return new Intl.NumberFormat( undefined, {
		style: 'currency',
		currency: code,
		minimumFractionDigits: 2,
		maximumFractionDigits: 2,
	} ).format( value || 0 );
}

/**
 * Format a number with locale-aware separators.
 *
 * @param {number} value - Numeric value.
 * @return {string} Formatted number.
 */
export function formatNumber( value ) {
	return new Intl.NumberFormat().format( value || 0 );
}

/**
 * Format a decimal number (1-2 decimal places).
 *
 * @param {number} value - Numeric value.
 * @return {string} Formatted decimal.
 */
export function formatDecimal( value ) {
	return new Intl.NumberFormat( undefined, {
		minimumFractionDigits: 1,
		maximumFractionDigits: 2,
	} ).format( value || 0 );
}

/**
 * Format a percentage change with sign and arrow.
 *
 * @param {number} value - Percentage value (e.g., -12.5).
 * @return {string} Formatted string like "+12.5%" or "-8.3%".
 */
export function formatPercent( value ) {
	if ( ! value && value !== 0 ) {
		return '0%';
	}
	const sign = value > 0 ? '+' : '';
	return `${ sign }${ value.toFixed( 1 ) }%`;
}

/**
 * Format a date string for display.
 *
 * @param {string} dateStr - Date in Y-m-d format.
 * @return {string} Human-readable date.
 */
export function formatDate( dateStr ) {
	if ( ! dateStr ) {
		return '';
	}
	const date = new Date( dateStr + 'T00:00:00' );
	return date.toLocaleDateString( undefined, {
		weekday: 'short',
		month: 'short',
		day: 'numeric',
	} );
}

/**
 * Format a metric card value based on its format type.
 *
 * @param {number} value  - The value.
 * @param {string} format - Format type: 'currency', 'number', 'decimal'.
 * @return {string} Formatted value.
 */
export function formatMetricValue( value, format ) {
	switch ( format ) {
		case 'currency':
			return formatCurrency( value );
		case 'number':
			return formatNumber( value );
		case 'decimal':
			return formatDecimal( value );
		default:
			return String( value );
	}
}
