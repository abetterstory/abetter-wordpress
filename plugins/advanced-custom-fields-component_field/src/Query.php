<?php

namespace GummiIO\AcfComponentField;

use WP_Query;

/**
 * Query Class
 *
 * Class which holds caching the component field groups
 *
 * @since   2.0.0
 * @version 2.0.2
 */
class Query
{
	/**
	 * The cached component field groups
	 */
	protected $componentGroups = [];

	/**
	 * Add action to register additional hooks when acf is initilized
	 *
     * @since   2.0.0
     * @version 2.0.2
	 */
	public function __construct()
	{
		add_action('acf/init', [$this, 'registerCacheHook']);
	}

	/**
	 * Cache the component in this class's property
	 *
	 * Cannot use the 'acf/init' function because the acf post types are
	 * registered after the 'acf/init' at 'init:5'. So this has to be after
	 * the post types are registered to ensure WPML's filters take affect
	 *
     * @since   2.0.2
     * @version 2.0.2
	 */
	public function registerCacheHook()
	{
		add_action('init', [$this, 'cacheComponentGruops']);
	}

	/**
	 * Loop throught all the field groups and add the component field group
	 * into the cache property
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function cacheComponentGruops()
	{
		$componentGroups = wp_filter_object_list(acf_get_field_groups(), ['is_acf_component' => 1]);

		foreach ($componentGroups as $componentGroup) {
			$this->componentGroups[$componentGroup['key']] = $componentGroup;
		}
	}

	/**
	 * Get the component field group by key
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   stirng $key Field gorup key
	 */
	public function getComponent($key)
	{
		return acf_maybe_get($this->componentGroups, $key);
	}

	/**
	 * Add a component into cache
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   lbject $fieldGroup Acf component field object
	 */
	public function addComponent($fieldGroup)
	{
		$this->componentGroups[$fieldGroup['key']] = $fieldGroup;
	}

	/**
	 * Get all the component field groups
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function getComponents()
	{
		return $this->componentGroups;
	}

	/**
	 * Count the copomnent fields that's from the database
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function getComponentCount()
	{
		$dbComponents = array_filter($this->getComponents(), function($component) {
			return $component['ID'];
		});

		return count($dbComponents);
	}

	/**
	 * Count the usage of a component field group
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   string $group Field gorup ID or key
	 */
	public function getUsedCount($group)
	{
		global $wpdb;

		$fieldGroup = acf_get_field_group($group);
		$this->groupKey = acf_maybe_get($fieldGroup, 'key');

		add_filter('posts_where', [$this, 'addComponentSearchCluse']);
		add_filter('posts_fields', [$this, 'addComponentSelectCount']);
		$query = new WP_Query([
			'post_type' => 'acf-field',
			'posts_per_page' => -1,
		]);
		$results = $wpdb->get_results($query->request);
		remove_filter('posts_where', [$this, 'addComponentSearchCluse']);
		remove_filter('posts_fields', [$this, 'addComponentSelectCount']);

		return $results? (int) $results[0]->count : 0;
	}

	/**
	 * Add search cluse to query
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   string $where Existing where cluse
	 */
	public function addComponentSearchCluse($where)
	{
		global $wpdb;

		$length = strlen($this->groupKey);
		$where .= " AND {$wpdb->posts}.post_content LIKE '%s:15:\"field_group_key\";s:{$length}:\"{$this->groupKey}\";%'";

		return $where;
	}

	/**
	 * Alter the query's select SQL statement
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   string $select Existing select cluse
	 */
	public function addComponentSelectCount($select)
	{
		return 'COUNT(*) as count';
	}
}
