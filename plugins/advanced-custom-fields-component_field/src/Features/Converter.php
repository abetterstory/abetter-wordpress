<?php

namespace GummiIO\AcfComponentField\Features;

/**
 * Converter Class
 *
 * Class that handles converting field between repeater and component field
 *
 * @since       2.0.0
 * @version     2.0.2
 */
class Converter
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
	 * Register ajax hooks to handle field conversion checking and operation
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function registerHooks()
	{
        add_action('wp_ajax_acf/component_field/features/convert_check', [$this, 'checkComponentConvertStatus']);
        add_action('wp_ajax_acf/component_field/features/convert', [$this, 'converField']);
	}

	/**
	 * Check if the selected field is valid for conversion
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function checkComponentConvertStatus()
	{
		$this->verifyAjax();

		$field = $this->verifyField();

		if (! in_array(acf_maybe_get($field, 'type'), ['repeater', 'component_field'])) {
			$this->errorOut(__('Invalid field type, this field type cannot be converted.', 'acf-component_field'));
        }

        if (! acf_maybe_get($field, 'sub_fields')) {
			$this->errorOut(__('This field does not contain any sub fields.', 'acf-component_field'));
        }

        require acf_component_field()->path('templates/popups/convert-settings.php');

        wp_die();
	}

	/**
	 * Convert the selected field to either repeater or component field
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	public function converField()
	{
		$this->verifyAjax();

		$field = $this->verifyField();

		acf_disable_filters();

		if (acf_maybe_get_POST('convert_to') == 'component_field') {
			$this->convertToComponentField($field);
		}

		$this->convertToRepeaterField($field);
	}

	/**
	 * Convert the passed field to component field
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   object $field The repeater field that will be converted
	 */
	protected function convertToComponentField($field)
	{
		$args = [
			'title' => sprintf(__('Component: %s', 'acf-component_field'), $field['label']),
			'is_acf_component' => true,
			'acf_component_defaults' => []
		];

		foreach (acf_get_field_type('component_field')->componentSettingFields() as $setting) {
			$args['acf_component_defaults'][$setting['name']] = acf_maybe_get($field, $setting['name']);
		}

		if ($field['min'] == $field['max'] && $field['min'] == 1) {
			$args['acf_component_defaults']['repeatable'] = false;
		}

		$newGroup = acf_update_field_group($args);
		acf_component_field('query')->addComponent($newGroup);

		foreach (acf_get_fields($field) as $subField) {
			$subField['parent'] = $newGroup['ID'];
			$subField['conditional_logic'] = 0;
			acf_update_field($subField);
		}

		$field['type'] = 'component_field';
		$field['field_group_key'] = $newGroup['key'];
		acf_update_field($field);

		ob_start();

		$field = acf_get_field($field['ID'], true);

		acf_get_view('field-group-field', ['field' => $field, 'i' => acf_maybe_get_POST('order', 0)]);

		$link = sprintf('<a href="%s" target="_blank">%s</a>', admin_url("post.php?post={$newGroup['ID']}&action=edit"), $newGroup['title']);
		$popup = sprintf('
			<div>
				<p><strong>%s</</strong></p>
				<p>%s</p>
				<a href="#" class="button button-primary acf-close-popup">%s</a>
			</div>',
			__('Field Converted.', 'acf-component_field'),
			sprintf(__('The converted component field can now be found at %s', 'acf-component_field'), $link),
			__('Close Window', 'acf')
		);

		wp_send_json_success([
			'field' => ob_get_clean(),
			'popup' => $popup
		]);
	}

	/**
	 * Convert the passed component field to repeater field
	 *
     * @since   2.0.0
     * @version 2.0.2
	 * @param   object $field The component field that will be converted
	 */
	protected function convertToRepeaterField($field)
	{
		$componentGroup = acf_component_field('query')->getComponent($field['field_group_key']);

        if (! $componentGroup) {
			$this->errorOut(__('Unable find the selected component field gorup in this field.', 'acf-component_field'), true);
        }

		foreach (acf_get_fields($componentGroup) as $subField) {
			acf_duplicate_field($subField['key'], $field['ID']);
		}

		$field['type'] = 'repeater';
		$field['field_group_key'] = '';
		acf_update_field($field);

		if (acf_maybe_get_POST('delete_component') == 'true') {
			acf_delete_field_group($componentGroup['key']);
		}

		ob_start();

		$field = acf_get_field($field['ID'], true);
		$field['sub_fields'] = acf_get_fields($field); // not sure why sub fields can't be loaded

		acf_get_view('field-group-field', ['field' => $field, 'i' => acf_maybe_get_POST('order', 0)]);

		$popup = sprintf('
			<div>
				<p><strong>%s</</strong></p>
				<p>%s</p>
				<a href="#" class="button button-primary acf-close-popup">%s</a>
			</div>',
			__('Field Converted.', 'acf-component_field'),
			__('This field has been converted to repeater field.', 'acf-component_field'),
			__('Close Window', 'acf')
		);

		wp_send_json_success([
			'field' => ob_get_clean(),
			'popup' => $popup
		]);
	}

	/**
	 * Verify nonce for acf's ajax call
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	protected function verifyAjax()
	{
		if (! acf_verify_ajax()) {
			$this->errorOut(__('Session expired, please refresh the page and try again.', 'acf-component_field'));
		}
	}

	/**
	 * Verify if the selected field exists
	 *
     * @since   2.0.0
     * @version 2.0.0
	 */
	protected function verifyField()
	{
		if (! $field = acf_get_field(acf_maybe_get_POST('field_key'))) {
			$this->errorOut(__('Unable to load selected field, please refresh the page and try again.', 'acf-component_field'));
        }

        return $field;
	}

	/**
	 * Helper function to throw error back from the ajax request
	 *
     * @since   2.0.0
     * @version 2.0.0
	 * @param   string  $message The error message
	 * @param   boolean $json    If the return type should be json or html
	 */
	protected function errorOut($message, $json = false)
	{
		$html = sprintf(
			'<p><strong>%s</strong> %s</p>',
			__('Error.', 'acf'),
			$message
		);

		if ($json) {
			wp_send_json_success([
				'popup' => $html,
			]);
		}

		echo $html;
		wp_die();
	}
}
