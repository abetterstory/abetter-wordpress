<?php

namespace GummiIO\AcfComponentField\Screens;

/**
 * FieldGroup Class
 *
 * Class that handles all the hook when on the field gropu edit admin page
 *
 * @since   2.0.0
 * @version 2.0.0
 */
class FieldGroup
{
	/**
	 * Add action to register additional hooks when acf is initilized
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function __construct()
	{
        add_action('acf/init', [$this, 'registerHooks']);
	}

	/**
	 * Register hooks to adjust field group edit admin page on screen
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function registerHooks()
	{
		add_action('current_screen', [$this, 'adjustFieldGroupsScreen']);
	}

	/**
	 * Add additional filters and hooks
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function adjustFieldGroupsScreen()
	{
		if (! acf_is_screen('acf-field-group')) {
			return;
		}

		add_action('acf/field_group/admin_head', [$this, 'addComponentMetaBoxes']);
	}

	/**
	 * Add Component status and default option metaboxes
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function addComponentMetaBoxes()
	{
		add_meta_box(
            'acf-component-field-status-metabox',
            __('Used as ACF Component Field?', 'acf-component_field'),
            [$this, 'componentStatusMetaboxCallback'],
            'acf-field-group',
            'side'
        );

		add_meta_box(
            'acf-component-field-default-metabox',
            __('Component Field Default Options', 'acf-component_field'),
            [$this, 'componentDefaultOptionsMetaboxCallback'],
            'acf-field-group',
            'normal',
            'high'
        );
	}

	/**
	 * Output component status metabox's html
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function componentStatusMetaboxCallback()
	{
		require_once acf_component_field()->path('templates/admin/metabox-component-status.php');
	}

	/**
	 * Output component default options metabox's html
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function componentDefaultOptionsMetaboxCallback()
	{
		require_once acf_component_field()->path('templates/admin/metabox-component-defaults.php');
	}
}
