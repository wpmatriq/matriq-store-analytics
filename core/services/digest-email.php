<?php
/**
 * Digest WC_Email subclass.
 *
 * Subclasses WC_Email so the digest renders in WooCommerce's standard email
 * shell (header, footer, brand color, inline CSS) and shows up in
 * WooCommerce -> Settings -> Emails for discoverability.
 *
 * The settings form on the WC tab is intentionally read-only - configuration
 * (toggle + recipient) lives in Matriq Store Analytics Settings to keep a single source
 * of truth.
 *
 * Triggered exclusively by DigestMailer; not wired to any WC action hook.
 *
 * @package Matriq\MSA\Core\Services
 */

namespace Matriq\MSA\Core\Services;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\\WC_Email' ) ) {
	return;
}

/**
 * WC_Email subclass for the morning-briefing digest. Hooked into the
 * WooCommerce email manager so merchants can configure subject/template
 * via the standard WC Settings → Emails surface.
 */
class DigestEmail extends \WC_Email {
	/**
	 * Payload assembled by DigestMailer; available to templates as `$email->payload`.
	 *
	 * @var array<string, mixed>
	 */
	public $payload = [];

	/**
	 * Wire up the WC_Email metadata used by the WC mailer registry.
	 */
	public function __construct() {
		$this->id             = 'matriq_msa_morning_digest';
		$this->customer_email = false;
		$this->title          = __( 'Matriq Store Analytics: Morning digest', 'matriq-store-analytics' );
		$this->description    = __(
			'Daily morning briefing combining yesterday, last 7 days, and last 30 days insights. Configured in Matriq Store Analytics Settings.',
			'matriq-store-analytics'
		);
		$this->heading        = __( 'Your morning briefing', 'matriq-store-analytics' );

		$this->template_base  = MATRIQ_MSA_DIR;
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

	/**
	 * From-name override applied to the digest email.
	 *
	 * @param string $from_name Default WC value (unused; we always provide our own name).
	 * @return string
	 */
	public function get_from_name( $from_name = '' ) {
		return apply_filters( 'matriq_msa_digest_from_name', 'Matriq Store Analytics', $this );
	}

	/**
	 * From-address override applied to the digest email. Defaults to the
	 * site admin_email option; overridable via the matriq_msa filter.
	 *
	 * @param string $from_email Default WC value (unused; we always provide our own address).
	 * @return string
	 */
	public function get_from_address( $from_email = '' ) {
		$default = (string) get_option( 'admin_email', '' );
		return apply_filters( 'matriq_msa_digest_from_address', $default, $this );
	}

	/**
	 * Render the HTML template into a string.
	 *
	 * @return string
	 */
	public function get_content_html() {
		ob_start();
		// Bind $email so the included template can read $email->payload.
		$email = $this; // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- consumed by included template.
		include $this->template_base . $this->template_html;
		return (string) ob_get_clean();
	}

	/**
	 * Render the plain-text template into a string.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		ob_start();
		$email = $this; // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- consumed by included template.
		include $this->template_base . $this->template_plain;
		return (string) ob_get_clean();
	}

	/**
	 * No editable fields. Render a notice instead with a deep link to our Settings page.
	 *
	 * @return void
	 */
	public function init_form_fields(): void {
		$settings_url = admin_url( 'admin.php?page=matriq-store-analytics&tab=settings' );

		$this->form_fields = [
			'manage_link' => [
				'title'       => __( 'Configuration', 'matriq-store-analytics' ),
				'type'        => 'title',
				'description' => sprintf(
					/* translators: %s: link to Matriq Store Analytics Settings page. */
					__(
						'This email is configured in Matriq Store Analytics Settings, not here. <a href="%s">Open Matriq Store Analytics Settings</a>.',
						'matriq-store-analytics'
					),
					esc_url( $settings_url )
				),
			],
		];
	}
}
