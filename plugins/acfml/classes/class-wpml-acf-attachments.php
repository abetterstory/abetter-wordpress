<?php

class WPML_ACF_Attachments {
	public function register_hooks() {
		add_filter( 'acf/load_value/type=gallery', array( $this, 'load_translated_attachment' ), 10, 3 );
		add_filter( 'acf/load_value/type=image', array( $this, 'load_translated_attachment' ), 10, 3 );
		add_filter( 'acf/load_value/type=file', array( $this, 'load_translated_attachment' ), 10, 3 );
	}

	public function load_translated_attachment($value, $post_id, $field) {
		$newValue = $value;

		if ( is_array($value) ) { // Galleries come in arrays
			$newValue = array();
			foreach ( $value as $key => $id ) {
				$newValue[$key] = icl_object_id( $id, 'attachment' );
			}
		} else { // Single images arrive as simple values
			$newValue = icl_object_id( $value, 'attachment' );
		}

		return $newValue;
	}
}