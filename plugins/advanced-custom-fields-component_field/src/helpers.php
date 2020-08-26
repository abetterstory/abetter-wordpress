<?php

/**
 * Retreive the global plugin instance, or its module
 *
 * @since   2.0.0
 * @version 2.0.0
 * @param  string $module Optional module slug
 */
function acf_component_field($module = null) {
	global $acfComponentField;

	return $module? $acfComponentField->$module : $acfComponentField;
}

/**
 * Check whether the given parameter is a component field group
 *
 * @since   2.0.0
 * @version 2.0.0
 * @param  mix  $fieldGroup Thing to check against
 */
function is_acf_component_field_group($fieldGroup) {
	if ((is_int($fieldGroup) || is_string($fieldGroup)) && $group = acf_get_field_group($fieldGroup)) {
		$fieldGroup = $group;
	}

	if (is_array($fieldGroup) && isset($fieldGroup['is_acf_component'])) {
		return !! $fieldGroup['is_acf_component'];
	}

	if (is_array($fieldGroup) && isset($fieldGroup['ID'])) {
		return !! get_post_meta($fieldGroup['ID'], 'is_acf_component', true);
	}

	if (is_object($fieldGroup) && isset($fieldGroup->ID)) {
		return !! get_post_meta($fieldGroup->ID, 'is_acf_component', true);
	}

	return false;
}
