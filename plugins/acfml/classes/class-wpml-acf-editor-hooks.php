<?php

class WPML_ACF_Editor_Hooks {
	public function init_hooks() {
		add_filter('wpml_tm_editor_string_style', array($this, 'wpml_tm_editor_string_style'), 10, 3);
	}

	public function wpml_tm_editor_string_style($field_style, $field_type, $original_post) {
		return $this->maybe_set_acf_wyswig_style($field_style, $field_type, $original_post);
	}

	private function maybe_set_acf_wyswig_style($field_style, $field_type, $original_post) {
		if ( preg_match_all('/field-(.+)-\d+/', $field_type, $matches, PREG_SET_ORDER, 0) !== false ) {

			$field_name = $matches[0][1];
			$acf_meta_meta = get_post_meta($original_post->ID, "_".$field_name, true);

			if (!empty($acf_meta_meta)) {
				global $wpdb;
				$query = $wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = %s", $acf_meta_meta);
				$query_result = $wpdb->get_var($query);
				$acf_field_data = maybe_unserialize($query_result);
				if (isset($acf_field_data['type']) && $acf_field_data['type'] === 'wysiwyg') {
					$field_style = '2';
				}
			}

		}

		return $field_style;
	}
}