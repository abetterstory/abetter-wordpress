<?php

namespace GummiIO\AcfComponentField;

use WP_Query;

/**
 * Upgrader Class
 *
 * Class which handles the plugin version upgrade
 *
 * @since   2.0.0
 * @version 2.0.1
 */
class Upgrader
{
	/**
	 * Database option's key name that store the version
	 *
	 * @since   2.0.0
	 * @version 2.0.0
	 */
	protected $optionKey = 'acf_component_field_version';

	/**
	 * Version value from the database
	 *
	 * @since   2.0.0
	 * @version 2.0.0
	 */
	protected $dbVersion;

	/**
	 * Versions map that will need to run migration
	 *
	 * @since   2.0.0
	 * @version 2.0.1
	 */
	protected $versions = [
		'2.0.0' => 'upgradeTo_2_0_0',
		'2.0.1' => 'upgradeTo_2_0_1'
	];

	/**
	 * Add action to register additional hooks when acf is initilized
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function __construct()
	{
		$this->dbVersion = get_option($this->optionKey, '0.0.0');

		add_action('acf/init', [$this, 'checkForUpdatesHook']);
	}

	/**
	 * Get the current DB migration version
	 *
     * @since   2.0.1
     * @version 2.0.1
	 */
	public function getDbVersion()
	{
		return $this->dbVersion;
	}

	/**
	 * Reset the migration value, and run the migration again
	 *
     * @since   2.0.1
     * @version 2.0.1
	 * @param  string $redirect The url to redirect to after complete
	 */
	public function forceMigrate($redirect = null)
	{
		$this->dbVersion = '0.0.0';
		update_option($this->optionKey, '0.0.0');
		$this->checkForUpdates($redirect);
	}

	/**
	 * Acf's order is wierd on the init from ACF class, so it have to actually
	 * use the init hook to check update after acf is initilized
	 *
     * @since   2.0.0
     * @version 2.0.1
	 */
	public function checkForUpdatesHook()
	{
        if (version_compare(ACF_VERSION, '5.7.0', '<')) {
            return;
        }

		add_action('init', [$this, 'checkForUpdates']);
	}

	/**
	 * Loop through the version maps and check against the db version to see
	 * if need to perform any updates.
	 *
     * @since   2.0.0
     * @version 2.0.1
	 * @param  string $redirect The url to redirect to after complete
	 */
	public function checkForUpdates($redirect = null)
	{
		if ($this->isUpToDate()) {
			return;
		}

		foreach ($this->versions as $version => $method) {
			if (version_compare($this->dbVersion, $version, '>=')) {
				continue;
			}

			$this->$method();
			update_option($this->optionKey, $version);
			$this->dbVersion = $version;
		}

		// do a refresh, because acf cache...
		wp_redirect($redirect? : $_SERVER['REQUEST_URI']);
		exit;
	}

	/**
	 * Check if the db version is already the latest version that requires migration
	 *
     * @since   2.0.0
     * @version 2.0.1
	 */
	protected function isUpToDate()
	{
		$latestVersion = array_values(array_slice(array_keys($this->versions), -1))[0];

		return version_compare($this->dbVersion, $latestVersion, '>=');
	}

	/**
	 * Migration function to upgrade to version 2.0.0
	 *
	 * In version 2.0.0, component field no longer uses 'acf-component' status,
	 * so we need loop through all field gorups and convert them to the
	 * build-in status.
	 *
	 * In version 2.0.0, the field property "field_group_id" has been changed
	 * to "field_gropu_key", so we need to loop through all fields and
	 * convert them.
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	protected function upgradeTo_2_0_0()
	{
		$this->registerComponentStatus();

	    $fieldGroups = new WP_Query([
	        'posts_per_page' => -1,
	        'post_type'      => 'acf-field-group',
	        'post_status'    => ['acf-component', 'acf-disabled']
	    ]);

	    foreach ($fieldGroups->posts as $fieldGroup) {
	        $fieldGroup = acf_get_field_group($fieldGroup);

	        if (! acf_maybe_get($fieldGroup, 'is_acf_component')) {
	        	continue;
	        }

	        $fieldGroup['active'] = true;
	        acf_update_field_group($fieldGroup);

            wp_update_post([
                'ID' => acf_maybe_get($fieldGroup, 'ID'),
                'post_status' => 'publish',
                'meta_input' => [
                	'is_acf_component' => true
                ]
            ]);
	    }

	    $this->unregisterComponentStatus();

	    // =====
	    $fields = new WP_Query([
	        'posts_per_page' => -1,
	        'post_type'      => 'acf-field'
	    ]);

	    foreach ($fields->posts as $field) {
	    	$field = acf_get_field($field);

	        if ($fieldGroupKey = acf_maybe_get($field, 'field_group_id')) {
	        	$field['field_group_key'] = $fieldGroupKey;
	        	unset($field['field_group_id']);
	        }

	        if (acf_maybe_get($field, 'min') == acf_maybe_get($field, 'max') && acf_maybe_get($field, 'min') == 1) {
	        	$field['repeatable'] = false;
	        } else {
	        	$field['repeatable'] = true;
	        }

        	acf_update_field($field);
	    }
	}

	/**
	 * Migration function to upgrade to version 2.0.1
	 *
	 * Additional update after the 2.0.0. In some cases, user update the plugin
	 * in the wrong order, or using old export file.
	 *
     * @since   2.0.1
     * @version 2.0.1
	 */
	protected function upgradeTo_2_0_1()
	{
	    $fieldGroups = new WP_Query([
	        'posts_per_page' => -1,
	        'post_type'      => 'acf-field-group',
	        'post_status'    => ['acf-component', 'acf-disabled', 'publish']
	    ]);

	    foreach ($fieldGroups->posts as $fieldGroup) {
	        $fieldGroup = acf_get_field_group($fieldGroup);

	        if (! $this->wasComponentField($fieldGroup)) {
	        	continue;
	        }

	        // in case the data is curropted, we add the data back
            $fieldGroup['active'] = true;
	        $fieldGroup['is_acf_component'] = true;
	        acf_update_field_group($fieldGroup);

            wp_update_post([
                'ID' => acf_maybe_get($fieldGroup, 'ID'),
                'post_status' => 'publish',
                'meta_input' => [
                	'is_acf_component' => true
                ]
            ]);
	    }
	}

	/**
	 * Register the post status that uses from version 1
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	protected function registerComponentStatus()
	{
        register_post_status('acf-component', [
            'label'  => __('Component', 'acf-component_field'),
            'public' => false,
        ]);
	}

	/**
	 * Unregister the post status that uses from version 1
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	protected function unregisterComponentStatus()
	{
		global $wp_post_statuses;

		unset($wp_post_statuses['acf-component']);
	}

	/**
	 * Check if the field group was a component field from version 1 or the
	 * corrupted version. Checking field group key is not reliable if data is
	 * already corrupted, add additional check on the meta value.
	 *
     * @since   2.0.1
     * @version 2.0.1
	 * @param  array $fieldGroup Field group to check
	 */
    protected function wasComponentField($fieldGroup)
    {
        if (acf_maybe_get($fieldGroup, 'is_acf_component')) {
            return true;
        }

        if (! $id = acf_maybe_get($fieldGroup, 'ID')) {
            return false;
        }

        return !! get_post_meta($id, 'is_acf_component', true);
    }
}
