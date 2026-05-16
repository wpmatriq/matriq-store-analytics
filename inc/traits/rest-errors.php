<?php
/**
 * All REST related actions.
 *
 * @package Matriq\MSA
 * @since 0.0.2
 */

namespace Matriq\MSA\Inc\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Trait Ajax.
 *
 * @since 0.0.2
 */
trait Rest_Errors {
	/**
	 * Errors
	 *
	 * @access private
	 * @var array<string, string> Errors strings.
	 * @since 0.0.2
	 */
	public $errors = [];

	/**
	 * Creates an array of default ajax action related error messages.
	 *
	 * @since 0.0.2
	 * @return void
	 */
	public function set_rest_event_errors(): void {
		$this->errors = [
			'permission'        => __( 'Sorry, you are not allowed to do this operation.', 'matriq-store-analytics' ),
			'nonce'             => __( 'Nonce validation failed', 'matriq-store-analytics' ),
			'default'           => __( 'Sorry, something went wrong.', 'matriq-store-analytics' ),
			'missing_key'       => __( 'Oops, the required key is missing.', 'matriq-store-analytics' ),
			'invalid_post_type' => __( 'The current post\'s post type is not of this plugin.', 'matriq-store-analytics' ),
			'success'           => __( 'Data saved successfully.', 'matriq-store-analytics' ),
		];
	}

	/**
	 * Get error message.
	 *
	 * @param string $type Message type.
	 * @return string
	 * @since 0.0.2
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
