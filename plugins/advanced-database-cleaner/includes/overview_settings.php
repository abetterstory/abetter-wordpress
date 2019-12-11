<?php
global $wpdb, $wp_version;
// DB size
$aDBc_db_size = $wpdb->get_var("SELECT sum(round(((data_length + index_length) / 1024), 2)) FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
if($aDBc_db_size >= 1024){
	$aDBc_db_size = round(($aDBc_db_size / 1024), 2) . " MB";
}else{
	$aDBc_db_size = round($aDBc_db_size, 2) . " KB";
}
// Total unused data
$aDBc_unused_elements = aDBc_count_all_elements_to_clean();
$aDBc_total_unused = 0;
foreach($aDBc_unused_elements as $element_type => $element_info){
	// Count total unused elements. DO not take into account transient with expiration and not expiring transients because they are not intended to be cleaned
	if($element_type != "transients-with-expiration" && $element_type != "transients-with-no-expiration")
		$aDBc_total_unused += $element_info['count'];
}
// Total tables
$aDBc_total_tables = $wpdb->get_var("SELECT count(*) FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
// Total options
if(function_exists('is_multisite') && is_multisite()){
							 
	$aDBc_options_toolip = "<span class='aDBc-tooltips-headers'>
					<img class='aDBc-info-image' src='".  ADBC_PLUGIN_DIR_PATH . '/images/information2.svg' . "'/>
					<span>" . __('Indicates the total number of rows in your option tables of all your network sites, including transients...','advanced-database-cleaner') ." </span>
				  </span>";	
				  
}else{
							 
	$aDBc_options_toolip = "<span class='aDBc-tooltips-headers'>
					<img class='aDBc-info-image' src='".  ADBC_PLUGIN_DIR_PATH . '/images/information2.svg' . "'/>
					<span>" . __('Indicates the total number of rows in your options table, including transients...','advanced-database-cleaner') ." </span>
				  </span>";				 
							 
}
// Total options
$aDBc_total_options = 0;
if(function_exists('is_multisite') && is_multisite()){
	$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
	foreach($blogs_ids as $blog_id){
		switch_to_blog($blog_id);
		global $wpdb;
		$aDBc_total_options += $wpdb->get_var("SELECT count(*) FROM $wpdb->options");
		restore_current_blog();
	}
}else{
	// Count total options
	$aDBc_total_options = $wpdb->get_var("SELECT count(*) FROM $wpdb->options");
}

// Total scheduled tasks
$aDBc_all_tasks = aDBc_get_all_scheduled_tasks();
$aDBc_total_tasks = 0;
if(function_exists('is_multisite') && is_multisite()){
	foreach($aDBc_all_tasks as $hook => $task_info){
		foreach($task_info['sites'] as $site => $info){
			$aDBc_total_tasks += count($task_info['sites'][$site]['args']);
		}
	}
}else{
	foreach($aDBc_all_tasks as $hook => $task_info){
		$aDBc_total_tasks += count($task_info['sites'][1]['args']);
	}
}

// Total tables to optimize
$aDBc_tables_to_optimize = $wpdb->get_var("SELECT count(*) FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "' and Engine <> 'InnoDB' and data_free > 0");

// Is MU?
if(function_exists('is_multisite') && is_multisite()){
	$aDBc_is_mu = __('Yes', 'advanced-database-cleaner');
	$aDBc_number_sites = $wpdb->get_var("SELECT count(*) FROM $wpdb->blogs");
}else{
	$aDBc_is_mu = __('No', 'advanced-database-cleaner');
	$aDBc_number_sites = "1";
}

// Get settings
global $aDBc_settings;
if(isset($_POST['save_settings'])){
	if(isset($_POST['aDBc_left_menu']) || isset($_POST['aDBc_menu_under_tools'])){
		echo '<div id="aDBc_message" class="updated notice is-dismissible"><p>' . __('Settings saved successfully!', 'advanced-database-cleaner') . '</p></div>';
	}else{
		echo '<div id="aDBc_message" class="updated notice is-dismissible"><p>' . __('Settings saved successfully! The default position of the plugin menu is the left side of the admin panel', 'advanced-database-cleaner') . '</p></div>';
	}
}

?>

<div class="aDBc-content-max-width">
	<div class="aDBc-overview-box">
		<div class="aDBc-overview-box-head"><?php _e('Overview', 'advanced-database-cleaner'); ?></div>
		<ul class="aDBc-overview-box-line">
			<li>
				<div class="aDBc-overview-text-left"><?php _e('WP Version', 'advanced-database-cleaner'); ?> :</div>
				<div class="aDBc-overview-text-right"><?php echo $wp_version; ?></div>
			</li>		
			<li>
				<div class="aDBc-overview-text-left"><?php _e('Database size', 'advanced-database-cleaner'); ?> :</div>
				<div class="aDBc-overview-text-right"><?php echo $aDBc_db_size; ?></div>
			</li>
			<li>
				<div class="<?php echo $aDBc_total_unused > 0 ? 'aDBc-overview-text-left-warning' : 'aDBc-overview-text-left'; ?>"><?php _e('Total unused data', 'advanced-database-cleaner'); ?> :</div>
				<div class="aDBc-overview-text-right"><?php echo $aDBc_total_unused; ?></div>
			</li>
			<li>
				<div class="aDBc-overview-text-left"><?php _e('Total tables', 'advanced-database-cleaner'); ?> :</div>
				<div class="aDBc-overview-text-right"><?php echo $aDBc_total_tables; ?></div>
			</li>
			<li>
				<div class="<?php echo $aDBc_tables_to_optimize > 0 ? 'aDBc-overview-text-left-warning' : 'aDBc-overview-text-left'; ?>"><?php _e('Tables to optimize', 'advanced-database-cleaner'); ?> :</div>
				<div class="aDBc-overview-text-right"><?php echo $aDBc_tables_to_optimize; ?></div>
			</li>
			<li>
				<div class="aDBc-overview-text-left"><?php echo __('Total options', 'advanced-database-cleaner') . $aDBc_options_toolip; ?> : </div>
				<div class="aDBc-overview-text-right"><?php echo $aDBc_total_options; ?></div>
			</li>
			<li>
				<div class="aDBc-overview-text-left"><?php _e('Total cron tasks', 'advanced-database-cleaner'); ?> :</div>
				<div class="aDBc-overview-text-right"><?php echo $aDBc_total_tasks; ?></div>
			</li>
			<li>
				<div class="aDBc-overview-text-left"><?php _e('WP multisite Enabled ?', 'advanced-database-cleaner'); ?></div>
				<div class="aDBc-overview-text-right"><?php echo $aDBc_is_mu; ?></div>
			</li>
			<li>
				<div class="aDBc-overview-text-left"><?php _e('Number of sites', 'advanced-database-cleaner'); ?> :</div>
				<div class="aDBc-overview-text-right"><?php echo $aDBc_number_sites; ?></div>
			</li>
			<li>
				<div class="aDBc-overview-text-left"><?php _e('Script Max timeout', 'advanced-database-cleaner'); ?> :</div>
				<div class="aDBc-overview-text-right"><?php echo ADBC_ORIGINAL_TIMEOUT . " ". __('seconds', 'advanced-database-cleaner'); ?></div>
			</li>
			<li>
				<div class="aDBc-overview-text-left"><?php _e('Local time', 'advanced-database-cleaner'); ?> :</div>
				<div class="aDBc-overview-text-right"><?php echo date_i18n('Y-m-d H:i:s'); ?></div>
			</li>
		</ul>
	</div>

	<div class="aDBc-overview-box">
		<div class="aDBc-overview-box-head"><?php _e('Settings', 'advanced-database-cleaner'); ?></div>

		<form action="" method="post">
			<ul class="aDBc-overview-box-line" style="margin-left:0px">

				<li style="background:#f9f9f9;margin-bottom:0px;padding:10px">

					<?php 
					if((!empty($aDBc_settings['left_menu']) && $aDBc_settings['left_menu'] == '1') || 
						(empty($aDBc_settings['left_menu']) && empty($aDBc_settings['menu_under_tools']))){
						$aDBc_checked = "checked='checked'";
					}else{
						$aDBc_checked = "";
					}
					?>

					<input type="checkbox" name="aDBc_left_menu" <?php echo $aDBc_checked ?>/>
					<?php _e('Show plugin left menu', 'advanced-database-cleaner'); ?>
					<div class="aDBc-overview-setting-desc">
						<?php _e('Displays a menu at the left side of your WP admin', 'advanced-database-cleaner'); ?>
					</div>
				</li>

				<li style="background:#f9f9f9;margin-top:0px;padding:10px">
					<input type="checkbox" name="aDBc_menu_under_tools" <?php echo (!empty($aDBc_settings['menu_under_tools']) && $aDBc_settings['menu_under_tools'] == '1') ? "checked='checked'" : ""?>/>
					<?php _e('Show plugin menu under tools', 'advanced-database-cleaner'); ?>
					<div class="aDBc-overview-setting-desc">
						<?php _e('Displays a menu under "tools" menu', 'advanced-database-cleaner'); ?>
					</div>
				</li>

				<?php 
				if(ADBC_PLUGIN_F_TYPE == "free"){
				?>
					<li>
						<input type="checkbox" name="aDBc_hide_premium_tab" <?php echo (!empty($aDBc_settings['hide_premium_tab']) && $aDBc_settings['hide_premium_tab']) == '1' ? "checked='checked'" : ""?>/>
						<?php _e('Hide premium tab', 'advanced-database-cleaner'); ?>
						<div class="aDBc-overview-setting-desc">
							<?php _e('If checked, it will hide the above premium tab', 'advanced-database-cleaner'); ?>
						</div>
					</li>
					
				<?php	
				}
				?>
			
			</ul>
			<input name="save_settings" type="submit" class="button-primary aDBc-save-settings-button" value="<?php _e('Save settings','advanced-database-cleaner'); ?>" />
		</form>
	</div>
	<div class="aDBc-clear-both"></div>
</div>