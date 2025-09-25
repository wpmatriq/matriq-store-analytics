<?php
/**
 * All REST related actions.
 *
 * @package WC_Smart_Analytics
 * @since x.x.x
 */

namespace WC_Smart_Analytics\Inc\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Trait Ajax.
 *
 * @since x.x.x
 */
trait Rest_Errors {
	/**
	 * Errors
	 *
	 * @access private
	 * @var array<string, string> Errors strings.
	 * @since x.x.x
	 */
	public $errors = [];

	/**
	 * Creates an array of default ajax action related error messages.
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function set_rest_event_errors(): void {
		$this->errors = [
			'permission'        => __( 'Sorry, you are not allowed to do this operation.', 'wc-smart-analytics' ),
			'nonce'             => __( 'Nonce validation failed', 'wc-smart-analytics' ),
			'default'           => __( 'Sorry, something went wrong.', 'wc-smart-analytics' ),
			'missing_key'       => __( 'Oops, the required key is missing.', 'wc-smart-analytics' ),
			'invalid_post_type' => __( 'The current post\'s post type is not of this plugin.', 'wc-smart-analytics' ),
			'success'           => __( 'Data saved successfully.', 'wc-smart-analytics' ),
		];
	}

	/**
	 * Get error message.
	 *
	 * @param string $type Message type.
	 * @return string
	 * @since x.x.x
	 */
	public function get_rest_event_error( $type ) {

		if ( empty( $this->errors ) ) {
			$this->set_rest_event_errors();
		}

		if ( ! isset( $this->errors[ $type ] ) ) {
			$type = 'default';
		}

		return $this->errors[ $type ];
	}
}
