<?php
/**
 * Digest Controller.
 *
 * REST endpoint for the "Send test digest" button in Sales Pulse Settings.
 * Bypasses the once-per-day idempotency guard but still requires the toggle
 * to be enabled. Rate-limited to 1 request per minute per user.
 *
 * @package EC_Sales_Pulse\Core\Controllers
 */

namespace EC_Sales_Pulse\Core\Controllers;

use EC_Sales_Pulse\Core\Services\DigestMailer;

defined( 'ABSPATH' ) || exit;

class DigestController extends BaseController {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'system/digest';

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/test',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'send_test' ],
				'permission_callback' => [ $this, 'admin_permission_check' ],
				'args'                => [
					'recipient' => [
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_email',
					],
				],
			]
		);
	}

	/**
	 * Send a one-off test digest.
	 *
	 * @param \WP_REST_Request $request Request object.
	 */
	public function send_test( \WP_REST_Request $request ): \WP_REST_Response {
		$user_id        = get_current_user_id();
		$rate_limit_key = 'salespulse_digest_test_' . $user_id;

		if ( get_transient( $rate_limit_key ) ) {
			return $this->error(
				__( 'You just sent a test. Try again in a few seconds.', 'sales-pulse' ),
				429
			);
		}

		if ( ! SettingsController::get( 'email_enabled' ) ) {
			return $this->error(
				__( 'Enable the email digest toggle before sending a test.', 'sales-pulse' ),
				409
			);
		}

		$override = $request->get_param( 'recipient' );
		$override = is_string( $override ) && $override !== '' ? $override : null;

		set_transient( $rate_limit_key, 1, MINUTE_IN_SECONDS );

		$result = DigestMailer::get_instance()->send( $override, true );

		if ( empty( $result['sent'] ) ) {
			return $this->error(
				(string) ( $result['reason'] ?? __( 'Could not send the test email.', 'sales-pulse' ) ),
				502
			);
		}

		return $this->success( $result );
	}
}
