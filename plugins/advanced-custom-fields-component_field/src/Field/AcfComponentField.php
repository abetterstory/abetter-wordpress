<?php

namespace GummiIO\AcfComponentField\Field;

use acf_field_repeater;

/**
 * Main Acf Component Field Class
 *
 * Component field class that extends the functionality from acf repeater field
 *
 * @since   2.0.0
 * @version 2.0.0
 */
class AcfComponentField extends acf_field_repeater
{
	/**
	 * Setting up the field's property
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function initialize()
	{
		$this->name     = 'component_field';
		$this->label    = __('Component Field', 'acf-component_field');
		$this->category = 'relational';
		$this->defaults = [
			'field_group_key' => null,
			'sub_fields'      => [],
			'repeatable'      => false,
			'min'             => null,
			'max'             => null,
			'layout'          => 'block',
			'button_label'    => '',
			'collapsed'       => '',
			'appearances'     => []
		];

		add_action('wp_ajax_acf/field_types/component_field/load_settings', [$this, 'loadComponentSettings']);
		add_filter('acf/prepare_field/name=field_group_key', [$this, 'filterAvaliableFieldGroups']);

		$this->add_field_filter('acf/prepare_field_for_export', [$this, 'removeSubfieldsOnExport']);
	}

	/**
	 * Enqueue addets and translation when on the field group edit screen
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function field_group_admin_enqueue_scripts()
	{
		wp_enqueue_script(
			'acf-group-component_field',
			acf_component_field()->url('assets/js/field-group.js', true),
			['acf-pro-input'],
			acf_component_field()->version()
		);

		acf_localize_text([
			'convert_title'       => __('Convert field', 'acf-component_field'),
			'convert_text'        => __('Convert', 'acf-component_field'),
			'convert_warning'     => __('This field cannot be converted until its changes have been saved', 'acf-component_field'),
			'convert_popup_title' => __('Convert Field Group', 'acf-component_field'),
			'convert_delete_confirm_message' => __('Are you sure you want to delete the component afterwards?', 'acf-component_field'),
		]);
	}

	/**
	 * Enqueue addets on post that uses this acf fields
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function input_admin_enqueue_scripts()
	{
		wp_enqueue_script(
			'acf-input-component_field',
			acf_component_field()->url('assets/js/field-input.js', true),
			['acf-pro-input'],
			acf_component_field()->version()
		);

		wp_enqueue_style(
			'acf-input-component_field',
			acf_component_field()->url('assets/css/field-input.css', true),
			['acf-pro-input'],
			acf_component_field()->version()
		);
	}

	/**
	 * Render field's settings. Additional settings will only be loaded if a
	 * selected field group is set and valid.
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   object $field acf field
	 */
	public function render_field_settings($field)
	{
		acf_render_field_setting($field, [
			'label'         => __('Field Group', 'acf-component_field'),
			'instructions'  => __('Select a field group to be used', 'acf-component_field'),
			'type'          => 'select',
			'name'          => 'field_group_key',
			'allow_null'    => true,
			'choices'       => [], // choices will be loaded from filter
			'acf-component_field::select_group' => 1,
		]);

		$fieldGroupKey = acf_maybe_get($field, 'field_group_key');

		if (! acf_get_field_group($fieldGroupKey)) {
			return;
		}

		foreach ($this->componentSettingFields() as $setting) {
			acf_render_field_setting($field, $setting);
		}
	}

	/**
	 * Do not delete the sub fields since they are referenced
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   object $field acf field
	 */
	public function delete_field($field)
	{
		return $field;
	}

	/**
	 * Do not duplicate the sub field since they are referenced
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   object $field acf field
	 */
	public function duplicate_field($field)
	{
		return $field;
	}

	/**
	 * Inject the selected field group's field as sub fields
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   object $field acf field
	 */
	public function load_field($field)
	{
		$field['sub_fields'] = $this->fetchSubFields($field);

		return $field;
	}

	/**
	 * Add appearance classes to the field's wrappepr. Adjust the min and max's
	 * value if repeatable is set to false
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   object $field acf field
	 */
	public function prepare_field($field)
	{
		$appearances = acf_maybe_get($field, 'appearances')? : [];

        foreach ($appearances as $appearance) {
            $field['wrapper']['class'] .= " acf-$appearance";
        }

		if (! $field['repeatable']) {
			$field['min'] = $field['max'] = 1;
		}

        return $field;
	}

	/**
	 * Show some error message if the field group cannot be found or not selected
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   object $field acf field
	 */
	public function render_field($field)
	{
		if (! $field['field_group_key']) {
			_e('A field group is not set on this component field.', 'acf-component_field');
			return;
		}

		if (! acf_get_field_group($field['field_group_key'])) {
			_e('Unable to locate the selected field group.', 'acf-component_field');
			return;
		}

		parent::render_field($field);
	}

	/**
	 * Ajax callbaack to output the additional component settings after a
	 * field group has been selected. The default values from the component
	 * field group will be used.
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function loadComponentSettings()
	{
		$ajaxErrorFormat = '
			<tr class="acf-field" data-setting="component_field">
				<td class="acf-label"></td>
				<td class="acf-input acf-required"><span class="dashicons dashicons-warning"></span> %s</td>
			</tr>
		';

		if (! acf_verify_ajax()) {
			printf($ajaxErrorFormat, __('Session expired, please refresh the page and try again.', 'acf-component_field'));
			wp_die();
		}

		if (! $fieldGroup = acf_get_field_group(acf_maybe_get_POST('field_group_key'))) {
			printf($ajaxErrorFormat, __('Unable to find the selected field group, please refresh the page and try again.', 'acf-component_field'));
			wp_die();
		}

		$groupDefault     = $this->defaults;
		$componentDefault = acf_maybe_get($fieldGroup, 'acf_component_defaults');
		$value            = wp_parse_args($componentDefault, $groupDefault);

		foreach ($this->componentSettingFields() as $setting) {
			$setting['value'] = acf_maybe_get($value, $setting['name']);

			acf_render_field_setting([
				'type' => $this->name,
				'prefix' => acf_maybe_get_POST('prefix')
			], $setting);
		}

		wp_die();
	}

	/**
	 * Dynamically list the available component field groups to the select field
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   object $field acf field
	 */
	public function filterAvaliableFieldGroups($field)
	{
		if (acf_maybe_get($field, 'acf-component_field::select_group')) {
			$componentGroups  = acf_component_field('query')->getComponents();
			$field['choices'] = wp_list_pluck($componentGroups, 'title', 'key');
		}

		return $field;
	}

	/**
	 * Do not include the referenced sub fields when exporting/duplicating the
	 * component field
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   object $field acf field
	 */
	public function removeSubfieldsOnExport($field)
	{
		$field['sub_fields'] = [];

		return $field;
	}

	/**
	 * The additional field settings properties
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function componentSettingFields()
	{
		return [
			[
				'label'         => __('Repeatable', 'acf-component_field'),
				'instructions'  => __('Can this component be self repeated?', 'acf-component_field'),
				'type'          => 'true_false',
				'ui'             => true,
				'name'          => 'repeatable',
			],
			[
				'label'         => __('Minimum Rows', 'acf'),
				'instructions'  => '',
				'type'          => 'number',
				'name'          => 'min',
				'placeholder'   => '0',
				'conditions'    => [
					'field'    => 'repeatable',
					'operator' => '==',
					'value'    => 1
				]
			],
			[
				'label'         => __('Maximum Rows', 'acf'),
				'instructions'  => '',
				'type'          => 'number',
				'name'          => 'max',
				'placeholder'   => '0',
				'conditions'    => [
					'field'    => 'repeatable',
					'operator' => '==',
					'value'    => 1
				]
			],
			[
				'label'         => __('Layout', 'acf'),
				'instructions'  => '',
				'class'         => 'acf-repeater-layout',
				'type'          => 'radio',
				'name'          => 'layout',
				'layout'        => 'horizontal',
				'choices'       => [
					'table'     => __('Table', 'acf'),
					'block'     => __('Block', 'acf'),
					'row'       => __('Row', 'acf')
				]
			],
			[
				'label'         => __('Button Label', 'acf'),
				'instructions'  => '',
				'type'          => 'text',
				'name'          => 'button_label',
				'placeholder'    => __('Add Row', 'acf')
			],
			[
				'label'         => __('Component Appearances', 'acf-component_field'),
				'instructions'  => sprintf(
					'%s <br/>%s',
					__('Alternative styles for component box.', 'acf-component_field'),
					__('(usually used for non-repeating nesting components)', 'acf-component_field')
				),
				'type'          => 'checkbox',
				'name'          => 'appearances',
				// 'layout'        => 'horizontal',
				'choices'       => [
					'hide-outer-boundary' => __('Hide outer boundary', 'acf-component_field'),
					'hide-outer-label'    => __('Hide outer field label', 'acf-component_field')
				]
			],
		];
	}

	/**
	 * Fetch the givent field'd selected field group, and return its fields
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   object $field acf field
	 */
	protected function fetchSubFields($field)
	{
		$groupKey = $field['field_group_key'];

		$fieldGroup = acf_component_field('query')->getComponent($groupKey);

		return acf_get_fields($fieldGroup);
	}
}
