<?php

namespace GummiIO\AcfComponentField\Screens;

/**
 * EditFieldGroup Class
 *
 * Class that handles all the hook when on the field gropu listing admin page
 *
 * @since   2.0.0
 * @version 2.0.0
 */
class EditFieldGroup
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
	 * Register hooks to adjust field group listing admin page on screen
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function registerHooks()
	{
		add_action('current_screen', [$this, 'maybeRedirectOnDuplication'], 5);
		add_action('current_screen', [$this, 'editFieldGroupsScreen']);
	}

	/**
	 * Redirects to the component tab after a component field is duplicated
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function maybeRedirectOnDuplication()
	{
		if (! acf_is_screen('edit-acf-field-group')) {
			return;
		}

		if (! $ids = acf_maybe_get_GET('acfduplicatecomplete')) {
			return;
		}

		if (acf_maybe_get_GET('component_fields')) {
			return;
		}

		$ids = explode(',', $ids);
		$id = array_shift($ids);

		if (count($ids) > 0) {
			return;
		}

		$fieldGroup = acf_get_field_group($id);

		if (! acf_component_field('query')->getComponent($fieldGroup['key'])) {
			return;
		}

		$url = add_query_arg(wp_parse_args([
			'component_fields' => 1
		], $_GET), admin_url('edit.php'));

		wp_redirect($url);
		exit;
	}

	/**
	 * Add additional filters and hooks
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function editFieldGroupsScreen()
	{
		if (! acf_is_screen('edit-acf-field-group')) {
			return;
		}

		add_filter('pre_get_posts', [$this, 'filterComponentGruops']);
		add_filter('wp_count_posts', [$this, 'adjustPostCount']);
		add_filter('views_edit-acf-field-group', [$this, 'removeMineTab']);
		add_filter('views_edit-acf-field-group', [$this, 'addComponentSubSubSub']);

		add_filter('manage_edit-acf-field-group_columns', [$this, 'adjustListColumns'], 15);
		add_action('manage_acf-field-group_posts_custom_column', [$this, 'adjustListColumnsHtml'], 15, 2);
		add_action('admin_footer', [$this, 'inlineColumnCss']);
	}

	/**
	 * Alter the main query, to either show only the component fields or exclude them
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   object $query WP_Query
	 */
	public function filterComponentGruops($query)
	{
		if (! $this->needToFilterComponent($query)) {
			return;
		}

		// Just in case if other plugin added stuff
		$metaQuery = $query->get('meta_query') ?: [];

		if (acf_maybe_get_GET('component_fields')) {
			$metaQuery[] = [
				[
					'key' => 'is_acf_component',
					'value' => true
				]
			];
		} else {
			$metaQuery[] = [
				'relation' => 'OR',
				[
					'key' => 'is_acf_component',
					'compare' => 'NOT EXISTS'
				],
				[
					'key' => 'is_acf_component',
					'value' => false
				]
			];
		}

		$query->set('meta_query', $metaQuery);
	}

	/**
	 * Adjust the publish count to exclude the component fields
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   int $count Original count that includes component fields
	 */
	public function adjustPostCount($count)
	{
		$offset = $count->publish - acf_component_field('query')->getComponentCount();

		$count->publish = $offset < 0? 0 : $offset;

		return $count;
	}

	/**
	 * Remove the "mine" tab. Since we alter the "publish" count, wp will
	 * show the "mine" tab. So we need to remove it from lsit table tabs.
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   array $views the list table's tabs
	 */
	public function removeMineTab($views)
	{
		unset($views['mine']);

		return $views;
	}

	/**
	 * Add Component into list table's tabs
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   array $views the list table's tabs
	 */
	public function addComponentSubSubSub($views)
	{
		$url   = add_query_arg('component_fields', '1', admin_url('edit.php?post_type=acf-field-group'));
		$count = acf_component_field('query')->getComponentCount();

		$countHtml = sprintf(
			_n(
				'Component <span class="count">(%s)</span>',
				'Components <span class="count">(%s)</span>',
				$count,
				'acf-component_field'
			),
			number_format_i18n($count)
		);

		$views['component'] = sprintf(
			'<a href="%s" class="%s">%s</a>',
			$url,
			acf_maybe_get_GET('component_fields')? 'current' : '',
			$countHtml
		);

		return $views;
	}

	/**
	 * Remove the status column and add usage on the component tab
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   array $columns list table's columns
	 */
	public function adjustListColumns($columns)
	{
		if (! acf_maybe_get_GET('component_fields')) {
			return $columns;
		}

		$fields = $columns['acf-fg-count'];
		unset($columns['acf-fg-status']);
		unset($columns['acf-fg-count']);

		$columns['acf-fg-usage'] = sprintf(
			'<span class="acf-js-tooltip" title="%s">%s</span>',
			__('Times this component has been used', 'acf-component_field'),
			__('Used', 'acf-component_field')
		);

		$columns['acf-fg-count'] = $fields;

		return $columns;
	}

	/**
	 * Output the component usage count
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   string $column Current columns' name
	 * @param   int $postId Current row in list table
	 */
	public function adjustListColumnsHtml($column, $postId)
	{
		if ($column == 'acf-fg-usage') {
			echo acf_component_field('query')->getUsedCount($postId);
		}
	}

	/**
	 * Add column width's css in the footer
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function inlineColumnCss()
	{
        echo '<style>
	        #acf-field-group-wrap .wp-list-table .column-acf-fg-usage {
				width: 10%;
			}
		</style>';
	}

	/**
	 * Check whether the givent query should filter out the component fields
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   object $query WP_Query object
	 */
	protected function needToFilterComponent($query)
	{
		return (
			$query->is_main_query() &&
			$query->get('post_type') == 'acf-field-group' &&
			$query->get('post_status') !== 'trash'
		);
	}
}
