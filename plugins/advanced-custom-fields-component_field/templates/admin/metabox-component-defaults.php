<?php
	global $field_group;

	$prefix           = 'acf_field_group[acf_component_defaults]';
	$componentField   = acf_get_field_type('component_field');
	$groupDefault     = $componentField->defaults;
	$componentDefault = acf_maybe_get($field_group, 'acf_component_defaults');
	$value            = wp_parse_args($componentDefault, $groupDefault);

	foreach ($componentField->componentSettingFields() as $setting) {
		acf_render_field_wrap(wp_parse_args([
			'conditions' => [],
			'prefix'     => $prefix,
			'value'      => acf_maybe_get($value, $setting['name'])
		], $setting));
	}
?>

<script type="text/javascript">
if( typeof acf !== 'undefined' ) {
	acf.newPostbox({
		'id': 'acf-component-field-default-metabox',
		'label': 'left'
	});
}
</script>
