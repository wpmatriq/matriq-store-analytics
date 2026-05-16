<?php
/**
 * Plugin functions.
 *
 * @package Matriq\MSA
 * @since 0.0.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if pro version is active.
 *
 * @return bool
 * @since 0.0.2
 */
function matriq_msa_is_pro_active() {
	return defined( 'MATRIQ_MSA_PRO_VER' );
}

/**
 * Clean variables using sanitize_text_field.
 *
 * @param mixed $var Data to sanitize.
 * @return mixed
 *
 * @since 0.0.2
 */
function matriq_msa_clean_data( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'matriq_msa_clean_data', $var );
	}
	return is_scalar( $var ) ? sanitize_text_field( (string) $var ) : $var;
}
