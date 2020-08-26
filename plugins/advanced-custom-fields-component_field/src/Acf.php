<?php

namespace GummiIO\AcfComponentField;

use GummiIO\AcfComponentField\Field\AcfComponentField;

/**
 * Acf Class
 *
 * Class that handles all the hook on global acf stuff
 *
 * @since   2.0.0
 * @version 2.0.2
 */
class Acf
{
	/**
	 * Register acf specific hooks and filters
	 *
     * @since   2.0.0
     * @version 2.0.2
	 */
	public function __construct()
	{
		add_action('acf/include_field_types', [$this, 'reegisterComponentFieldType']);
        add_filter('acf/get_field_group', [$this, 'removeLocationOnComponentFieldGroup']);
        add_action('acf/update_field_group', [$this, 'updateComponentStatus']);
        add_action('acf/prepare_field_group_for_export', [$this, 'removeLocationOnComponentFieldGroup']);
	}

	/**
	 * Register the component field into acf's field type list
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function reegisterComponentFieldType()
	{
		acf_register_field_type(AcfComponentField::class);
	}

	/**
	 * Remove the location when retreiving the component field group, since
	 * we are not setting the field group to inactive anymore. This will
	 * prevent the field group from showing up on post edit page.
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   object $fieldGroup The acf field group
	 */
	public function removeLocationOnComponentFieldGroup($fieldGroup)
	{
    	if (acf_maybe_get($fieldGroup, 'is_acf_component')) {
    		$fieldGroup['location'] = [];
    	}

        return $fieldGroup;
	}

	/**
	 * Update the meta value when field group is saved.
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   object $fieldGroup The acf field group
	 */
	public function updateComponentStatus($fieldGroup)
	{
		$fieldGroupID = acf_maybe_get($fieldGroup, 'ID');
		$isComponent  = !! acf_maybe_get($fieldGroup, 'is_acf_component');

		update_post_meta($fieldGroupID, 'is_acf_component', $isComponent);
	}
}
