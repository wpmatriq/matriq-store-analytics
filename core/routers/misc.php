<?php
/**
 * Misc Router Initialize.
 *
 * @package WC_Smart_Analytics
 */

namespace WC_Smart_Analytics\Core\Routers;

use WC_Smart_Analytics\Core\Models\Controller;
use WC_Smart_Analytics\Inc\Traits\Get_Instance;
use WC_Smart_Analytics\Inc\Traits\Rest_Errors;
use WC_Smart_Analytics\Inc\Utils\Helper;
use WC_Smart_Analytics\Inc\Utils\Sanitizer;

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

		wp_send_json_success( [ 'message' => __( 'Thank you for your feedback!', 'wc-smart-analytics' ) ] );
	}
}
