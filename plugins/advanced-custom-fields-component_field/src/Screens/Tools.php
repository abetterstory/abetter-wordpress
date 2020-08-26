<?php

namespace GummiIO\AcfComponentField\Screens;

use GummiIO\AcfComponentField\Tools\Migration;

/**
 * Tools Class
 *
 * Class that handles all the hook when on the acf tools admin page
 *
 * @since   2.0.0
 * @version 2.0.1
 */
class Tools
{
	/**
	 * Add action to register additional hooks when acf is initilized
	 *
     * @since   2.0.0
     * @version 2.0.1
	 */
	public function __construct()
	{
        add_action('acf/init', [$this, 'registerHooks']);
        add_action('acf/include_admin_tools', [$this, 'registerTools']);
	}

	/**
	 * Register hooks to adjust field group edit admin page on screen
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function registerHooks()
	{
		add_action('load-custom-fields_page_acf-tools', [$this, 'toolsScreen']);
	}

	/**
	 * Register acf admin tools
	 *
     * @since   2.0.1
     * @version 2.0.1
	 */
	public function registerTools()
	{
		acf_register_admin_tool(Migration::class);
	}

	/**
	 * Add and remove additional filters and hooks
	 *
     * @since   2.0.0
     * @version 2.0.1
	 */
	public function toolsScreen()
	{
		// cannot use 'acf/prepare_field_for_import' because that does not
		// overwrite the fields array that will be imported
		add_filter('acf/prepare_fields_for_import', [$this, 'importFieldBackwardCompatibility']);

		// there's not filter for field group import, have to use this
        add_action('acf/update_field_group', [$this, 'importFieldGroupBackwardCompatibility']);

		remove_filter('acf/load_field/type=component_field', [acf_get_field_type('component_field'), 'load_field']);
	}

	/**
	 * In case user import an 1.0 export file, we need to convert the options
	 * to the correct key.
	 *
     * @since   2.0.0
     * @version 2.0.1
	 * @param   array $fields Fields that will be imported
	 */
	public function importFieldBackwardCompatibility($fields)
	{
		return array_map(function($field) {
			if (! isset($field['field_group_id'])) {
				return $field;
			}

			$field['field_group_key'] = $field['field_group_id'];
			unset($field['field_group_id']);

			return $field;
		}, $fields);
	}

	/**
	 * In case user import an 1.0 export file, we need to set the field group
	 * to the correct keys.
	 *
     * @since   2.0.1
     * @version 2.0.1
	 * @param   array $fieldGroup Field group that will be imported
	 */
    public function importFieldGroupBackwardCompatibility($fieldGroup)
    {
        if (! acf_maybe_get($_FILES, 'acf_import_file')) {
            return;
        }

        if (! $fieldGroup['is_acf_component']) {
            return;
        }

        update_post_meta($fieldGroup['ID'], 'is_acf_component', true);

        if ($fieldGroup['active']) {
            return;
        }

        $fieldGroup['active'] = true;
        $fieldGroup['is_acf_component'] = true;

        remove_action('acf/update_field_group', [$this, 'ImportFieldGroupBackwardCompatibility']);
        acf_update_field_group($fieldGroup);
        add_action('acf/update_field_group', [$this, 'ImportFieldGroupBackwardCompatibility']);
    }
}
