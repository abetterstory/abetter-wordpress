<?php
	global $post;

    $checked = is_acf_component_field_group($post)? 'checked' : '';
    $name = 'acf_field_group[is_acf_component]';
?>

<input type="hidden" name="<?php echo $name; ?>" value="0" />
<label>
	<input
		id="is_acf_component_checkbox"
		type="checkbox"
		name="<?php echo $name; ?>"
		value="1"
		autocomplete="off"
		<?php echo $checked; ?>
	/>
	<?php _e('this is a component', 'acf-component_field'); ?>
</label>
