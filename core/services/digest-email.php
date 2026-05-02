<?php
/**
 * Digest WC_Email subclass.
 *
 * Subclasses WC_Email so the digest renders in WooCommerce's standard email
 * shell (header, footer, brand color, inline CSS) and shows up in
 * WooCommerce -> Settings -> Emails for discoverability.
 *
 * The settings form on the WC tab is intentionally read-only - configuration
 * (toggle + recipient) lives in Sales Pulse Settings to keep a single source
 * of truth.
 *
 * Triggered exclusively by DigestMailer; not wired to any WC action hook.
 *
 * @package EC_Sales_Pulse\Core\Services
 */

namespace EC_Sales_Pulse\Core\Services;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\\WC_Email' ) ) {
	return;
}

class DigestEmail extends \WC_Email {

	/**
	 * Payload assembled by DigestMailer; available to templates as `$email->payload`.
	 *
	 * @var array<string, mixed>
	 */
	public $payload = [];

	public function __construct() {
		$this->id             = 'salespulse_morning_digest';
		$this->customer_email = false;
		$this->title          = __( 'Sales Pulse: Morning digest', 'sales-pulse' );
		$this->description    = __(
			'Daily morning briefing combining yesterday, last 7 days, and last 30 days insights. Configured in Sales Pulse Settings.',
			'sales-pulse'
		);
		$this->heading        = __( 'Your morning briefing', 'sales-pulse' );

		$this->template_base  = EC_Sales_Pulse_DIR;
		$this->template_html  = 'templates/email/digest-html.php';
		$this->template_plain = 'templates/email/digest-text.php';

		$this->email_type = 'multipart';

		parent::__construct();

		// Always considered enabled at the WC layer - DigestMailer is the real gate.
		$this->enabled = 'yes';
		$this->manual  = false;
	}

	/**
	 * Triggered by DigestMailer. Renders templates and sends in one call.
	 *
	 * @param string               $recipient Validated recipient address.
	 * @param string               $subject   Final subject line.
	 * @param array<string, mixed> $payload   Data payload for the templates.
	 */
	public function trigger_digest( string $recipient, string $subject, array $payload ): bool {
		$this->payload = $payload;

		return (bool) $this->send(
			$recipient,
			$subject,
			$this->get_content(),
			$this->get_headers(),
			$this->get_attachments()
		);
	}

	public function get_from_name() {
		return apply_filters( 'salespulse_digest_from_name', 'Sales Pulse', $this );
	}

	public function get_from_address() {
		$default = (string) get_option( 'admin_email', '' );
		return apply_filters( 'salespulse_digest_from_address', $default, $this );
	}

	public function get_content_html() {
		ob_start();
		// Templates use `$email` (this object) and `$email->payload`.
		$email = $this;
		include $this->template_base . $this->template_html;
		return (string) ob_get_clean();
	}

	public function get_content_plain() {
		ob_start();
		$email = $this;
		include $this->template_base . $this->template_plain;
		return (string) ob_get_clean();
	}

	/**
	 * No editable fields. Render a notice instead with a deep link to our Settings page.
	 */
	public function init_form_fields() {
		$settings_url = admin_url( 'admin.php?page=sales-pulse&tab=settings' );

		$this->form_fields = [
			'manage_link' => [
				'title'       => __( 'Configuration', 'sales-pulse' ),
				'type'        => 'title',
				'description' => sprintf(
					/* translators: %s: link to Sales Pulse Settings page. */
					__(
						'This email is configured in Sales Pulse Settings, not here. <a href="%s">Open Sales Pulse Settings</a>.',
						'sales-pulse'
					),
					esc_url( $settings_url )
				),
			],
		];
	}
}
