<?php
	$options = [
		'repeater' => sprintf('%s %s', __('Repeater', 'acf'), __('Field', 'acf')),
		'component_field' => __('Component Field', 'acf-component_field')
	];

	$convertTo = $field['type'] == 'component_field'? 'repeater' : 'component_field';
	$componentUsedCount = acf_maybe_get($field, 'field_group_key')? acf_component_field('query')->getUsedCount($field['field_group_key']) : 0;

?>
<form method="post">
	<p>
		<?php printf(__('You are about to convert this field to:', 'acf-component_field')); ?>
		<b><pre><?php echo $options[$convertTo]; ?></pre></b>
	</p>

	<p style="font-size: 12px; font-style: italic; color: #777;">
		<?php
			if ($convertTo == 'repeater') {
				printf(
					__('Converting to a %s will duplicate %s\'s fields to this field as sub-fields.', 'acf-component_field'),
					$options['repeater'],
					$options['component_field']
				);
			} else {
				printf(
					__('Converting to a %s will create a new field group, and move this field\'s sub fields to it.', 'acf-component_field'),
					$options['component_field']
				);
			}
		?>
	</p>

	<?php if ($field['type'] == 'component_field' && $componentUsedCount == 1): ?>
		<?php
			acf_render_field_wrap([
				'type' => 'true_false',
				'name' => 'delete-component',
				'ui'   => true,
				'message' => __('Delete the component afterwards.', 'acf-component_field')
			]);
		?>
	<?php endif; ?>

	<p class="acf-submit">
		<input type="hidden" name="convert-to" value="<?php echo $convertTo; ?>" />
		<button class="acf-submit-button button button-primary" type="submit"><?php _e('Convert', 'acf-component_field'); ?></button>
	</p>
</form>
