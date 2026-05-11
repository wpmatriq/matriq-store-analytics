<?php
/**
 * Sales Pulse: Morning Digest - HTML body.
 *
 * Receives `$email` (the DigestEmail object). Read data via `$email->payload`.
 *
 * Inline CSS only. No remote resources. Self-contained so the wp_mail()
 * fallback path renders correctly when WooCommerce isn't active.
 *
 * Locally-scoped variables ($payload, $meta, $card, etc.) are template-only.
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

/**
 * Format a metric_card value.
 *
 * @param mixed  $value
 * @param string $format currency|number|decimal
 * @param string $symbol Currency symbol.
 */
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

$pct_color = static function ( $value ): string {
	$value = (float) $value;
	if ( abs( $value ) < 0.05 ) {
		return '#6b7280'; // muted.
	}
	return $value > 0 ? '#0f9d58' : '#d93025';
};

$sections = [
	'daily'   => __( 'Yesterday', 'sales-pulse' ),
	'weekly'  => __( 'Last 7 days', 'sales-pulse' ),
	'monthly' => __( 'Last 30 days', 'sales-pulse' ),
];

?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>">
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html__( 'Sales Pulse: Morning briefing', 'sales-pulse' ); ?></title>
</head>
<body style="margin:0;padding:0;background:#f7f5f0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;color:#1a1d2e;-webkit-font-smoothing:antialiased;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f7f5f0;">
	<tr>
		<td align="center" style="padding:32px 16px;">
			<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 12px rgba(20,25,45,0.06);">

				<!-- Hero -->
				<tr>
					<td style="padding:32px 32px 16px 32px;border-bottom:1px solid #ece9e0;">
						<div style="font-size:11px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#7c8093;">
							<?php echo esc_html__( 'Morning briefing', 'sales-pulse' ); ?>
						</div>
						<div style="margin-top:6px;font-size:24px;font-weight:600;letter-spacing:-0.01em;color:#1a1d2e;">
							<?php echo esc_html( $friendly_date ); ?>
						</div>
						<div style="margin-top:4px;font-size:13px;color:#7c8093;">
							<?php echo esc_html( $site_name ); ?>
						</div>
						<?php if ( $campaign && ! empty( $campaign['name'] ) ) { ?>
							<div style="margin-top:14px;display:inline-block;background:#fff5e6;color:#a05a00;font-size:11px;font-weight:600;padding:4px 10px;border-radius:999px;">
								<?php
								echo esc_html(
									sprintf(
										/* translators: %s: campaign name. */
										__( 'Campaign: %s', 'sales-pulse' ),
										$campaign['name']
									)
								);
								?>
							</div>
						<?php } ?>
					</td>
				</tr>

				<?php
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
					// Phase 2: Pro plugin enriches the daily section with an AI paragraph
					// and a tailored action via filters; render them only on the daily window.
					$ai_paragraph = $key === 'daily' ? (string) ( $diagnosis['ai_paragraph'] ?? '' ) : '';
					$ai_offline   = ( $key === 'daily' ) && ! empty( $diagnosis['ai_offline'] );
					$ai_action    = $key === 'daily' ? (string) ( $rec['ai_text'] ?? '' ) : '';
					?>
					<tr>
						<td style="padding:24px 32px;border-bottom:1px solid #ece9e0;">
							<div style="font-size:11px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#7c8093;">
								<?php echo esc_html( $label ); ?>
							</div>

							<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top:12px;">
								<tr>
									<td style="vertical-align:middle;">
										<span style="font-size:36px;font-weight:600;letter-spacing:-0.02em;color:<?php echo esc_attr( $pct_color( $pct ) ); ?>;">
											<?php echo esc_html( $fmt_pct( $pct ) ); ?>
										</span>
									</td>
									<td style="vertical-align:baseline;padding-left:14px;">
										<?php if ( $headline !== '' ) { ?>
											<div style="font-size:15px;font-weight:500;color:#1a1d2e;line-height:1.4;">
												<?php echo esc_html( $headline ); ?>
											</div>
										<?php } ?>
										<?php if ( $sub_cause !== '' ) { ?>
											<div style="margin-top:2px;font-size:12px;color:#7c8093;">
												<?php echo esc_html( $sub_cause ); ?>
											</div>
										<?php } ?>
									</td>
								</tr>
							</table>

							<?php if ( $ai_paragraph !== '' ) { ?>
								<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top:18px;">
									<tr>
										<td style="border-left:3px solid #6366f1;padding:10px 0 10px 14px;background:transparent;">
											<div style="font-size:11px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#6366f1;">
												✦ <?php echo esc_html__( 'Copilot · Why this happened', 'sales-pulse' ); ?>
											</div>
											<div style="margin-top:6px;font-size:14px;color:#1a1d2e;line-height:1.55;">
												<?php echo esc_html( $ai_paragraph ); ?>
											</div>
										</td>
									</tr>
								</table>
							<?php } elseif ( $ai_offline ) { ?>
								<div style="margin-top:14px;display:inline-block;font-size:11px;color:#7c8093;background:#f7f5f0;border:1px solid #ece9e0;border-radius:999px;padding:4px 10px;">
									<?php echo esc_html__( 'AI insights paused', 'sales-pulse' ); ?>
								</div>
							<?php } ?>

							<?php if ( $cards ) { ?>
								<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top:18px;border-collapse:separate;border-spacing:8px 0;">
									<tr>
										<?php
										foreach ( $cards as $card ) {
											$card_value  = $fmt_value( $card['current'] ?? 0, (string) ( $card['format'] ?? 'number' ), $currency_symbol );
											$card_change = (float) ( $card['change'] ?? 0 );
											?>
											<td width="25%" style="background:#f7f5f0;border-radius:10px;padding:12px;vertical-align:top;">
												<div style="font-size:10px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#7c8093;">
													<?php echo esc_html( (string) ( $card['label'] ?? '' ) ); ?>
												</div>
												<div style="margin-top:6px;font-size:16px;font-weight:600;color:#1a1d2e;">
													<?php echo esc_html( $card_value ); ?>
												</div>
												<div style="margin-top:2px;font-size:11px;color:<?php echo esc_attr( $pct_color( $card_change ) ); ?>;">
													<?php echo esc_html( $fmt_pct( $card_change ) ); ?>
												</div>
											</td>
										<?php } ?>
									</tr>
								</table>
							<?php } ?>

							<?php if ( $rec_text !== '' ) { ?>
								<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top:18px;">
									<tr>
										<td style="border-left:3px solid #0f9d58;padding:8px 0 8px 14px;background:transparent;">
											<div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#0f9d58;">
												<?php echo esc_html__( 'Suggested action', 'sales-pulse' ); ?>
											</div>
											<div style="margin-top:4px;font-size:14px;color:#1a1d2e;line-height:1.5;">
												<?php echo esc_html( $rec_text ); ?>
											</div>
										</td>
									</tr>
								</table>
							<?php } ?>

							<?php if ( $ai_action !== '' ) { ?>
								<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top:14px;">
									<tr>
										<td style="border-left:3px solid #6366f1;padding:8px 0 8px 14px;background:transparent;">
											<div style="font-size:11px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#6366f1;">
												✦ <?php echo esc_html__( 'Copilot · AI alternative', 'sales-pulse' ); ?>
											</div>
											<div style="margin-top:4px;font-size:14px;color:#1a1d2e;line-height:1.5;">
												<?php echo esc_html( $ai_action ); ?>
											</div>
										</td>
									</tr>
								</table>
							<?php } ?>
						</td>
					</tr>
				<?php } ?>

				<!-- CTA -->
				<tr>
					<td align="center" style="padding:28px 32px 32px 32px;">
						<a href="<?php echo esc_url( $dashboard_url ); ?>" style="display:inline-block;background:#1a1d2e;color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;padding:12px 24px;border-radius:999px;">
							<?php echo esc_html__( 'Open Sales Pulse dashboard', 'sales-pulse' ); ?>
						</a>
					</td>
				</tr>

				<!-- Footer -->
				<tr>
					<td style="padding:20px 32px;background:#f7f5f0;border-top:1px solid #ece9e0;">
						<div style="font-size:11px;color:#7c8093;line-height:1.5;">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: site name. */
									__( 'Sent by Sales Pulse on behalf of %s.', 'sales-pulse' ),
									$site_name
								)
							);
							?>
							<br>
							<?php
							echo wp_kses(
								sprintf(
									/* translators: %s: settings page URL. */
									__( 'Manage your morning digest in <a href="%s" style="color:#1a1d2e;">Sales Pulse Settings</a>.', 'sales-pulse' ),
									esc_url( admin_url( 'admin.php?page=sales-pulse&tab=settings' ) )
								),
								[
									'a' => [
										'href'  => [],
										'style' => [],
									],
								]
							);
							?>
						</div>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
</html>
