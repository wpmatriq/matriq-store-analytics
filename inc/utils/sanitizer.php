<?php
/**
 * Sanitizer.
 *
 * @package WC_Smart_Analytics
 * @since x.x.x
 */

namespace WC_Smart_Analytics\Inc\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * This class setup all sanitization methods
 *
 * @class Sanitizer
 */
class Sanitizer {
	/**
	 * Sanitize JSON data with support for various data structures.
	 *
	 * @access public
	 *
	 * @param string               $json_data JSON string to sanitize.
	 * @param array<string,string> $field_types Optional array mapping field names to their data types.
	 * @param bool                 $preserve_structure Whether to preserve the original structure or extract specific fields.
	 * @param array<string>        $extract_fields Fields to extract if not preserving structure.
	 *
	 * @since 1.0.0
	 * @return array|mixed Sanitized data.
	 */
	public static function sanitize_json_data( $json_data, $field_types = [], $preserve_structure = true, $extract_fields = [] ) {
		if ( empty( $json_data ) ) {
			return [];
		}

		// First, unslash the data to handle WordPress's automatic escaping.
		$raw_json = wp_unslash( $json_data );

		// Decode the JSON string into a PHP array.
		$decoded_data = json_decode( $raw_json, true );

		// If JSON is invalid, return empty array.
		if ( ! is_array( $decoded_data ) ) {
			return [];
		}

		$sanitized_data = [];

		// Handle array of objects.
		if ( isset( $decoded_data[0] ) && is_array( $decoded_data[0] ) ) {
			foreach ( $decoded_data as $item ) {
				if ( ! is_array( $item ) ) {
					continue;
				}

				if ( $preserve_structure ) {
					$sanitized_item = [];

					foreach ( $item as $key => $value ) {
						$type                   = $field_types[ $key ] ?? 'string';
						$sanitized_item[ $key ] = self::sanitize_by_type( $value, $type );
					}

					$sanitized_data[] = $sanitized_item;
				} else {
					// Extract only specific fields.
					$extracted_item = [];

					foreach ( $extract_fields as $field ) {
						if ( isset( $item[ $field ] ) ) {
							$type                     = $field_types[ $field ] ?? 'string';
							$extracted_item[ $field ] = self::sanitize_by_type( $item[ $field ], $type );
						}
					}

					if ( ! empty( $extracted_item ) ) {
						$sanitized_data[] = $extracted_item;
					}
				}
			}
		} else {
			// Handle simple array of values.
			foreach ( $decoded_data as $key => $value ) {
				$type                   = is_numeric( $key ) ? 'integer' : ( $field_types[ $key ] ?? 'string' );
				$sanitized_data[ $key ] = self::sanitize_by_type( $value, $type );
			}
		}

		return $sanitized_data;
	}

	/**
	 * Settings sanitizer for wc_sma settings.
	 *
	 * @access public
	 *
	 * @param mixed $dataset from AJAX.
	 * @since 1.0.0
	 * @return mixed Sanitized data.
	 */
	public static function sanitize_settings_data( $dataset ) {
		$output = '';

		if ( is_array( $dataset ) ) {
			$output = [];

			foreach ( $dataset as $key => $value ) {
				$datatype = Settings::get_setting_type( $key );

				switch ( $datatype ) {
					case 'html':
						$output[ $key ] = wp_kses_post( $value );
						break;

					case 'array':
						$output[ $key ] = is_array( $value ) ? wc_sma_clean_data( $value ) : [];
						break;

					case 'boolean':
						$output[ $key ] = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
						break;

					case 'integer':
					case 'number':
						$output[ $key ] = absint( $value );
						break;

					case 'email':
						$output[ $key ] = sanitize_email( $value );
						break;

					default:
					case 'string':
						$output[ $key ] = sanitize_text_field( $value );
						break;
				}

				do_action( "wc_sma_sanitize_setting_{$key}", $output[ $key ], $key );
			}
		} else {
			$output = sanitize_text_field( $dataset );
		}

		return $output;
	}

	/**
	 * Sanitize a value based on its type.
	 *
	 * @access private
	 *
	 * @param mixed  $value Value to sanitize.
	 * @param string $type Type of data.
	 *
	 * @since 1.0.0
	 * @return mixed Sanitized value.
	 */
	private static function sanitize_by_type( $value, $type ) {
		switch ( $type ) {
			case 'html':
				return wp_kses_post( $value );

			case 'array':
				return is_array( $value ) ? wc_sma_clean_data( $value ) : [];

			case 'boolean':
				return filter_var( $value, FILTER_VALIDATE_BOOLEAN );

			case 'integer':
			case 'number':
				return absint( $value );

			case 'email':
				return sanitize_email( $value );

			case 'url':
				return esc_url_raw( $value );

			default:
			case 'string':
				return sanitize_text_field( $value );
		}
	}
}
