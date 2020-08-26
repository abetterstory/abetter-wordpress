
<p><?php _e('Manually re-run the database migration. In some cases, the database update is incomplete or get interrupted when switching ACF Component Field plugin to new version. You can trigger the migration manully here.', 'acf-component_field'); ?></p>

<div class="acf-fields">
	<div class="acf-field">
		<b><?php _e('Current Database Version:', 'acf-component_field'); ?></b>
		<?php echo acf_component_field()->upgrader->getDbVersion(); ?>
	</div>
</div>

<p class="acf-submit">
	<input type="submit" class="button button-primary" value="<?php _e('Re-run Migration', 'acf-component_field'); ?>" />
</p>
