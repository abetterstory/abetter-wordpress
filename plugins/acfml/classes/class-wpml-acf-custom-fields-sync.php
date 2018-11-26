<?php

class WPML_ACF_Custom_Fields_Sync {
	public function register_hooks() {
		add_action( 'wpml_single_custom_field_sync_option_updated', array($this, 'synchronise_subrepeater_fields'), 10, 1);
		add_action( 'wpml_custom_fields_sync_option_updated', array($this, 'synchronise_subrepeater_fields'), 10, 1);

		// make copy once synchornisation working (acfml-45)
		add_filter( 'wpml_custom_field_values', array($this, 'remove_cf_value_for_copy_once'), 10, 2);

	}

	public function synchronise_subrepeater_fields($cft) {
		global $iclTranslationManagement;
		foreach ($cft as $field_name => $field_preferences) {
			$post_id = $this->get_post_with_custom_field($field_name);
			$repeater_object = get_field_object($field_name, $post_id);
			if (isset($repeater_object['type']) && 'repeater' == $repeater_object['type']) { // it is repeater field
				$repeater = get_field($field_name, $post_id);
				if ($repeater) {
					foreach ($repeater as $row_number => $row_content) {
						foreach ($row_content as $subfield_name => $subfield_value) {
							$iclTranslationManagement->settings['custom_fields_translation'][$field_name . "_" . $row_number . "_" . $subfield_name] = $field_preferences;
						}
					}
				}

				$iclTranslationManagement->settings['custom_fields_translation'][$field_name] = $field_preferences;
			}
		}

		$iclTranslationManagement->save_settings();

		// this action runs also for case 'icl_tcf_translation', @see \TranslationManagement::ajax_calls
		// it shouldn't because it will overwrite normal cf fields values with zeros
		remove_action( 'wpml_custom_fields_sync_option_updated', array($this, 'synchronise_subrepeater_fields'), 10, 1);
	}

	public function remove_cf_value_for_copy_once( $value, $context_data ) {
		/*
		 * when user starts translating post, acfml automatically creates custom fields with empty values
		 * WPML before running "copy once" checks if field doesn't exist - it is conflicting
		 * purpose of this code:
		 * if it is first copy-once synchronisation of acf field, remove value
		 */

		// check if this is copy once synchronisation
		if ( WPML_COPY_ONCE_CUSTOM_FIELD == $context_data['custom_fields_translation'] ) {
			// check if custom field is acf field
			$field = get_field_object($context_data['meta_key'], $context_data['post_id']);
			if ( false != $field ) {
				// check if value is array with empty strings
				if (is_array($value) && end(end($value)) === "") {
					$value = array();
				}
			}
		}

		return $value;
	}

	private function get_post_with_custom_field($field_name) {
		$post_id = get_the_ID() || get_queried_object();
		if (!is_numeric($post_id)) {
			global $wpdb;
			$query = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '{$field_name}' LIMIT 1";
			$post_id = $wpdb->get_var($query);
		}
		return $post_id;
	}


}