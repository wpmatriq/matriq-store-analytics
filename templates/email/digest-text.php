<?php
/**
 * Sales Pulse: Morning Digest - plain-text body.
 *
 * @var \EC_Sales_Pulse\Core\Services\DigestEmail $email
 */

defined( 'ABSPATH' ) || exit;

// Guard against include paths that don't seed `$email` (some WC admin preview flows re-render templates without the parent context).
$payload = ( isset( $email ) && is_object( $email ) && isset( $email->payload ) ) ? (array) $email->payload : [];
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

echo strtoupper( __( 'Sales Pulse - Morning briefing', 'sales-pulse' ) ) . "\n";
echo str_repeat( '=', 60 ) . "\n";
echo $friendly_date . "\n";
echo $site_name . "\n";
if ( $campaign && ! empty( $campaign['name'] ) ) {
	echo sprintf( __( 'Campaign: %s', 'sales-pulse' ), $campaign['name'] ) . "\n";
}
echo "\n";

foreach ( $sections as $key => $label ) {
	$section = isset( $payload[ $key ] ) ? (array) $payload[ $key ] : [];
	if ( ! $section ) {
		continue;
	}

	$diagnosis    = (array) ( $section['diagnosis'] ?? [] );
	$rec          = (array) ( $section['recommendation'] ?? [] );
	$cards        = (array) ( $section['metric_cards'] ?? [] );
	$pct          = (float) ( $diagnosis['revenue_change_percent'] ?? 0 );
	$headline     = (string) ( $diagnosis['headline'] ?? '' );
	$sub_cause    = (string) ( $diagnosis['confidence_label'] ?? '' );
	$rec_text     = (string) ( $rec['recommendation'] ?? '' );
	// Phase 2: Pro AI fields are emitted only on the daily window.
	$ai_paragraph = ( $key === 'daily' ) ? (string) ( $diagnosis['ai_paragraph'] ?? '' ) : '';
	$ai_offline   = ( $key === 'daily' ) && ! empty( $diagnosis['ai_offline'] );
	$ai_action    = ( $key === 'daily' ) ? (string) ( $rec['ai_text'] ?? '' ) : '';

	echo $label . "\n";
	echo str_repeat( '-', 60 ) . "\n";
	echo $fmt_pct( $pct ) . '   ' . $headline . "\n";
	if ( $sub_cause !== '' ) {
		echo $sub_cause . "\n";
	}
	echo "\n";

	if ( $ai_paragraph !== '' ) {
		echo __( 'COPILOT · WHY THIS HAPPENED', 'sales-pulse' ) . "\n";
		echo '  ' . $ai_paragraph . "\n\n";
	} elseif ( $ai_offline ) {
		echo '  ' . __( '(AI insights paused)', 'sales-pulse' ) . "\n\n";
	}

	if ( $cards ) {
		foreach ( $cards as $card ) {
			$lbl    = (string) ( $card['label'] ?? '' );
			$val    = $fmt_value( $card['current'] ?? 0, (string) ( $card['format'] ?? 'number' ), $currency_symbol );
			$change = $fmt_pct( (float) ( $card['change'] ?? 0 ) );
			echo sprintf( "  %-18s %15s   (%s)\n", $lbl, $val, $change );
		}
		echo "\n";
	}

	if ( $rec_text !== '' ) {
		echo __( 'Suggested action:', 'sales-pulse' ) . "\n";
		echo '  ' . $rec_text . "\n\n";
	}

	if ( $ai_action !== '' ) {
		echo __( 'COPILOT · AI alternative', 'sales-pulse' ) . "\n";
		echo '  ' . $ai_action . "\n\n";
	}
}

echo str_repeat( '=', 60 ) . "\n";
echo __( 'Open Sales Pulse dashboard:', 'sales-pulse' ) . "\n";
echo $dashboard_url . "\n\n";
echo sprintf( __( 'Sent by Sales Pulse on behalf of %s.', 'sales-pulse' ), $site_name ) . "\n";
echo __( 'Manage your morning digest in Sales Pulse Settings.', 'sales-pulse' ) . "\n";
