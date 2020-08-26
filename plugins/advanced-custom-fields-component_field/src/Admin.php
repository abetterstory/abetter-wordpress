<?php

namespace GummiIO\AcfComponentField;

/**
 * Admin Class
 *
 * Class that handles all the hook on wp's admin
 *
 * @since   2.0.0
 * @version 2.0.0
 */
class Admin
{
	/**
	 * Constructor
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function __construct()
	{
        add_action('admin_notices', [$this, 'printAcfRequiredNotice']);
        add_action('admin_notices', [$this, 'printAcfVersionNotice']);
	}

	/**
	 * Show the admin notice if acf is not activated or install
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
    public function printAcfRequiredNotice()
    {
        if (class_exists('acf')) {
            return;
        }

        printf('
            <div class="notice notice-error">
                <p>%s</p>
            </div>',
            sprintf(
                __('%s requires the plugin %s to be activated.', 'acf-component_field'),
                '<b>' . __('ACF Component Field', 'acf-component_field') . '</b>',
                '<b>' . __('Advanced Custom Fields Pro', 'acf-component_field') . '</b>'
            )
        );
    }

	/**
	 * Show the admin notice if acf version is not grater than 5.7
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
    public function printAcfVersionNotice()
    {
        if (! class_exists('acf')) {
            return;
        }

        if (version_compare(ACF_VERSION, '5.7.0', '>=')) {
        	return;
        }

        printf('
            <div class="notice notice-error">
                <p>%s</p>
            </div>',
            sprintf(
                __('%s v2 requires the plugin %s to be at least v5.7.0.', 'acf-component_field'),
                '<b>' . __('ACF Component Field', 'acf-component_field') . '</b>',
                '<b>' . __('Advanced Custom Fields Pro', 'acf-component_field') . '</b>'
            )
        );
    }
}
