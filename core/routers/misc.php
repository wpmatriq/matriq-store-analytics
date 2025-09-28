<?php
/**
 * Misc Router Initialize.
 *
 * @package EC_Sales_Pulse
 */

namespace EC_Sales_Pulse\Core\Routers;

use EC_Sales_Pulse\Core\Models\Controller;
use EC_Sales_Pulse\Inc\Traits\Get_Instance;
use EC_Sales_Pulse\Inc\Traits\Rest_Errors;
use EC_Sales_Pulse\Inc\Utils\Helper;
use EC_Sales_Pulse\Inc\Utils\Sanitizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Misc Router.
 */
class Misc {
	use Get_Instance;
	use Rest_Errors;

	/**
	 * Handler to get topic submitted.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @since x.x.x
	 * @return void
	 */
	public function submit_topic( $request ): void {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wp_rest' ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'nonce' ) ] );
		}

		$submitted_data = ! empty( $_POST['formData'] ) ? json_decode( wp_unslash( $_POST['formData'] ), true ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data is sanitized in the Sanitizer::sanitize_meta_data() method.

		if ( empty( $submitted_data ) ) {
			wp_send_json_error( [ 'message' => $this->get_rest_event_error( 'default' ) ] );
		}

		wp_send_json_success( [ 'message' => __( 'Thank you for your feedback!', 'sales-pulse' ) ] );
	}
}
