<?php
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
			$wpdb->query("DELETE FROM $wpdb->posts WHERE post_type = 'revision'");
			break;
		case "draft":
			$wpdb->query("DELETE FROM $wpdb->posts WHERE post_status = 'draft'");
			break;
		case "auto-draft":
			$wpdb->query("DELETE FROM $wpdb->posts WHERE post_status = 'auto-draft'");
			break;
		case "trash-posts":
			$wpdb->query("DELETE FROM $wpdb->posts WHERE post_status = 'trash'");
			break;					
		case "moderated-comments":
			$wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = '0'");
			break;
		case "spam-comments":
			$wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = 'spam'");
			break;
		case "trash-comments":
			$wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = 'trash'");
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
		case "dashboard-transient-feed":
			$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_site_transient_browser_%' OR option_name LIKE '_site_transient_timeout_browser_%' OR option_name LIKE '_transient_feed_%' OR option_name LIKE '_transient_timeout_feed_%'");
			break;
	}
}

/** Cleans all elements in the current site and in MU (used by the scheduler) */
function aDBc_clean_all_elements(){
	global $wpdb;
	if(function_exists('is_multisite') && is_multisite()){
		$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach($blogs_ids as $blog_id){
			switch_to_blog($blog_id);
			aDBc_clean_elements();
			restore_current_blog();
		}
	}else{
		aDBc_clean_elements();
	}
}

/** Cleans all elements in the current site */
function aDBc_clean_elements(){
	global $wpdb;
	$wpdb->query("DELETE 	FROM $wpdb->posts WHERE post_type = 'revision'");
	$wpdb->query("DELETE 	FROM $wpdb->posts WHERE post_status = 'draft'");
	$wpdb->query("DELETE 	FROM $wpdb->posts WHERE post_status = 'auto-draft'");
	$wpdb->query("DELETE 	FROM $wpdb->posts WHERE post_status = 'trash'");
	$wpdb->query("DELETE 	FROM $wpdb->comments WHERE comment_approved = '0'");
	$wpdb->query("DELETE 	FROM $wpdb->comments WHERE comment_approved = 'spam'");
	$wpdb->query("DELETE 	FROM $wpdb->comments WHERE comment_approved = 'trash'");
	$wpdb->query("DELETE pm FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL");
	$wpdb->query("DELETE 	FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_id FROM $wpdb->comments)");
	$wpdb->query("DELETE 	FROM $wpdb->term_relationships WHERE term_taxonomy_id=1 AND object_id NOT IN (SELECT id FROM $wpdb->posts)");
	$wpdb->query("DELETE 	FROM $wpdb->options WHERE option_name LIKE '_site_transient_browser_%' OR option_name LIKE '_site_transient_timeout_browser_%' OR option_name LIKE '_transient_feed_%' OR option_name LIKE '_transient_timeout_feed_%'");
}


/** Counts all elements to clean (in the current site or MU) */
function aDBc_count_all_elements_to_clean(){
	global $wpdb;
	$aDBc_unused["revision"]['name'] 					= __('Revisions','advanced-database-cleaner');
	$aDBc_unused["draft"]['name'] 						= __('Drafts','advanced-database-cleaner');
	$aDBc_unused["auto-draft"]['name'] 					= __('Auto Drafts','advanced-database-cleaner');
	$aDBc_unused["trash-posts"]['name'] 				= __('Trash posts','advanced-database-cleaner');
	$aDBc_unused["moderated-comments"]['name'] 			= __('Pending comments','advanced-database-cleaner');
	$aDBc_unused["spam-comments"]['name'] 				= __('Spam Comments','advanced-database-cleaner');
	$aDBc_unused["trash-comments"]['name'] 				= __('Trash comments','advanced-database-cleaner');
	$aDBc_unused["orphan-postmeta"]['name'] 			= __('Orphan Postmeta','advanced-database-cleaner');
	$aDBc_unused["orphan-commentmeta"]['name'] 			= __('Orphan Commentmeta','advanced-database-cleaner');
	$aDBc_unused["orphan-relationships"]['name'] 		= __('Orphan Relationships','advanced-database-cleaner');
	$aDBc_unused["dashboard-transient-feed"]['name'] 	= __('Dashboard Transient Feed','advanced-database-cleaner');
	// Initialize counts to 0
	foreach($aDBc_unused as $aDBc_type => $element_info){
		$aDBc_unused[$aDBc_type]['count'] = 0;
	}

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
	$aDBc_unused["revision"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'revision'");
	$aDBc_unused["draft"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'draft'");
	$aDBc_unused["auto-draft"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'auto-draft'");
	$aDBc_unused["trash-posts"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'trash'");
	$aDBc_unused["moderated-comments"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = '0'");
	$aDBc_unused["spam-comments"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = 'spam'");
	$aDBc_unused["trash-comments"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = 'trash'");
	$aDBc_unused["orphan-postmeta"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL");
	$aDBc_unused["orphan-commentmeta"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_id FROM $wpdb->comments)");
	$aDBc_unused["orphan-relationships"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id=1 AND object_id NOT IN (SELECT id FROM $wpdb->posts)");
	$aDBc_unused["dashboard-transient-feed"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '_site_transient_browser_%' OR option_name LIKE '_site_transient_timeout_browser_%' OR option_name LIKE '_transient_feed_%' OR option_name LIKE '_transient_timeout_feed_%'");
}

/** Optimizes all tables having lost space (data_free > 0). Used by the scheduled task */
function aDBc_optimize_tables(){
	global $wpdb;
	$adbc_sql = "SELECT table_name, data_free FROM information_schema.tables WHERE table_schema = '" . DB_NAME ."' and Engine <> 'InnoDB' and data_free > 0";
	$result = $wpdb->get_results($adbc_sql);
	foreach($result as $row){
		$wpdb->query('OPTIMIZE TABLE ' . $row->table_name);
	}
}

/***********************************************************************************
*
* Common function to: options, tables and scheduled tasks processes
*
***********************************************************************************/

/** Prepares items (options, tables or tasks) to display + message*/
function aDBc_prepare_items_to_display(&$items_to_display, &$aDBc_items_categories_info, $items_type){

	// Prepare categories info
	switch($items_type){
		case 'tasks' :
			$aDBc_all_items = aDBc_get_all_scheduled_tasks();
			$aDBc_items_categories_info = array(
					'all' 	=> array('name' => __('All tasks', 'advanced-database-cleaner'),		'color' => '#4E515B',  	'count' => 0),
					'o'		=> array('name' => __('Orphan tasks','advanced-database-cleaner'),	'color' => '#E97F31', 	'count' => "--"),
					'p'		=> array('name' => __('Plugins tasks', 'advanced-database-cleaner'),	'color' => '#00BAFF', 	'count' => "--"),
					't'		=> array('name' => __('Themes tasks', 'advanced-database-cleaner'),	'color' => '#45C966', 	'count' => "--"),
					'w'		=> array('name' => __('WP tasks', 'advanced-database-cleaner'),		'color' => '#D091BE', 	'count' => "--")
					);
			break;
		case 'options' :
			$aDBc_all_items = aDBc_get_all_options();
			$aDBc_items_categories_info = array(
					'all' 	=> array('name' => __('All options', 'advanced-database-cleaner'),	'color' => '#4E515B',  	'count' => 0),
					'o'		=> array('name' => __('Orphan options','advanced-database-cleaner'),	'color' => '#E97F31', 	'count' => "--"),
					'p'		=> array('name' => __('Plugins options', 'advanced-database-cleaner'),'color' => '#00BAFF', 	'count' => "--"),
					't'		=> array('name' => __('Themes options', 'advanced-database-cleaner'),	'color' => '#45C966', 	'count' => "--"),
					'w'		=> array('name' => __('WP options', 'advanced-database-cleaner'),		'color' => '#D091BE', 	'count' => "--")
					);
			break;
		case 'tables' :
			$aDBc_all_items = aDBc_get_all_tables();
			$aDBc_items_categories_info = array(
					'all' 	=> array('name' => __('All tables', 'advanced-database-cleaner'),		'color' => '#4E515B',  	'count' => 0),
					'o'		=> array('name' => __('Orphan tables','advanced-database-cleaner'),	'color' => '#E97F31', 	'count' => "--"),
					'p'		=> array('name' => __('Plugins tables', 'advanced-database-cleaner'),	'color' => '#00BAFF', 	'count' => "--"),
					't'		=> array('name' => __('Themes tables', 'advanced-database-cleaner'),	'color' => '#45C966', 	'count' => "--"),
					'w'		=> array('name' => __('WP tables', 'advanced-database-cleaner'),		'color' => '#D091BE', 	'count' => "--")
					);
			break;
	}

	// Prepare items to display
	$belongs_to = '<span style="color:#cecece">' . __('Available in Pro version!', 'advanced-database-cleaner') . '</span>';
	foreach($aDBc_all_items as $item_name => $item_info){

		$aDBc_items_categories_info['all']['count'] += count($item_info['sites']);
		if($_GET['aDBc_cat'] != "all"){
			continue;
		}

		foreach($item_info['sites'] as $site_id => $site_item_info){
			switch($items_type){
				case 'tasks' :
					array_push($items_to_display, array(
							'hook_name' 		=> $item_name,
							'site_id' 			=> $site_id,
							'next_run' 			=> $site_item_info['next_run'] . ' - ' . $site_item_info['frequency'],
							'hook_belongs_to'	=> $belongs_to
					));
					break;
				case 'options' :
					array_push($items_to_display, array(
							'option_name' 		=> $item_name,
							'option_value' 		=> htmlspecialchars($site_item_info['value'], ENT_QUOTES),
							'option_autoload' 	=> $site_item_info['autoload'],
							'site_id' 			=> $site_id,
							'option_belongs_to' => $belongs_to
					));
					break;
				case 'tables' :
					array_push($items_to_display, array(
							'table_name' 		=> $item_name,
							'table_prefix' 		=> $site_item_info['prefix'],
							'table_rows' 		=> $site_item_info['rows'],
							'table_size' 		=> $site_item_info['size'],
							'site_id' 			=> $site_id,
							'table_belongs_to' 	=> $belongs_to
					));
					break;
			}
		}
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
function aDBc_add_options(&$aDBc_all_options, $blog_id) {
	global $wpdb;
	// Get the list of all options from the current WP database
	$aDBc_options_in_db = $wpdb->get_results("SELECT option_name, option_value, autoload FROM $wpdb->options WHERE option_name NOT LIKE '%transient%' and option_name NOT LIKE '%session%expire%'");
	foreach($aDBc_options_in_db as $option){
		// If the option has not been added yet, add it and initiate its info
		if(empty($aDBc_all_options[$option->option_name])){
			$aDBc_all_options[$option->option_name] = array('belongs_to' => '', 'sites' => array());
		}
		// Add info of the option according to the current site
		$aDBc_all_options[$option->option_name]['sites'][$blog_id] = array(
										'value' => strlen($option->option_value) > 30 ? substr($option->option_value, 0, 30) . " ..." : $option->option_value,
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
		$aDBc_table_size = ($aDBc_row->Data_length + $aDBc_row->Index_length) / 1024;
		$aDBc_table_size = round($aDBc_table_size, 1) . " KB";
		$aDBc_tables_rows_sizes[$aDBc_row->Name] = array('rows' => $aDBc_row->Rows, 'size' => $aDBc_table_size);
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
		// Holds the possible prefixes found for the current table
		$aDBc_found_prefixes = array();
		// Test if the table name starts with a valid prefix
		foreach($aDBc_prefix_list as $prefix => $site_id){
			if(substr($aDBc_table->table_name, 0, strlen($prefix)) === $prefix){
				$aDBc_found_prefixes[$prefix] = $site_id;
			}
		}
		// If the table do not start with any valid prefix, we add it as it is
		if(count($aDBc_found_prefixes) == 0){
			$aDBc_table_name_without_prefix = $aDBc_table->table_name;
			$aDBc_table_prefix = "";
			$aDBc_table_site = "1";
		}else if(count($aDBc_found_prefixes) == 1){
			// If the number of possible prefixes found is 1, we add the table name with its data
			// Get the first element in $aDBc_found_prefixes
			reset($aDBc_found_prefixes);
			$aDBc_table_prefix = key($aDBc_found_prefixes);
			$aDBc_table_site = current($aDBc_found_prefixes);
			$aDBc_table_name_without_prefix = substr($aDBc_table->table_name, strlen($aDBc_table_prefix));
		}else{
			// If the number of possible prefixes found >= 2, we choose the longest prefix as valid one
			$aDBc_table_prefix = "";
			$aDBc_table_site = "";
			$aDBc_table_name_without_prefix = "";
			foreach($aDBc_found_prefixes as $aDBc_prefix => $aDBc_site){
				if(strlen($aDBc_prefix) >= strlen($aDBc_table_prefix)){
					$aDBc_table_prefix = $aDBc_prefix;
					$aDBc_table_site = $aDBc_site;
					$aDBc_table_name_without_prefix = substr($aDBc_table->table_name, strlen($aDBc_table_prefix));
				}
			}
		}
		// Add table information to the global array
		// If the table has not been added yet, add it and initiate its info
		if(empty($aDBc_all_tables[$aDBc_table_name_without_prefix])){
			$aDBc_all_tables[$aDBc_table_name_without_prefix] = array('belongs_to' => '', 'sites' => array());
		}
		// Add info of the task according to the current site
		$aDBc_all_tables[$aDBc_table_name_without_prefix]['sites'][$aDBc_table_site] = array('prefix' => $aDBc_table_prefix,
																						 'rows'	=> $aDBc_tables_rows_sizes[$aDBc_table->table_name]['rows'],
																						 'size'	=> $aDBc_tables_rows_sizes[$aDBc_table->table_name]['size'],
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
					$aDBc_frequency = "<em>" . __('One-off event', 'advanced-database-cleaner') ."</em>";
				}
				// If the task has not been added yet, add it and initiate its info
				if(empty($aDBc_all_tasks[$hook])){
					$aDBc_all_tasks[$hook] = array('belongs_to' => '', 'sites' => array());
				}
				// Add info of the task according to the current site
				$aDBc_all_tasks[$hook]['sites'][$blog_id] = array('frequency' => $aDBc_frequency,
																  'next_run' => get_date_from_gmt(date('Y-m-d H:i:s', $timestamp), 'M j, Y @ H:i:s'));

			}
		}
	}
}

?>