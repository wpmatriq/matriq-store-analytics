<?php
/**
 * Sales Pulse: Morning Digest - plain-text body.
 *
 * Locally-scoped variables ($payload, $meta, $section, etc.) are template-only.
 * The PrefixAllGlobals sniff false-flags them because templates run in WC's
 * Email scope; they are not global.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 *
 * @package EC_Sales_Pulse\Templates
 *
 * @var \EC_Sales_Pulse\Core\Services\DigestEmail $email
 */

defined( 'ABSPATH' ) || exit;

// Guard against include paths that don't seed `$email` (some WC admin preview flows re-render templates without the parent context).
$payload = isset( $email ) && is_object( $email ) && isset( $email->payload ) ? (array) $email->payload : [];
$meta    = isset( $payload['meta'] ) ? (array) $payload['meta'] : [];

$site_name       = (string) ( $meta['site_name'] ?? get_bloginfo( 'name' ) );
$currency_symbol = (string) ( $meta['currency_symbol'] ?? '$' );
$dashboard_url   = (string) ( $meta['dashboard_url'] ?? admin_url( 'admin.php?page=sales-pulse' ) );
$friendly_date   = '';
if ( ! empty( $meta['date'] ) ) {
	try {
		$d             = new \DateTime( (string) $meta['date'], wp_timezone() );
		$friendly_date = wp_date( 'l, F j', $d->getTimestamp() );
	} catch ( \Exception $e ) {
		$friendly_date = (string) $meta['date'];
	}
}
$campaign = isset( $meta['campaign'] ) && is_array( $meta['campaign'] ) ? $meta['campaign'] : null;

$fmt_value = static function ( $value, string $format, string $symbol ): string {
	$value = (float) $value;
	if ( $format === 'currency' ) {
		return $symbol . number_format_i18n( $value, 2 );
	}
	if ( $format === 'decimal' ) {
		return number_format_i18n( $value, 1 );
	}
	return number_format_i18n( $value, 0 );
};

$fmt_pct = static function ( $value ): string {
	$value = (float) $value;
	if ( abs( $value ) < 0.05 ) {
		return '0%';
	}
	$prefix = $value > 0 ? '+' : '';
	$digits = abs( $value ) >= 10 ? 0 : 1;
	return $prefix . number_format_i18n( $value, $digits ) . '%';
};

$sections = [
	'daily'   => __( 'YESTERDAY', 'sales-pulse' ),
	'weekly'  => __( 'LAST 7 DAYS', 'sales-pulse' ),
	'monthly' => __( 'LAST 30 DAYS', 'sales-pulse' ),
];

echo esc_html( strtoupper( __( 'Sales Pulse - Morning briefing', 'sales-pulse' ) ) ) . "\n";
echo esc_html( str_repeat( '=', 60 ) ) . "\n";
echo esc_html( $friendly_date ) . "\n";
echo esc_html( $site_name ) . "\n";
if ( $campaign && ! empty( $campaign['name'] ) ) {
	echo esc_html(
		sprintf(
			/* translators: %s: campaign name. */
			__( 'Campaign: %s', 'sales-pulse' ),
			$campaign['name']
		)
	) . "\n";
}
echo "\n";

foreach ( $sections as $key => $label ) {
	$section = isset( $payload[ $key ] ) ? (array) $payload[ $key ] : [];
	if ( ! $section ) {
		continue;
	}

	$diagnosis = (array) ( $section['diagnosis'] ?? [] );
	$rec       = (array) ( $section['recommendation'] ?? [] );
	$cards     = (array) ( $section['metric_cards'] ?? [] );
	$pct       = (float) ( $diagnosis['revenue_change_percent'] ?? 0 );
	$headline  = (string) ( $diagnosis['headline'] ?? '' );
	$sub_cause = (string) ( $diagnosis['confidence_label'] ?? '' );
	$rec_text  = (string) ( $rec['recommendation'] ?? '' );
	// Phase 2: Pro AI fields are emitted only on the daily window.
	$ai_paragraph = $key === 'daily' ? (string) ( $diagnosis['ai_paragraph'] ?? '' ) : '';
	$ai_offline   = ( $key === 'daily' ) && ! empty( $diagnosis['ai_offline'] );
	$ai_action    = $key === 'daily' ? (string) ( $rec['ai_text'] ?? '' ) : '';

	echo esc_html( $label ) . "\n";
	echo esc_html( str_repeat( '-', 60 ) ) . "\n";
	echo esc_html( $fmt_pct( $pct ) . '   ' . $headline ) . "\n";
	if ( $sub_cause !== '' ) {
		echo esc_html( $sub_cause ) . "\n";
	}
	echo "\n";

	if ( $ai_paragraph !== '' ) {
		echo esc_html__( 'COPILOT · WHY THIS HAPPENED', 'sales-pulse' ) . "\n";
		echo '  ' . esc_html( $ai_paragraph ) . "\n\n";
	} elseif ( $ai_offline ) {
		echo '  ' . esc_html__( '(AI insights paused)', 'sales-pulse' ) . "\n\n";
	}

	if ( $cards ) {
		foreach ( $cards as $card ) {
			$lbl    = (string) ( $card['label'] ?? '' );
			$val    = $fmt_value( $card['current'] ?? 0, (string) ( $card['format'] ?? 'number' ), $currency_symbol );
			$change = $fmt_pct( (float) ( $card['change'] ?? 0 ) );
			echo esc_html( sprintf( '  %-18s %15s   (%s)', $lbl, $val, $change ) ) . "\n";
		}
		echo "\n";
	}

	if ( $rec_text !== '' ) {
		echo esc_html__( 'Suggested action:', 'sales-pulse' ) . "\n";
		echo '  ' . esc_html( $rec_text ) . "\n\n";
	}

	if ( $ai_action !== '' ) {
		echo esc_html__( 'COPILOT · AI alternative', 'sales-pulse' ) . "\n";
		echo '  ' . esc_html( $ai_action ) . "\n\n";
	}
}

echo esc_html( str_repeat( '=', 60 ) ) . "\n";
echo esc_html__( 'Open Sales Pulse dashboard:', 'sales-pulse' ) . "\n";
echo esc_url( $dashboard_url ) . "\n\n";
echo esc_html(
	sprintf(
		/* translators: %s: site name. */
		__( 'Sent by Sales Pulse on behalf of %s.', 'sales-pulse' ),
		$site_name
	)
) . "\n";
echo esc_html__( 'Manage your morning digest in Sales Pulse Settings.', 'sales-pulse' ) . "\n";
