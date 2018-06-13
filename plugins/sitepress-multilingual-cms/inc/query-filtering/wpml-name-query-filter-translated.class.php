<?php

/**
 * Class WPML_Name_Query_Filter_Translated
 *
 * @package    wpml-core
 * @subpackage post-translation
 *
 * @since      3.2.3
 */
class WPML_Name_Query_Filter_Translated extends WPML_Name_Query_Filter {

	/**
	 * @param array $pages_with_name
	 *
	 * @return int|null|string
	 */
	protected function select_best_match( $pages_with_name ) {
		$pages_to_langs = array();
		foreach ( $pages_with_name as $page_with_name ) {
			$page_lang = $this->post_translation->get_element_lang_code( (int) $page_with_name );

			if ( $this->sitepress->get_current_language() === $page_lang ) {
				// return immediately the first page in the current language
				return $page_with_name;
			}

			$pages_to_langs[ $page_with_name ] = $page_lang;
		}

		foreach ( $pages_to_langs as $page_with_name => $element_lang ) {
			if ( $element_lang === $this->sitepress->get_default_language()
			     && $this->sitepress->is_display_as_translated_post_type( get_post_type( $page_with_name ) )
			) {
				return $page_with_name;
			}
		}

		foreach ( $this->active_languages as $lang_code ) {
			foreach ( $pages_to_langs as $page_with_name => $element_lang ) {
				if ( $element_lang === $lang_code ) {
					return $page_with_name;
				}
			}
		}

		return null;
	}

	/**
	 * Returns a SQL snippet for joining the posts table with icl translations filtered for the post_type
	 * of this class.
	 *
	 * @return string
	 */
	protected function get_from_join_snippet() {

		return " FROM {$this->wpdb->posts} p
	             JOIN {$this->wpdb->prefix}icl_translations t
					ON p.ID = t.element_id
						AND t.element_type = CONCAT('post_', p.post_type ) ";
	}
}