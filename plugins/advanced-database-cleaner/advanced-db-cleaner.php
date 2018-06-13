<?php
if (!defined('ABSPATH')) return;
if (!is_main_site()) return;
/*
Plugin Name: Advanced Database Cleaner
Plugin URI: https://sigmaplugin.com/downloads/wordpress-advanced-database-cleaner
Description: Clean database by deleting unused data such as 'old revisions', 'old drafts', 'orphan options', etc. Optimize database and more.
Version: 2.0.0
Author: Younes JFR.
Author URI: https://www.sigmaplugin.com
Contributors: symptote
Text Domain: advanced-database-cleaner
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/********************************************************************
* Require WordPress List Table Administration API
* xxx: Test validity of WP_List_Table class after each release of WP.
* Notice from Wordpress.org:
* Since this class is marked as private, developers should use this only at their own risk as this class is
* subject to change in future WordPress releases. Any developers using this class are strongly encouraged to
* test their plugins with all WordPress beta/RC releases to maintain compatibility. 
********************************************************************/
if(!class_exists('WP_List_Table')) {
	if(file_exists(ABSPATH . 'wp-admin/includes/class-wp-list-table.php')){
		require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
	}else{
		return;
	}
}

/********************************************************************
*
* Define common constants
*
********************************************************************/
if (!defined("ADBC_PLUGIN_VERSION")) 				define("ADBC_PLUGIN_VERSION", "2.0.0");
if (!defined("ADBC_PLUGIN_DIR_PATH")) 				define("ADBC_PLUGIN_DIR_PATH", plugins_url('' , __FILE__));

/********************************************************************
*
* load language
*
********************************************************************/
add_action('plugins_loaded', 'aDBc_load_textdomain');
function aDBc_load_textdomain() {
	// load_plugin_textdomain('advanced-database-cleaner', false, plugin_basename(dirname(__FILE__)) . '/languages');
	load_plugin_textdomain( 'advanced-database-cleaner' );
}

/********************************************************************
*
* Get settings
*
********************************************************************/
global $aDBc_settings;
$aDBc_settings = get_option('aDBc_settings');
// Test if settings are updated
if(isset($_POST['save_settings'])){
	$aDBc_settings['left_menu'] = isset($_POST['aDBc_left_menu']) ? "1" : "0";
	$aDBc_settings['top_main_msg'] = isset($_POST['aDBc_top_main_msg']) ? "1" : "0";
	// Update settings in DB
	update_option( 'aDBc_settings', $aDBc_settings );
}

/********************************************************************
*
* Add 'Database Cleaner' to Wordpress menu
*
********************************************************************/
add_action('admin_menu', 'aDBc_add_admin_menu');
function aDBc_add_admin_menu() {
	global $aDBc_settings, $aDBc_left_menu, $aDBc_tool_submenu;
	if($aDBc_settings['left_menu'] == "1"){
		$aDBc_left_menu = add_menu_page('Advanced DB Cleaner', 'WP DB Cleaner', 'manage_options', 'advanced_db_cleaner', 'aDBc_main_page_callback', ADBC_PLUGIN_DIR_PATH.'/images/menu-icon.png', '80.01123');
	}
	$aDBc_tool_submenu = add_submenu_page('tools.php', 'Advanced DB Cleaner', 'WP DB Cleaner', 'manage_options', 'advanced_db_cleaner', 'aDBc_main_page_callback');
}

/********************************************************************
*
* Load CSS and JS
*
********************************************************************/
add_action('admin_enqueue_scripts', 'aDBc_load_styles_and_scripts');
function aDBc_load_styles_and_scripts($hook) {
	// Enqueue our js and css in the plugin pages only
	global $aDBc_left_menu, $aDBc_tool_submenu;
	if($hook != $aDBc_left_menu && $hook != $aDBc_tool_submenu){
		return;
	}
	wp_enqueue_style('aDBc_css', ADBC_PLUGIN_DIR_PATH . '/css/admin.css');
	wp_enqueue_script('aDBc_js', ADBC_PLUGIN_DIR_PATH . '/js/admin.js');
    //wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_style('wp-jquery-ui-dialog');
}

/******************************************************************************************
*
* The scheduler
* Get more info here: http://codex.wordpress.org/Plugin_API/Filter_Reference/cron_schedules
*
******************************************************************************************/
add_filter('cron_schedules', 'aDBc_additional_schedules');
function aDBc_additional_schedules($schedules){
	// Add weekly schedule
	$schedules['weekly'] = array(
		'interval' => 604800,
		'display'  => __('Once weekly', 'advanced-database-cleaner')
	);
	// Add monthly schedule
	$schedules['monthly'] = array(
		'interval' => 2635200,
		'display'  => __('Once monthly', 'advanced-database-cleaner')
	);
	return $schedules;
}

/********************************************************************
*
* (RE)-schedule tasks after (RE)-activation or update of the plugin
*
********************************************************************/
register_activation_hook(__FILE__, 'aDBc_activate_plugin');
add_action('aDBc_optimize_scheduler', 'aDBc_optimize_tables');
add_action('aDBc_clean_scheduler', 'aDBc_clean_all_elements');
function aDBc_activate_plugin(){

	// Add scheduled task for optimization if option exists
	$aDBc_optimize_schedule = get_option('aDBc_optimize_schedule');
	if($aDBc_optimize_schedule && $aDBc_optimize_schedule != 'no_schedule'){
		if(!wp_next_scheduled('aDBc_optimize_scheduler'))
			wp_schedule_event(time()+60, $aDBc_optimize_schedule, 'aDBc_optimize_scheduler');
	}

	// Add scheduled task for clean-up if option exists
	$aDBc_clean_schedule = get_option('aDBc_clean_schedule');
	if($aDBc_clean_schedule && $aDBc_clean_schedule != 'no_schedule'){
		if(!wp_next_scheduled('aDBc_clean_scheduler'))
			wp_schedule_event(time()+60, $aDBc_clean_schedule, 'aDBc_clean_scheduler');
	}

	// Add default settings if not exists
	$aDBc_settings = get_option('aDBc_settings');
	if(empty($aDBc_settings)){
		$aDBc_settings['left_menu'] = "1";
		$aDBc_settings['top_main_msg'] = "1";
		$aDBc_settings['tables_cleanup_warning'] = "1";
		update_option('aDBc_settings', $aDBc_settings);
	}

	// When activating version >= 2.0.0, delete all options and tasks created by older versions in MU sites since only the main site can clean the network now
	if(function_exists('is_multisite') && is_multisite()){
		global $wpdb;
		$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach($blogs_ids as $blog_id){
			switch_to_blog($blog_id);
			if(!is_main_site()){
				delete_option('aDBc_optimize_schedule');
				delete_option('aDBc_clean_schedule');
				wp_clear_scheduled_hook('aDBc_optimize_scheduler');
				wp_clear_scheduled_hook('aDBc_clean_scheduler');
			}
			restore_current_blog();
		}
	}	
}

/********************************************************************
*
* Clear current scheduled tasks (if any) when deactivated
*
********************************************************************/
register_deactivation_hook(__FILE__, 'aDBc_deactivate_plugin');
function aDBc_deactivate_plugin($network_wide){
	wp_clear_scheduled_hook('aDBc_optimize_scheduler');
	wp_clear_scheduled_hook('aDBc_clean_scheduler');
}

/********************************************************************
*
* Clear scheduled tasks + options if UNINSTALL
*
********************************************************************/
register_uninstall_hook(__FILE__, 'aDBc_uninstall');
function aDBc_uninstall(){

	// Delete options
	delete_option('aDBc_optimize_schedule');
	delete_option('aDBc_clean_schedule');
	delete_option('aDBc_settings');

	// Clear scheduled tasks
	wp_clear_scheduled_hook('aDBc_optimize_scheduler');
	wp_clear_scheduled_hook('aDBc_clean_scheduler');

	// Testing for MU is useful to delete options and tasks created by older versions of the plugin ( < 2.0.0 ) in network sites
	if(function_exists('is_multisite') && is_multisite()){
		global $wpdb;
		$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach($blogs_ids as $blog_id){
			switch_to_blog($blog_id);
			delete_option('aDBc_optimize_schedule');
			delete_option('aDBc_clean_schedule');
			wp_clear_scheduled_hook('aDBc_optimize_scheduler');
			wp_clear_scheduled_hook('aDBc_clean_scheduler');
			restore_current_blog();
		}
	}
}

/********************************************************************
*
* The admin page of the plugin
*
********************************************************************/
function aDBc_main_page_callback(){ ?>
	<div class="wrap">
		<h2>Advanced Database Cleaner</h2>
		<?php
		global $aDBc_settings;
		if($aDBc_settings['top_main_msg'] == "1"){ ?>
			<div id="aDBc_main_msg" class="updated aDBc-top-main-msg">
				<span><?php _e('Welcome!', 'advanced-database-cleaner'); ?></span>
				<p style="font-size:15px">
					<?php _e('Before doing any clean-up, please make sure to always backup your database first.', 'advanced-database-cleaner'); ?>
					<br/>
					<span style="font-size:12px;color:#999"><?php _e('Once you read and understand this message, you can disable it from settings Tab.', 'advanced-database-cleaner'); ?></span>
				</p>
			</div>
		<?php } ?>
		<div class="aDBc-margin-r-300">
			<div class="aDBc-tab-box">
				<?php
				$aDBc_tabs = array('general'  => __('General clean-up', 'advanced-database-cleaner'),
								   'optimize' => __('Optimize', 'advanced-database-cleaner'),
								   'tables'   => __('Tables', 'advanced-database-cleaner'),
								   'options'  => __('Options', 'advanced-database-cleaner'),
								   'cron'  	  => __('Scheduled tasks', 'advanced-database-cleaner'),
								   'overview' => __('Overview & Settings', 'advanced-database-cleaner'),
								   'premium'  => __('Premium', 'advanced-database-cleaner')
							);

				$current_tab = isset($_GET['aDBc_tab']) ? $_GET['aDBc_tab'] : 'general';
				
				echo '<h2 class="nav-tab-wrapper">';
				foreach($aDBc_tabs as $tab => $name){
					$class = ($tab == $current_tab) ? ' nav-tab-active' : '';
					$link = "?page=advanced_db_cleaner&aDBc_tab=$tab";
					if($tab == "tables" || $tab == "options" || $tab == "cron"){
						$link .= '&aDBc_cat=all';
					}
					echo "<a class='nav-tab$class' href='$link'>$name</a>";
				}
				echo '</h2>';

				echo '<div class="aDBc-tab-box-div">';
				switch ($current_tab){
					case 'general' :
						include_once 'includes/clean_db.php';
						break;
					case 'optimize' :
						include_once 'includes/class_optimize_tables.php';
						break;
					case 'tables' :
						include_once 'includes/class_clean_tables.php';
						break;
					case 'options' :
						include_once 'includes/class_clean_options.php';
						break;
					case 'cron' :
						include_once 'includes/class_clean_cron.php';
						break;
					case 'overview' :
						include_once 'includes/overview_settings.php';
						break;						
					case 'premium' :
						include_once 'includes/premium_page.php';
						break;						
				}
				echo '</div>';
				?>
			</div>
			<div class="aDBc-sidebar"><?php include_once 'includes/sidebar.php'; ?></div>
		</div>
	</div>
<?php 
}

/***************************************************************
*
* Get functions
*
***************************************************************/
include_once 'includes/functions.php';

?>