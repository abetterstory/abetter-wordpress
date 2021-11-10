<?php

namespace WPML\Compatibility\Divi;

/**
 * Divi replaces double quotes with %22 when saving shortcode attributes.
 * ATE needs valid HTML so we temporarily decode the double quotes.
 * When we receive the translation we undo the change.
 *
 * @package WPML\Compatibility\Divi
 */
class DoubleQuotes implements \IWPML_Backend_Action, \IWPML_Frontend_Action {

	public function add_hooks() {
		add_filter( 'wpml_pb_shortcode_decode', [ $this, 'decode' ], -PHP_INT_MAX );
		add_filter( 'wpml_pb_shortcode_encode', [ $this, 'encode' ], PHP_INT_MAX );
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public function decode( $string ) {
		if ( is_string( $string ) ) {
			$string = str_replace( '%22', '"', $string );
		}

		return $string;
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public function encode( $string ) {
		if ( is_string( $string ) ) {
			$string = str_replace( '"', '%22', $string );
		}

		return $string;
	}

}
