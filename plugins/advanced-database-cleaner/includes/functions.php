<?php
/** If we are in MU, define the main blog ID. This will be usefull to call get_option correctly when we switch between blogs */
/** Used mainly in aDBc_get_keep_last_sql_arg() */
if(function_exists('is_multisite') && is_multisite()){
	if(!defined("ADBC_MAIN_SITE_ID")) 		define("ADBC_MAIN_SITE_ID", get_current_blog_id());
}

/** Reduces the value of the string in parameter according to max lenght then create a tooltip for it */
function aDBc_create_tooltip_for_long_string($string_value, $max_characters){
	$new_name = esc_html($string_value);
	if(strlen($new_name) > $max_characters){
		$new_name = trim(substr($new_name, 0, $max_characters));
		$new_name = $new_name . " <span class='aDBc-tooltips'>" . "..." . "<span>" . esc_html($string_value) . "</span></span>";
	}
	return $new_name;
}

/** Reduces the value of the string in parameter according to max lenght then create a tooltip for it */
function aDBc_create_tooltip_by_replace($string_value, $max_characters, $tooltip_content){
	$new_name = $string_value;
	if(strlen($new_name) > $max_characters){
		$new_name = substr($new_name, 0, $max_characters) . " ...";
	}
	$new_name = "<span class='aDBc-tooltips'>" . esc_html($new_name) . "<span>" . esc_html($tooltip_content) . "</span></span>";
	return $new_name;
}

/** Reduces the value of the option value according to max lenght then create a tooltip for it */
function aDBc_create_tooltip_for_option_value($string_value, $max_characters){

	$option_content = maybe_unserialize($string_value);
	if(is_array($option_content)){
		$new_name = "<i>Array</i>" . " <span class='aDBc-tooltips'>" . "..." . "<span>" . esc_html($string_value) . "</span></span>";
	}else if(gettype($option_content) == 'object'){
		$new_name = "<i>Object</i>" . " <span class='aDBc-tooltips'>" . "..." . "<span>" . esc_html($string_value) . "</span></span>";
	}else{
		$new_name = esc_html($string_value);
		if(strlen($new_name) > $max_characters){
			$new_name = trim(substr($new_name, 0, $max_characters));
			$new_name = $new_name . " <span class='aDBc-tooltips'>" . "..." . "<span>" . esc_html($string_value) . "</span></span>";
		}
	}
	return $new_name;
}

function aDBc_get_order_by_sql_arg($default_order_by){

		// Prepare ORDER BY and ORDER
		$order_by = " ORDER BY " . $default_order_by . " ASC";
		if(!empty($_GET['orderby'])){
			$order_by = " ORDER BY " . esc_sql($_GET['orderby']);
			$order_by .= empty($_GET['order']) ? " ASC" : " " . esc_sql($_GET['order']);
		}

		return $order_by;
}

function aDBc_get_limit_offset_sql_args(){

		// Identify current page for WP_List_Table
		$page_number = 1;
		if(!empty($_GET['paged'])){
			$page_number = absint($_GET['paged']);
		}

		// Identify items per page to display
		$per_page = 50;
		if(!empty($_GET['per_page'])){
			$per_page = absint($_GET['per_page']);
		}

		// Prepare LIMIT and OFFSET
		$offset = ($page_number - 1) * $per_page;
		$limit_offset = " LIMIT $offset,$per_page";

		return $limit_offset;
}

/** Cleans all elements in the current site and in MU according to the selected type */
function aDBc_clean_all_elements_type($type){
	global $wpdb;
	if(function_exists('is_multisite') && is_multisite()){
		$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach($blogs_ids as $blog_id){
			switch_to_blog($blog_id);
			aDBc_clean_elements_type($type);
			restore_current_blog();
		}
	}else{
		aDBc_clean_elements_type($type);
	}
}

/** Cleans all elements in the current site according to the selected type */
function aDBc_clean_elements_type($type){

	global $wpdb;

	switch($type){
		case "revision":
			$revision_date = aDBc_get_keep_last_sql_arg('revision','post_modified');
			$wpdb->query("DELETE FROM $wpdb->posts WHERE post_type = 'revision'" . $revision_date);
			break;
		case "auto-draft":
			$auto_draft_date = aDBc_get_keep_last_sql_arg('auto-draft','post_modified');
			$wpdb->query("DELETE FROM $wpdb->posts WHERE post_status = 'auto-draft'" . $auto_draft_date);
			break;
		case "trash-posts":
			$trash_post_date = aDBc_get_keep_last_sql_arg('trash-posts','post_modified');
			$wpdb->query("DELETE FROM $wpdb->posts WHERE post_status = 'trash'" . $trash_post_date);
			break;					
		case "moderated-comments":
			$moderated_comment_date = aDBc_get_keep_last_sql_arg('moderated-comments','comment_date');
			$wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = '0'" . $moderated_comment_date);
			break;
		case "spam-comments":
			$spam_comment_date = aDBc_get_keep_last_sql_arg('spam-comments','comment_date');
			$wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = 'spam'" . $spam_comment_date);
			break;
		case "trash-comments":
			$trash_comment_date = aDBc_get_keep_last_sql_arg('trash-comments','comment_date');
			$wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = 'trash'" . $trash_comment_date);
			break;
		case "pingbacks":
			$pingback_date = aDBc_get_keep_last_sql_arg('pingbacks','comment_date');
			$wpdb->query("DELETE FROM $wpdb->comments WHERE comment_type = 'pingback'" . $pingback_date);
			break;
		case "trackbacks":
			$trackback_date = aDBc_get_keep_last_sql_arg('trackbacks','comment_date');
			$wpdb->query("DELETE FROM $wpdb->comments WHERE comment_type = 'trackback'" . $trackback_date);
			break;			
		case "orphan-postmeta":
			$wpdb->query("DELETE pm FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL");
			break;
		case "orphan-commentmeta":
			$wpdb->query("DELETE FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_id FROM $wpdb->comments)");
			break;
		case "orphan-relationships":
			$wpdb->query("DELETE FROM $wpdb->term_relationships WHERE term_taxonomy_id=1 AND object_id NOT IN (SELECT id FROM $wpdb->posts)");
			break;
		case "orphan-usermeta":
			$wpdb->query("DELETE FROM $wpdb->usermeta WHERE user_id NOT IN (SELECT ID FROM $wpdb->users)");
			break;
		case "orphan-termmeta":
			$wpdb->query("DELETE FROM $wpdb->termmeta WHERE term_id NOT IN (SELECT term_id FROM $wpdb->terms)");
			break;
		case "expired-transients":
			$type_arg = " AND b.option_value < UNIX_TIMESTAMP()";
			aDBc_clean_all_transients($type_arg);
			break;
		//case "transients-with-expiration":
		//	$type_arg = " AND b.option_value > UNIX_TIMESTAMP()";
		//	aDBc_clean_all_transients($type_arg);
		//	break;
		//case "transients-with-no-expiration":
		//	$type_arg = " AND b.option_value is NULL";
		//	aDBc_clean_all_transients($type_arg);
		//	break;
	}
}

/** Cleans transients based on the type specified in parameter */
function aDBc_clean_all_transients($type_arg){

	global $wpdb;

	$aDBc_transients = $wpdb->get_results("SELECT a.option_name, b.option_value FROM $wpdb->options a LEFT JOIN $wpdb->options b ON b.option_name = 
	CONCAT(
		CASE WHEN a.option_name LIKE '_site_transient_%'
			THEN '_site_transient_timeout_'
			ELSE '_transient_timeout_'
		END
		,
		SUBSTRING(a.option_name, CHAR_LENGTH(
			CASE WHEN a.option_name LIKE '_site_transient_%'
			   THEN '_site_transient_'
			   ELSE '_transient_'
			END
		) + 1)
	)
	WHERE (a.option_name LIKE '_transient_%' OR a.option_name LIKE '_site_transient_%') AND a.option_name NOT LIKE '%_transient_timeout_%'" . $type_arg);

	foreach($aDBc_transients as $transient){
		$site_wide = (strpos($transient->option_name, '_site_transient') !== false);
		$name = str_replace($site_wide ? '_site_transient_' : '_transient_', '', $transient->option_name);
		if(false !== $site_wide){
			delete_site_transient($name);
		}else{
			delete_transient($name);
		}
	}
}

/** Cleans scheduled elements in the current site and in MU (used by the scheduler) */
function aDBc_clean_scheduled_elements($schedule_name){

	global $wpdb;

	$schedule_settings = get_option('aDBc_clean_schedule');

	if(is_array($schedule_settings) && array_key_exists($schedule_name, $schedule_settings)){

		$schedule_params = $schedule_settings[$schedule_name];
		$elements_to_clean = $schedule_params['elements_to_clean'];
		
		if(function_exists('is_multisite') && is_multisite()){
			$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
			foreach($blogs_ids as $blog_id){
				switch_to_blog($blog_id);
				foreach($elements_to_clean as $type){
					aDBc_clean_elements_type($type);
				}
				restore_current_blog();
			}
		}else{
			foreach($elements_to_clean as $type){
				aDBc_clean_elements_type($type);
			}
		}

		// After clean up, verify if the caller is a "ONCE" schedule, if so, change its settings in DB to inactive...
		if($schedule_params['repeat'] == "once"){
			$schedule_params['active'] = "0";
			$schedule_settings[$schedule_name] = $schedule_params;
			update_option('aDBc_clean_schedule', $schedule_settings, "no");
		}
	}
}

/** Optimizes/repairs all tables having lost space or that should be repaired (used by the scheduler) */
function aDBc_optimize_scheduled_tables($schedule_name){

	global $wpdb;

	$schedule_settings = get_option('aDBc_optimize_schedule');

	if(is_array($schedule_settings) && array_key_exists($schedule_name, $schedule_settings)){

		$schedule_params = $schedule_settings[$schedule_name];
		$operations = $schedule_params['operations'];

		// Perform optimize operation
		if(in_array("optimize", $operations)){
			$result = $wpdb->get_results("SELECT table_name FROM information_schema.tables WHERE table_schema = '" . DB_NAME ."' and Engine <> 'InnoDB' and data_free > 0");
			foreach($result as $table){

				// Get table name
				$table_name = "";
				// This test to prevent issues in MySQL 8 where tables are not shown
				// MySQL 5 uses $table->table_name while MySQL 8 uses $table->TABLE_NAME
				if(property_exists($table, "table_name")){
					$table_name = $table->table_name;
				}else if(property_exists($table, "TABLE_NAME")){
					$table_name = $table->TABLE_NAME;
				}

				$wpdb->query("OPTIMIZE TABLE " . $table_name);			
			}
		}

		// Perfom repair operation
		if(in_array("repair", $operations)){
			$result = $wpdb->get_results("SELECT table_name FROM information_schema.tables WHERE table_schema = '" . DB_NAME ."' and Engine IN ('CSV', 'MyISAM', 'ARCHIVE')");
			foreach($result as $table){

				// Get table name
				$table_name = "";
				// This test to prevent issues in MySQL 8 where tables are not shown
				// MySQL 5 uses $table->table_name while MySQL 8 uses $table->TABLE_NAME
				if(property_exists($table, "table_name")){
					$table_name = $table->table_name;
				}else if(property_exists($table, "TABLE_NAME")){
					$table_name = $table->TABLE_NAME;
				}

				$query_result = $wpdb->get_results("CHECK TABLE " . $table_name);
				foreach($query_result as $row){
					if($row->Msg_type == 'error'){
						if(preg_match('/corrupt/i', $row->Msg_text)){
							$wpdb->query("REPAIR TABLE " . $table_name);
						}
					}
				}
			}
		}

		// After optimization/repair, verify if the caller is a "ONCE" schedule, if so, change its settings in DB to inactive...
		if($schedule_params['repeat'] == "once"){
			$schedule_params['active'] = "0";
			$schedule_settings[$schedule_name] = $schedule_params;
			update_option('aDBc_optimize_schedule', $schedule_settings, "no");
		}
	}
}

/** Returns an array containing all elements in general cleanup tab with their names used in the code */
function aDBc_return_array_all_elements_to_clean(){

	$aDBc_unused["revision"]['name'] 						= __('Revisions','advanced-database-cleaner');
	$aDBc_unused["revision"]['URL_blog'] 					= "https://sigmaplugin.com/blog/what-are-wordpress-revisions-and-how-to-clean-them";
	$aDBc_unused["auto-draft"]['name'] 						= __('Auto drafts','advanced-database-cleaner');
	$aDBc_unused["auto-draft"]['URL_blog'] 					= "https://sigmaplugin.com/blog/what-are-wordpress-auto-drafts-and-how-to-clean-them";
	$aDBc_unused["trash-posts"]['name'] 					= __('Trashed posts','advanced-database-cleaner');
	$aDBc_unused["trash-posts"]['URL_blog'] 				= "https://sigmaplugin.com/blog/what-are-wordpress-trash-posts-and-how-to-clean-them";
	
	$aDBc_unused["moderated-comments"]['name'] 				= __('Pending comments','advanced-database-cleaner');
	$aDBc_unused["moderated-comments"]['URL_blog'] 			= "https://sigmaplugin.com/blog/what-are-wordpress-pending-comments-and-how-to-clean-them";
	$aDBc_unused["spam-comments"]['name'] 					= __('Spam comments','advanced-database-cleaner');
	$aDBc_unused["spam-comments"]['URL_blog'] 				= "https://sigmaplugin.com/blog/what-are-wordpress-spam-comments-and-how-to-clean-them";
	$aDBc_unused["trash-comments"]['name'] 					= __('Trashed comments','advanced-database-cleaner');
	$aDBc_unused["trash-comments"]['URL_blog'] 				= "https://sigmaplugin.com/blog/what-are-wordpress-trash-comments-and-how-to-clean-them";
	
	$aDBc_unused["pingbacks"]['name'] 						= __('Pingbacks','advanced-database-cleaner');
	$aDBc_unused["pingbacks"]['URL_blog'] 					= "https://sigmaplugin.com/blog/what-are-wordpress-pingbacks-and-how-to-clean-them";
	$aDBc_unused["trackbacks"]['name'] 						= __('Trackbacks','advanced-database-cleaner');
	$aDBc_unused["trackbacks"]['URL_blog'] 					= "https://sigmaplugin.com/blog/what-are-wordpress-trackbacks-and-how-to-clean-them";
	
	$aDBc_unused["orphan-postmeta"]['name'] 				= __('Orphaned post meta','advanced-database-cleaner');
	$aDBc_unused["orphan-postmeta"]['URL_blog'] 			= "https://sigmaplugin.com/blog/what-are-wordpress-orphan-posts-meta-and-how-to-clean-them";
	$aDBc_unused["orphan-commentmeta"]['name'] 				= __('Orphaned comment meta','advanced-database-cleaner');
	$aDBc_unused["orphan-commentmeta"]['URL_blog'] 			= "https://sigmaplugin.com/blog/what-are-wordpress-orphan-comments-meta-and-how-to-clean-them";
	$aDBc_unused["orphan-usermeta"]['name'] 				= __('Orphaned user meta','advanced-database-cleaner');
	$aDBc_unused["orphan-usermeta"]['URL_blog'] 			= "https://sigmaplugin.com/blog/what-are-wordpress-orphaned-user-meta-and-how-to-clean-them";
	$aDBc_unused["orphan-termmeta"]['name'] 				= __('Orphaned term meta','advanced-database-cleaner');
	$aDBc_unused["orphan-termmeta"]['URL_blog'] 			= "https://sigmaplugin.com/blog/what-are-wordpress-orphaned-term-meta-and-how-to-clean-them";
	
	$aDBc_unused["orphan-relationships"]['name'] 			= __('Orphaned relationships','advanced-database-cleaner');
	$aDBc_unused["orphan-relationships"]['URL_blog'] 		= "https://sigmaplugin.com/blog/what-are-wordpress-orphan-relationships-and-how-to-clean-them";

	$aDBc_unused["expired-transients"]['name'] 				= __("Expired transients","advanced-database-cleaner");
	$aDBc_unused["expired-transients"]['URL_blog'] 			= "https://sigmaplugin.com/blog/what-are-wordpress-transients";
	
	
	/*$aDBc_transient_toolip = "<span class='aDBc-tooltips-headers'>
								<img style='width:12px' class='aDBc-info-image' src='".  ADBC_PLUGIN_DIR_PATH . '/images/information2.svg' . "'/>
								<span>" . __('Do not clean these items unless you know what you are doing!','advanced-database-cleaner') ." </span>
							</span>";
	
	$aDBc_unused["transients-with-expiration"]['name'] 		= __("Transients with an expiration","advanced-database-cleaner") . "<br><span class='aDBc-caution'>" . __("Use with caution!","advanced-database-cleaner") . "</span>" . $aDBc_transient_toolip;
	$aDBc_unused["transients-with-expiration"]['URL_blog'] 	= "https://sigmaplugin.com/blog/what-are-wordpress-transients";
	
	$aDBc_unused["transients-with-no-expiration"]['name'] 	= __("Transients with no expiration","advanced-database-cleaner") . "<br><span class='aDBc-caution'>" . __("Use with caution!","advanced-database-cleaner") . "</span>" . $aDBc_transient_toolip;
	$aDBc_unused["transients-with-no-expiration"]['URL_blog'] 	= "https://sigmaplugin.com/blog/what-are-wordpress-transients";*/
	
	return $aDBc_unused;

}

/** Counts all elements to clean (in the current site or MU) */
function aDBc_count_all_elements_to_clean(){
	global $wpdb;
	$aDBc_unused = aDBc_return_array_all_elements_to_clean();

	// Initialize counts to 0
	foreach($aDBc_unused as $aDBc_type => $element_info){
		$aDBc_unused[$aDBc_type]['count'] = 0;
	}

	//(for the table usermeta, only one table exists for MU, do not witch over blogs for it)
	if(function_exists('is_multisite') && is_multisite()){
		$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach($blogs_ids as $blog_id){
			switch_to_blog($blog_id);
			aDBc_count_elements_to_clean($aDBc_unused);	
			restore_current_blog();
		}
	}else{
		aDBc_count_elements_to_clean($aDBc_unused);
	}
	return $aDBc_unused;
}

/** Counts elements to clean in the current site */
function aDBc_count_elements_to_clean(&$aDBc_unused){

	global $wpdb;

	// Test if there are any keep_last options to count only elements with date < keep_lat to add it to the query
	$revision_date 			= aDBc_get_keep_last_sql_arg('revision','post_modified');
	$auto_draft_date 		= aDBc_get_keep_last_sql_arg('auto-draft','post_modified');
	$trash_post_date 		= aDBc_get_keep_last_sql_arg('trash-posts','post_modified');
	$moderated_comment_date = aDBc_get_keep_last_sql_arg('moderated-comments','comment_date');
	$spam_comment_date 		= aDBc_get_keep_last_sql_arg('spam-comments','comment_date');
	$trash_comment_date 	= aDBc_get_keep_last_sql_arg('trash-comments','comment_date');
	$pingback_date 			= aDBc_get_keep_last_sql_arg('pingbacks','comment_date');
	$trackback_date 		= aDBc_get_keep_last_sql_arg('trackbacks','comment_date');

	// Execute count queries
	$aDBc_unused["revision"]['count'] += $wpdb->get_var("SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = 'revision'" . $revision_date);
	$aDBc_unused["auto-draft"]['count'] += $wpdb->get_var("SELECT COUNT(ID) FROM $wpdb->posts WHERE post_status = 'auto-draft'" . $auto_draft_date);
	$aDBc_unused["trash-posts"]['count'] += $wpdb->get_var("SELECT COUNT(ID) FROM $wpdb->posts WHERE post_status = 'trash'" . $trash_post_date);

	$aDBc_unused["moderated-comments"]['count'] += $wpdb->get_var("SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_approved = '0'" . $moderated_comment_date);
	$aDBc_unused["spam-comments"]['count'] += $wpdb->get_var("SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_approved = 'spam'" . $spam_comment_date);
	$aDBc_unused["trash-comments"]['count'] += $wpdb->get_var("SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_approved = 'trash'" . $trash_comment_date);
	$aDBc_unused["pingbacks"]['count'] += $wpdb->get_var("SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_type = 'pingback'" . $pingback_date);
	$aDBc_unused["trackbacks"]['count'] += $wpdb->get_var("SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_type = 'trackback'" . $trackback_date);

	$aDBc_unused["orphan-postmeta"]['count'] += $wpdb->get_var("SELECT COUNT(meta_id) FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL");

	$aDBc_unused["orphan-commentmeta"]['count'] += $wpdb->get_var("SELECT COUNT(meta_id) FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_id FROM $wpdb->comments)");

	// for the table usermeta, only one table exists for MU, do not switch over blogs for it. Get count only in main site
	if(is_main_site()){
		$aDBc_unused["orphan-usermeta"]['count'] += $wpdb->get_var("SELECT COUNT(umeta_id) FROM $wpdb->usermeta WHERE user_id NOT IN (SELECT ID FROM $wpdb->users)");
	}
	
	$aDBc_unused["orphan-termmeta"]['count'] += $wpdb->get_var("SELECT COUNT(meta_id) FROM $wpdb->termmeta WHERE term_id NOT IN (SELECT term_id FROM $wpdb->terms)");

	$aDBc_unused["orphan-relationships"]['count'] += $wpdb->get_var("SELECT COUNT(object_id) FROM $wpdb->term_relationships WHERE term_taxonomy_id=1 AND object_id NOT IN (SELECT ID FROM $wpdb->posts)");

	// Section for transients
	//$all_transients_names = $wpdb->get_col("SELECT option_name FROM $wpdb->options where (option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%') AND option_name NOT LIKE '_transient_timeout_%' AND option_name NOT LIKE '_site_transient_timeout_%'");

	$expired_transient_names = $wpdb->get_col("SELECT REPLACE(option_name, '_timeout', '') FROM $wpdb->options where (option_name LIKE '_transient_timeout_%' OR option_name LIKE '_site_transient_timeout_%') AND option_value < UNIX_TIMESTAMP()");

	//$transient_with_expiration_names = $wpdb->get_col("SELECT REPLACE(option_name, '_timeout', '') FROM $wpdb->options where option_value > UNIX_TIMESTAMP() AND (option_name LIKE '_transient_timeout_%' OR option_name LIKE '_site_transient_timeout_%')");

	// Get transients with no expiration
	//$transients_with_no_expiration = array_diff($all_transients_names, $expired_transient_names, $transient_with_expiration_names);

	$aDBc_unused["expired-transients"]['count'] += count($expired_transient_names);
	//$aDBc_unused["transients-with-expiration"]['count'] += count($transient_with_expiration_names);
	//$aDBc_unused["transients-with-no-expiration"]['count'] += count($transients_with_no_expiration);
	// End of transients section

}

/** Prepare keep_last element if any **/
function aDBc_get_keep_last_sql_arg($element_type, $column_name){

	// If we are in MU, we shoul call settings from the main site since here in are inside switch_blog and therefore calling get_option will lead to calling the current blog options
	if(function_exists('is_multisite') && is_multisite()){
		$settings = get_blog_option(ADBC_MAIN_SITE_ID, 'aDBc_settings');
	}else{
		$settings = get_option('aDBc_settings');
	}
	if(!empty($settings['keep_last'])){
		$keep_setting = $settings['keep_last'];
		if(!empty($keep_setting[$element_type]))
			return  " and $column_name < NOW() - INTERVAL " . $keep_setting[$element_type] . " DAY";
	}
	return "";
}

/**************************************************************************************************
* This function filters the array containing results according to users args for the free versions.
* Mainly for tables to optimize and repair
**************************************************************************************************/
function aDBc_filter_results_in_all_items_array_free(&$aDBc_all_items, $aDBc_tables_name_to_optimize, $aDBc_tables_name_to_repair){

	if(function_exists('is_multisite') && is_multisite()){

		// Filter according to tables types (to optimize, to repair...)
		if(!empty($_GET['t_type']) && $_GET['t_type'] != "all"){
			$type = esc_sql($_GET['t_type']);
			if($type == 'optimize'){
				$array_names = $aDBc_tables_name_to_optimize;
			}else{
				$array_names = $aDBc_tables_name_to_repair;
			}
			foreach($aDBc_all_items as $item_name => $item_info){
				foreach($item_info['sites'] as $site_id => $site_item_info){
					if(!in_array($site_item_info['prefix'] . $item_name, $array_names)){
						unset($aDBc_all_items[$item_name]['sites'][$site_id]);
					}
				}
			}
		}	

	}else{

		// Prepare an array containing names of items to delete
		$names_to_delete = array();

		// Filter according to tables types (to optimize, to repair...)
		$filter_on_t_type = !empty($_GET['t_type']) && $_GET['t_type'] != "all";
		if($filter_on_t_type){
			$type = esc_sql($_GET['t_type']);
			if($type == "optimize"){
				$array_names = $aDBc_tables_name_to_optimize;
			}else{
				$array_names = $aDBc_tables_name_to_repair;
			}			
		}

		foreach($aDBc_all_items as $item_name => $item_info){
			if($filter_on_t_type){
				if(!in_array($item_info['sites'][1]['prefix'] . $item_name, $array_names)){
					array_push($names_to_delete, $item_name);
				}
			}
		}

		// Loop over the names to delete and delete them for the array
		foreach($names_to_delete as $name){
			unset($aDBc_all_items[$name]);
		}

	}

}

/***********************************************************************************
*
* Common function to: options, tables and scheduled tasks processes
*
***********************************************************************************/

/** Prepares items (options, tables or tasks) to display + message*/
function aDBc_prepare_items_to_display(
	&$items_to_display,
	&$aDBc_items_categories_info,
	&$aDBc_which_button_to_show,
	$aDBc_tables_name_to_optimize,
	$aDBc_tables_name_to_repair,
	&$array_belongs_to_counts,
	&$aDBc_message,
	&$aDBc_class_message,
	$items_type){

	// Prepare categories info
	switch($items_type){
		case 'tasks' :
			$aDBc_all_items = aDBc_get_all_scheduled_tasks();
			$aDBc_items_categories_info = array(
					'all' 	=> array('name' => __('All', 'advanced-database-cleaner'),				'color' => '#4E515B',  	'count' => 0),
					'u'		=> array('name' => __('Uncategorized', 'advanced-database-cleaner'),	'color' => 'grey', 		'count' => 0),
					'o'		=> array('name' => __('Orphans','advanced-database-cleaner'),			'color' => '#E97F31', 	'count' => 0),
					'p'		=> array('name' => __('Plugins tasks', 'advanced-database-cleaner'),	'color' => '#00BAFF', 	'count' => 0),
					't'		=> array('name' => __('Themes tasks', 'advanced-database-cleaner'),		'color' => '#45C966', 	'count' => 0),
					'w'		=> array('name' => __('WP tasks', 'advanced-database-cleaner'),			'color' => '#D091BE', 	'count' => 0)
					);
			break;
		case 'options' :
			$aDBc_all_items = aDBc_get_all_options();
			$aDBc_items_categories_info = array(
					'all' 	=> array('name' => __('All', 'advanced-database-cleaner'),				'color' => '#4E515B',  	'count' => 0),
					'u'		=> array('name' => __('Uncategorized', 'advanced-database-cleaner'),	'color' => 'grey', 		'count' => 0),
					'o'		=> array('name' => __('Orphans','advanced-database-cleaner'),			'color' => '#E97F31', 	'count' => 0),
					'p'		=> array('name' => __('Plugins options', 'advanced-database-cleaner'),	'color' => '#00BAFF', 	'count' => 0),
					't'		=> array('name' => __('Themes options', 'advanced-database-cleaner'),	'color' => '#45C966', 	'count' => 0),
					'w'		=> array('name' => __('WP options', 'advanced-database-cleaner'),		'color' => '#D091BE', 	'count' => 0)
					);
			break;
		case 'tables' :
			$aDBc_all_items = aDBc_get_all_tables();
			$aDBc_items_categories_info = array(
					'all' 	=> array('name' => __('All', 'advanced-database-cleaner'),				'color' => '#4E515B',  	'count' => 0),
					'u'		=> array('name' => __('Uncategorized', 'advanced-database-cleaner'),	'color' => 'grey', 		'count' => 0),
					'o'		=> array('name' => __('Orphans','advanced-database-cleaner'),			'color' => '#E97F31', 	'count' => 0),
					'p'		=> array('name' => __('Plugins tables', 'advanced-database-cleaner'),	'color' => '#00BAFF', 	'count' => 0),
					't'		=> array('name' => __('Themes tables', 'advanced-database-cleaner'),	'color' => '#45C966', 	'count' => 0),
					'w'		=> array('name' => __('WP tables', 'advanced-database-cleaner'),		'color' => '#D091BE', 	'count' => 0)
					);
			break;
	}

	$aDBc_saved_items_file = "";
	// xxx change this later, no need for this test in the free verion. Moreover, ADBC_UPLOAD_DIR_PATH_TO_ADBC is not defined here
	/*if(file_exists(ADBC_UPLOAD_DIR_PATH_TO_ADBC . "/" . $items_type . ".txt")){
		$aDBc_saved_items_file 				= fopen(ADBC_UPLOAD_DIR_PATH_TO_ADBC . "/" . $items_type . ".txt", "r");
	}*/

	// Affect type and belongs_to to items. 
	if ($aDBc_saved_items_file) {
		while(($item = fgets($aDBc_saved_items_file)) !== false) {
			$columns = explode(":", trim($item));
			// We replace +=+ by : because names that contain : have been transformed to +=+ to prevent problems with split based on :
			$item_name = str_replace("+=+", ":", $columns[0]);
			// Prevent adding an item that was cleaned (maybe by other plugins) but not updated in file
			if(array_key_exists($item_name, $aDBc_all_items) && empty($aDBc_all_items[$item_name]['belongs_to'])) {

				$aDBc_all_items[$item_name]['belongs_to'] = $columns[1];
				$aDBc_all_items[$item_name]['type'] = $columns[2];

				// Add this belongs_to to array for display in dropdown filter
				$belongs_to_value = explode("(", $columns[1], 2);
				$belongs_to_value = trim($belongs_to_value[0]);
				$belongs_to_value = str_replace(" ", "-", $belongs_to_value);
				if($items_type == "tasks"){
					if(!array_key_exists($belongs_to_value, $array_belongs_to_counts)){
						$array_belongs_to_counts[$belongs_to_value]['type'] = $columns[2];
						foreach($aDBc_all_items[$item_name]['sites'] as $site => $info){
							$array_belongs_to_counts[$belongs_to_value]['count'] = count($aDBc_all_items[$item_name]['sites'][$site]['args']);
						}
					}else{
						foreach($aDBc_all_items[$item_name]['sites'] as $site => $info){
						$array_belongs_to_counts[$belongs_to_value]['count'] += count($aDBc_all_items[$item_name]['sites'][$site]['args']);
						}
					}
				}else{
					if(!array_key_exists($belongs_to_value, $array_belongs_to_counts)){
						$array_belongs_to_counts[$belongs_to_value]['type'] = $columns[2];
						$array_belongs_to_counts[$belongs_to_value]['count'] = count($aDBc_all_items[$item_name]['sites']);
					}else{
						$array_belongs_to_counts[$belongs_to_value]['count'] += count($aDBc_all_items[$item_name]['sites']);
					}
				}
			}
		}
		fclose($aDBc_saved_items_file);
	}

	// Filter results according to users choices and args
	if(ADBC_PLUGIN_F_TYPE == "pro"){
		aDBc_filter_results_in_all_items_array($aDBc_all_items, $aDBc_tables_name_to_optimize, $aDBc_tables_name_to_repair);
	}else{
		aDBc_filter_results_in_all_items_array_free($aDBc_all_items, $aDBc_tables_name_to_optimize, $aDBc_tables_name_to_repair);
	}

	// Put 'u' type to all uncategorized items and count all items
	foreach($aDBc_all_items as $item_name => $item_info){
		// Counting items differ from tasks to options and tables
		// For tasks, we will counts numbers of args in array, while for options/tables, we will count number of sites
		if($items_type == "tasks"){
			foreach($item_info['sites'] as $site => $info){
				$aDBc_items_categories_info['all']['count'] += count($item_info['sites'][$site]['args']);
				if(empty($item_info['type'])){
					$aDBc_all_items[$item_name]['type'] = 'u';
					$aDBc_items_categories_info['u']['count'] += count($item_info['sites'][$site]['args']);
				}else{
					$aDBc_items_categories_info[$item_info['type']]['count'] += count($item_info['sites'][$site]['args']);
				}
			}
		}else{
			$aDBc_items_categories_info['all']['count'] += count($item_info['sites']);
			if(empty($item_info['type'])){
				$aDBc_all_items[$item_name]['type'] = 'u';
				$aDBc_items_categories_info['u']['count'] += count($item_info['sites']);
			}else{
				$aDBc_items_categories_info[$item_info['type']]['count'] += count($item_info['sites']);
			}
		}
	}

	// Prepare items to display
	$aDBc_not_categorized_toolip = "<span class='aDBc-tooltips-headers'>
						<img class='aDBc-info-image' src='".  ADBC_PLUGIN_DIR_PATH . '/images/information2.svg' . "'/>
						<span>" . __('This item is not categorized yet! Please click on scan button above to categorize it.','advanced-database-cleaner') ." </span>
						 </span>";		
	foreach($aDBc_all_items as $item_name => $item_info){

		if($_GET['aDBc_cat'] != "all" && $item_info['type'] != $_GET['aDBc_cat']){
			continue;
		}

		switch($item_info['type']){
			case 'u' :
				if(ADBC_PLUGIN_F_TYPE == "free"){
					$belongs_to_without_html = __('Available in Pro version!', 'advanced-database-cleaner');
					$belongs_to = '<span style="color:#999">' . $belongs_to_without_html . '</span>';
				}else{
					$belongs_to_without_html = __('Uncategorised!', 'advanced-database-cleaner');
					$belongs_to = '<span style="color:#999">' . $belongs_to_without_html . '</span>' . $aDBc_not_categorized_toolip;
				}
				break;
			case 'o' :
				$belongs_to_without_html = __('Orphan!', 'advanced-database-cleaner');
				$belongs_to = '<span style="color:#E97F31">' . $belongs_to_without_html . '</span>';
				break;
			case 'w' :
				$belongs_to_without_html = __('Wordpress core', 'advanced-database-cleaner');
				$belongs_to = '<span style="color:#D091BE">' . $belongs_to_without_html;
				// Add percent % if any
				$belongs_to .= $item_info['belongs_to'] == "w" ? "" : " ".$item_info['belongs_to'];
				$belongs_to .= '</span>';
				break;
			case 'p' :
				$belongs_to_without_html = $item_info['belongs_to'];
				$belongs_to = '<span style="color:#00BAFF">' . $belongs_to_without_html . '</span>';
				break;
			case 't' :
				$belongs_to_without_html = $item_info['belongs_to'];
				$belongs_to = '<span style="color:#45C966">' . $belongs_to_without_html . '</span>';
				break;
		}
		foreach($item_info['sites'] as $site_id => $site_item_info){
			switch($items_type){
				case 'tasks' :
					foreach($site_item_info['args'] as $args_info){
						array_push($items_to_display, array(
								'hook_name' 				=> $item_name,
								'arguments' 				=> $args_info['arguments'],
								'site_id' 					=> $site_id,
								'next_run' 					=> $args_info['next_run'] . ' - ' . $args_info['frequency'],
								'timestamp'					=> $args_info['timestamp'],
								'hook_belongs_to'			=> $belongs_to
						));
					}
					break;
				case 'options' :
					array_push($items_to_display, array(
							'option_name' 				=> $item_name,
							'option_value' 				=> $site_item_info['value'],
							'option_autoload' 			=> $site_item_info['autoload'],
							'option_size'				=> $site_item_info['size'],
							'site_id' 					=> $site_id,
							'option_belongs_to' 		=> $belongs_to
					));
					break;
				case 'tables' :
					array_push($items_to_display, array(
							'table_name' 				=> $item_name,
							'table_prefix' 				=> $site_item_info['prefix'],
							'table_full_name' 			=> $site_item_info['prefix'].$item_name,
							'table_rows' 				=> $site_item_info['rows'],
							'table_size' 				=> $site_item_info['size'],
							'table_lost' 				=> $site_item_info['lost'],
							'site_id' 					=> $site_id,
							'table_belongs_to' 			=> $belongs_to
					));
					break;
			}
		}
	}

	// Sort items if necessary
	if(!empty($_GET['orderby'])){
		$order_by 	= esc_sql($_GET['orderby']);
		$order 		= empty($_GET['order']) ? "asc" : esc_sql($_GET['order']);

		if($order_by == "table_name"){
			$order_by = "table_full_name";
		}

		$elements = array();
		foreach($items_to_display as $items){
			$elements[] = $items[$order_by];
		}

		if($order_by == "table_size" || $order_by == "option_size" || $order_by == "site_id"){
			if($order == "asc"){
				array_multisort($elements, SORT_ASC, $items_to_display, SORT_NUMERIC);
			}else{
				array_multisort($elements, SORT_DESC, $items_to_display, SORT_NUMERIC);
			}
		}else{
			if($order == "asc"){
				array_multisort($elements, SORT_ASC, $items_to_display, SORT_REGULAR);
			}else{
				array_multisort($elements, SORT_DESC, $items_to_display, SORT_REGULAR);
			}
		}
	}

	// Select which button to show, is it "new search" or "continue search"?
	// If $aDBc_saved_items['last_file_path'] contains a path, then we conclude that the last search has failed => display "continue searching" button
	$new_search = get_option("aDBc_temp_last_iteration_".$items_type);
	if(empty($new_search)){
		$aDBc_which_button_to_show = "new_search";
	}else{
		$aDBc_which_button_to_show = "continue_search";
		$aDBc_message .= '<font color="black">';
		$aDBc_message .= __('This page will reload several times during this scan!', 'advanced-database-cleaner');
		$aDBc_message .= '</font>';
		$aDBc_class_message = "notice-info";
	}
}

/***********************************************************************************
*
* Function proper to options processes
*
***********************************************************************************/

/** Prepares all options for all sites (if any) in a multidimensional array */
function aDBc_get_all_options() {
	$aDBc_all_options = array();
	global $wpdb;
	if(function_exists('is_multisite') && is_multisite()){
		$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach($blogs_ids as $blog_id){
			switch_to_blog($blog_id);
				aDBc_add_options($aDBc_all_options, $blog_id);
			restore_current_blog();
		}
	}else{
		aDBc_add_options($aDBc_all_options, "1");
	}
	return $aDBc_all_options;
}

/** Prepares options for one single site (Used by aDBc_get_all_options() function) */
function aDBc_add_options(&$aDBc_all_options, $blog_id){
	global $wpdb;
	// Get the list of all options from the current WP database
	$aDBc_options_in_db = $wpdb->get_results("SELECT option_name, option_value, autoload FROM $wpdb->options WHERE option_name NOT LIKE '%transient%' and option_name NOT LIKE '%session%expire%'");
	foreach($aDBc_options_in_db as $option){
		// If the option has not been added yet, add it and initiate its info
		if(empty($aDBc_all_options[$option->option_name])){
			$aDBc_all_options[$option->option_name] = array('belongs_to' => '', 'maybe_belongs_to' => '', 'type' => '', 'sites' => array());
		}

		// Add info of the option according to the current site
		$aDBc_all_options[$option->option_name]['sites'][$blog_id] = array(
										'value' => aDBc_create_tooltip_for_option_value($option->option_value, 17),
										'size' => mb_strlen($option->option_value),
										'autoload' => $option->autoload
																	);
	}
}

/***********************************************************************************
*
* Function proper to tables processes
*
***********************************************************************************/

/** Prepares all tables for all sites (if any) in a multidimensional array */
function aDBc_get_all_tables() {

	global $wpdb;

	// First, prepare an array containing rows and sizes of tables
	$aDBc_tables_rows_sizes = array();
	$aDBc_result = $wpdb->get_results('SHOW TABLE STATUS FROM `'.DB_NAME.'`');
	foreach($aDBc_result as $aDBc_row){
		$aDBc_table_size = $aDBc_row->Data_length + $aDBc_row->Index_length;
		$aDBc_table_lost = $aDBc_row->Data_free;
		$aDBc_tables_rows_sizes[$aDBc_row->Name] = array('rows' => $aDBc_row->Rows, 'size' => $aDBc_table_size, 'lost' => $aDBc_table_lost);
	}

	// Prepare ana array to hold all info about tables
	$aDBc_all_tables = array();
	$aDBc_prefix_list = array();
	// If is Multisite then we retrieve the list of all prefixes
	if(function_exists('is_multisite') && is_multisite()){
		$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach($blogs_ids as $blog_id){
			$aDBc_prefix_list[$wpdb->get_blog_prefix($blog_id)] = $blog_id;
		}
	}else{
		$aDBc_prefix_list[$wpdb->prefix] = "1";
	}
	// Get the names of all tables in the database
	$aDBc_all_tables_names = $wpdb->get_results("SELECT table_name FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");

	foreach($aDBc_all_tables_names as $aDBc_table){

		// Get table name
		$table_name = "";
		// This test to prevent issues in MySQL 8 where tables are not shown
		// MySQL 5 uses $aDBc_table->table_name while MySQL 8 uses $aDBc_table->TABLE_NAME
		if(property_exists($aDBc_table, "table_name")){
			$table_name = $aDBc_table->table_name;
		}else if(property_exists($aDBc_table, "TABLE_NAME")){
			$table_name = $aDBc_table->TABLE_NAME;
		}

		// Holds the possible prefixes found for the current table
		$aDBc_found_prefixes = array();
		// Test if the table name starts with a valid prefix
		foreach($aDBc_prefix_list as $prefix => $site_id){
			if(substr($table_name, 0, strlen($prefix)) === $prefix){
				$aDBc_found_prefixes[$prefix] = $site_id;
			}
		}
		// If the table do not start with any valid prefix, we add it as it is
		if(count($aDBc_found_prefixes) == 0){
			$aDBc_table_name_without_prefix = $table_name;
			$aDBc_table_prefix = "";
			$aDBc_table_site = "1";
		}else if(count($aDBc_found_prefixes) == 1){
			// If the number of possible prefixes found is 1, we add the table name with its data
			// Get the first element in $aDBc_found_prefixes
			reset($aDBc_found_prefixes);
			$aDBc_table_prefix = key($aDBc_found_prefixes);
			$aDBc_table_site = current($aDBc_found_prefixes);
			$aDBc_table_name_without_prefix = substr($table_name, strlen($aDBc_table_prefix));
		}else{
			// If the number of possible prefixes found >= 2, we choose the longest prefix as valid one
			$aDBc_table_prefix = "";
			$aDBc_table_site = "";
			$aDBc_table_name_without_prefix = "";
			foreach($aDBc_found_prefixes as $aDBc_prefix => $aDBc_site){
				if(strlen($aDBc_prefix) >= strlen($aDBc_table_prefix)){
					$aDBc_table_prefix = $aDBc_prefix;
					$aDBc_table_site = $aDBc_site;
					$aDBc_table_name_without_prefix = substr($table_name, strlen($aDBc_table_prefix));
				}
			}
		}
		// Add table information to the global array
		// If the table has not been added yet, add it and initiate its info
		if(empty($aDBc_all_tables[$aDBc_table_name_without_prefix])){
			$aDBc_all_tables[$aDBc_table_name_without_prefix] = array('belongs_to' => '', 'maybe_belongs_to' => '', 'type' => '', 'sites' => array());
		}
		// Add info of the task according to the current site
		$aDBc_all_tables[$aDBc_table_name_without_prefix]['sites'][$aDBc_table_site] = array('prefix' 	=> $aDBc_table_prefix,
																							 'rows'		=> $aDBc_tables_rows_sizes[$table_name]['rows'],
																							 'size'		=> $aDBc_tables_rows_sizes[$table_name]['size'],
																							 'lost'		=> $aDBc_tables_rows_sizes[$table_name]['lost'],
																						);		
	}
	return $aDBc_all_tables;
}

/***********************************************************************************
*
* Function proper to scheduled tasks processes
*
***********************************************************************************/

/** Prepares all scheduled tasks for all sites (if any) in a multidimensional array */
function aDBc_get_all_scheduled_tasks() {
	$aDBc_all_tasks = array();
	if(function_exists('is_multisite') && is_multisite()){
		global $wpdb;
		$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach($blogs_ids as $blog_id){
			switch_to_blog($blog_id);
				aDBc_add_scheduled_tasks($aDBc_all_tasks, $blog_id);
			restore_current_blog();
		}
	}else{
		aDBc_add_scheduled_tasks($aDBc_all_tasks, "1");
	}
	return $aDBc_all_tasks;
}

/** Prepares scheduled tasks for one single site (Used by aDBc_get_all_scheduled_tasks() function) */
function aDBc_add_scheduled_tasks(&$aDBc_all_tasks, $blog_id) {
	$cron = _get_cron_array();
	$schedules = wp_get_schedules();
	foreach((array) $cron as $timestamp => $cronhooks){
		foreach( (array) $cronhooks as $hook => $events){
			foreach( (array) $events as $event){
				// If the frequency exist
				if($event['schedule']){
					if(!empty($schedules[$event['schedule']])){
						$aDBc_frequency = $schedules[$event['schedule']]['display'];
					}else{
						$aDBc_frequency = __('Unknown!', 'advanced-database-cleaner');
					}
				}else{
					$aDBc_frequency = __('Single event', 'advanced-database-cleaner');
				}
				// Get arguments
				$aDBc_args_array = array();
				if(!empty($event['args'])){
					$aDBc_args = $event['args'];
					foreach( (array) $aDBc_args as $id => $arg){
						array_push($aDBc_args_array, $arg);
					}
				}
				if(empty($aDBc_args_array)){
					$args_string = "none";
				}else{
					$args_string = serialize($aDBc_args_array);
				}
				// If the task has not been added yet, add it and initiate its info
				if(empty($aDBc_all_tasks[$hook])){

					$aDBc_all_tasks[$hook] = array('belongs_to' => '', 'maybe_belongs_to' => '', 'type' => '', 'sites' => array());

				}

				// Initialize args array
				if(empty($aDBc_all_tasks[$hook]['sites'][$blog_id]['args'])){
					$aDBc_all_tasks[$hook]['sites'][$blog_id]['args'] = array();
				}

				array_push($aDBc_all_tasks[$hook]['sites'][$blog_id]['args'], array('frequency' => $aDBc_frequency,
																	      'next_run' => get_date_from_gmt(@date('Y-m-d H:i:s', $timestamp), 'M j, Y @ H:i:s'),
																		  'timestamp' => $timestamp,
																		  'arguments' => $args_string));
			}
		}
	}
}


/***********************************************************************************
* Transfrom bytes to corresponding best size system: KB, MB or GB
***********************************************************************************/
function aDBc_get_size_from_bytes($bytes) {
	$size = $bytes / 1024;
	if($size >= 1024){
		$size = $size / 1024;
		if($size >= 1024){
			$size = $size / 1024;
			$size = round($size, 1) . " GB";
		}else{
			$size = round($size, 1) . " MB";
		}
	}else{
		$size = round($size, 1) . " KB";
	}
	return $size;
}

/***********************************************************************************
* Create the folder plus an index.php for silence is golden
***********************************************************************************/
function aDBc_create_folder_plus_index_file($folder) {

	wp_mkdir_p($folder);

	// Create index file
	$myfile = fopen($folder . '/index.php', "w");
	if($myfile){
		fwrite($myfile, "<?php\n// Silence is golden.");
		fclose($myfile);
	}

	// Create htaccess file
	// $myfile = fopen($folder . '/.htaccess', "w");
	// if($myfile){
		// fwrite($myfile, "Deny from all");
		// fclose($myfile);
	// }
}

/************************************************************************************************** 
 * Delete folder with its content
 *************************************************************************************************/
function aDBc_delete_folder_with_content($path){

	if(!file_exists($path))
		return;
	$dir = opendir($path);
	while(($file = readdir($dir)) !== false){
		if ($file != '.' && $file != '..'){
			unlink($path . "/" . $file);
		}
	}
	closedir($dir);
	rmdir( $path );

}

/************************************************************************************************** 
 * Uupdate task in db after being deleted
 *************************************************************************************************/
function aDBc_update_task_in_db_after_delete($arg_name, $db_option_name){

	$clean_schedule_setting = get_option($db_option_name);
	// We will proceed only if settings are an array
	if(is_array($clean_schedule_setting)){
		$schedule = $clean_schedule_setting[$arg_name];
		$schedule['active'] = "0";
		$clean_schedule_setting[$arg_name] = $schedule;
		update_option($db_option_name, $clean_schedule_setting, "no");
	}

}

/***********************************************************************************
* Get core tables that are categorized by default
***********************************************************************************/
function aDBc_get_core_tables() {

	/*
	* yyy: WP core tables
	* Found in wp-admin/includes/schema.php and wp-admin/includes/upgrade.php
	* After each release of WP, this list should be updated to add new tables if necessary (to minimize searches in files).
	*/
	$aDBc_wp_core_tables = array(
		'terms',
		'term_taxonomy',
		'term_relationships',
		'commentmeta',
		'comments',
		'links',
		'options',
		'postmeta',
		'posts',
		'users',
		'usermeta',
		// Since 3.0 in wp-admin/includes/upgrade.php
		'sitecategories',
		// Since 4.4
		'termmeta'
	);

	// If MU, add tables of MU
	if(function_exists('is_multisite') && is_multisite()){
		array_push($aDBc_wp_core_tables, 'blogs');
		array_push($aDBc_wp_core_tables, 'blog_versions');
		array_push($aDBc_wp_core_tables, 'blogmeta');
		array_push($aDBc_wp_core_tables, 'registration_log');
		array_push($aDBc_wp_core_tables, 'site');
		array_push($aDBc_wp_core_tables, 'sitemeta');
		array_push($aDBc_wp_core_tables, 'signups');
	}

	return $aDBc_wp_core_tables;
}

/***********************************************************************************
* Get core tasks that are categorized by default
***********************************************************************************/
function aDBc_get_core_tasks() {

	/*
	* yyy: WP core tasks
	* After each release of WP, this list should be updated to add new tasks if necessary (to minimize searches in files).
	*/
	$aDBc_wp_core_tasks = array(
		'wp_version_check',
		'wp_update_plugins',
		'wp_update_themes',
		'wp_maybe_auto_update',
		'wp_scheduled_auto_draft_delete',
		'wp_scheduled_delete',
		'update_network_counts',
		'delete_expired_transients',
		'wp_privacy_delete_old_export_files',
		'recovery_mode_clean_expired_keys'
	);

	return $aDBc_wp_core_tasks;
}

/***********************************************************************************
* Get core options that are categorized by default
***********************************************************************************/
function aDBc_get_core_options() {

	/*
	* yyy: WP core options
	* Found in wp-admin/includes/schema.php
	* After each release of WP, this list should be updated to add new options if necessary (to minimize searches in files).
	*/
	$aDBc_wp_core_options = array(
		'siteurl',
		'home',
		'blogname',
		'blogdescription',
		'users_can_register',
		'admin_email',
		'start_of_week',
		'use_balanceTags',
		'use_smilies',
		'require_name_email',
		'comments_notify',
		'posts_per_rss',
		'rss_use_excerpt',
		'mailserver_url',
		'mailserver_login',
		'mailserver_pass',
		'mailserver_port',
		'default_category',
		'default_comment_status',
		'default_ping_status',
		'default_pingback_flag',
		'posts_per_page',
		'date_format',
		'time_format',
		'links_updated_date_format',
		'comment_moderation',
		'moderation_notify',
		'permalink_structure',
		'gzipcompression',
		'hack_file',
		'blog_charset',
		'moderation_keys',
		'active_plugins',
		'category_base',
		'ping_sites',
		'advanced_edit',
		'comment_max_links',
		'gmt_offset',
		// 1.5
		'default_email_category',
		'recently_edited',
		'template',
		'stylesheet',
		'comment_whitelist',
		'blacklist_keys',
		'comment_registration',
		'html_type',
		// 1.5.1
		'use_trackback',
		// 2.0
		'default_role',
		'db_version',
		// 2.0.1
		'uploads_use_yearmonth_folders',
		'upload_path',
		// 2.1
		'blog_public',
		'default_link_category',
		'show_on_front',
		// 2.2
		'tag_base',
		// 2.5
		'show_avatars',
		'avatar_rating',
		'upload_url_path',
		'thumbnail_size_w',
		'thumbnail_size_h',
		'thumbnail_crop',
		'medium_size_w',
		'medium_size_h',
		// 2.6
		'avatar_default',
		// 2.7
		'large_size_w',
		'large_size_h',
		'image_default_link_type',
		'image_default_size',
		'image_default_align',
		'close_comments_for_old_posts',
		'close_comments_days_old',
		'thread_comments',
		'thread_comments_depth',
		'page_comments',
		'comments_per_page',
		'default_comments_page',
		'comment_order',
		'sticky_posts',
		'widget_categories',
		'widget_text',
		'widget_rss',
		'uninstall_plugins',
		// 2.8
		'timezone_string',
		// 3.0
		'page_for_posts',
		'page_on_front',
		// 3.1
		'default_post_format',
		// 3.5
		'link_manager_enabled',
		// 4.3.0
		'finished_splitting_shared_terms',
		'site_icon',
		// 4.4.0
		'medium_large_size_w',
		'medium_large_size_h',
		// 4.9.6
		'wp_page_for_privacy_policy',
		// 4.9.8
		'show_comments_cookies_opt_in',
		// Deleted from new versions
		'blodotgsping_url', 'bodyterminator', 'emailtestonly', 'phoneemail_separator', 'smilies_directory',
		'subjectprefix', 'use_bbcode', 'use_blodotgsping', 'use_phoneemail', 'use_quicktags', 'use_weblogsping',
		'weblogs_cache_file', 'use_preview', 'use_htmltrans', 'smilies_directory', 'fileupload_allowedusers',
		'use_phoneemail', 'default_post_status', 'default_post_category', 'archive_mode', 'time_difference',
		'links_minadminlevel', 'links_use_adminlevels', 'links_rating_type', 'links_rating_char',
		'links_rating_ignore_zero', 'links_rating_single_image', 'links_rating_image0', 'links_rating_image1',
		'links_rating_image2', 'links_rating_image3', 'links_rating_image4', 'links_rating_image5',
		'links_rating_image6', 'links_rating_image7', 'links_rating_image8', 'links_rating_image9',
		'links_recently_updated_time', 'links_recently_updated_prepend', 'links_recently_updated_append',
		'weblogs_cacheminutes', 'comment_allowed_tags', 'search_engine_friendly_urls', 'default_geourl_lat',
		'default_geourl_lon', 'use_default_geourl', 'weblogs_xml_url', 'new_users_can_blog', '_wpnonce',
		'_wp_http_referer', 'Update', 'action', 'rich_editing', 'autosave_interval', 'deactivated_plugins',
		'can_compress_scripts', 'page_uris', 'update_core', 'update_plugins', 'update_themes', 'doing_cron',
		'random_seed', 'rss_excerpt_length', 'secret', 'use_linksupdate', 'default_comment_status_page',
		'wporg_popular_tags', 'what_to_show', 'rss_language', 'language', 'enable_xmlrpc', 'enable_app',
		'embed_autourls', 'default_post_edit_rows',
		//Found in wp-admin/includes/upgrade.php
		'widget_search',
		'widget_recent-posts',
		'widget_recent-comments',
		'widget_archives',
		'widget_meta',
		'sidebars_widgets',
		// Found in wp-admin/includes/schema.php but not with the above list
		'initial_db_version',
		'WPLANG',
		// Found in wp-admin/includes/class-wp-plugins-list-table.php
		'recently_activated',
		// Found in wp-admin/network/site-info.php
		'rewrite_rules',
		// Found in wp-admin/network.php
		'auth_key',
		'auth_salt',
		'logged_in_key',
		'logged_in_salt',
		'nonce_key',
		'nonce_salt',
		// Found in wp-includes/theme.php
		'theme_switched',
		// Found in wp-includes/class-wp-customize-manager.php
		'current_theme',
		// Found in wp-includes/cron.php
		'cron',
		// Unknown : To verify
		'user_roles',
		'widget_nav_menu',
	);
	
	// Before doing anything, we add some special options to the WP core options array
	// The 'user_roles' option is added in Multi-site as $prefix.'user_roles'. So for each site we should add this options in that format
	if(function_exists('is_multisite') && is_multisite()){
		global $wpdb;
		$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach($blogs_ids as $blog_id){
			array_push($aDBc_wp_core_options, $wpdb->get_blog_prefix($blog_id).'user_roles');
		}
	}

	return $aDBc_wp_core_options;
}

?>