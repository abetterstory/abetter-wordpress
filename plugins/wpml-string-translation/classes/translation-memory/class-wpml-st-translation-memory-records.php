<?php

class WPML_ST_Translation_Memory_Records {

	/** @var wpdb $wpdb */
	private $wpdb;

	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	/**
	 * @param array $args with keys
	 *                    - `strings` an array of strings
	 *                    - `source_lang`
	 *                    - `target_lang` (optional)
	 *
	 * @return array
	 */
	public function get( array $args ) {
		$strings     = isset( $args['strings'] ) && is_array( $args['strings'] ) ? $args['strings'] : null;
		$source_lang = isset( $args['source_lang'] ) ? $args['source_lang'] : null;
		$target_lang = isset( $args['target_lang'] ) ? $args['target_lang'] : null;

		if ( ! ( $strings && $source_lang ) ) {
			return array();
		}

		$prepared_strings = wpml_prepare_in( $strings );

		$sql = "
			SELECT s.value as original, st.value as translation, st.language as language
			FROM {$this->wpdb->prefix}icl_strings as s
			JOIN {$this->wpdb->prefix}icl_string_translations as st
			ON s.id = st.string_id
			WHERE s.value IN ({$prepared_strings}) AND s.language = '%s'
				AND st.status = " . ICL_STRING_TRANSLATION_COMPLETE;

		$prepare_args = array( $source_lang );

		if ( $target_lang ) {
			$sql .= " AND st.language = '%s'";
			$prepare_args[] = $target_lang;
		} else {
			$sql .= " AND st.language <> '%s'";
			$prepare_args[] = $source_lang;
		}

		return $this->wpdb->get_results( $this->wpdb->prepare( $sql, $prepare_args ) );
	}
}