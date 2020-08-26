<?php

namespace GummiIO\AcfComponentField\Tools;

use ACF_Admin_Tool;

/**
 * Migration Tool Class
 *
 * Class that setup tool metabox on the acf tools admin page
 *
 * @since   2.0.1
 * @version 2.0.1
 */
class Migration extends ACF_Admin_Tool
{
	/**
	 * Setup admin tool's data
	 *
     * @since   2.0.1
     * @version 2.0.1
	 */
	public function initialize()
	{
		$this->name = 'acf_component_field-migration';
		$this->title = __("Acf Component Field: Migration", 'acf-component_field');
	}

	/**
	 * Check the url and maybe print migration complete notice
	 *
     * @since   2.0.1
     * @version 2.0.1
	 */
	public function load()
	{
		if (acf_maybe_get_GET('migrated')) {
	    	acf_add_admin_notice(__('Acf Component Field migration complete. You are now up to date.', 'acf-component_field'));
		}
	}

	/**
	 * Metabox content
	 *
     * @since   2.0.1
     * @version 2.0.1
	 */
	public function html()
	{
		include acf_component_field()->path('templates/admin/tools-migration.php');
	}

	/**
	 * Handle force migration
	 *
     * @since   2.0.1
     * @version 2.0.1
	 */
	public function submit()
	{
		$url = add_query_arg('migrated', 1, $_SERVER['REQUEST_URI']);
		acf_component_field()->upgrader->forceMigrate($url);
	}
}
