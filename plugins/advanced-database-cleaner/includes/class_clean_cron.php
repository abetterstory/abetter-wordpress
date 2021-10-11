<?php

class ADBC_Tasks_List extends WP_List_Table {

	/** Holds the message to be displayed if any */
	private $aDBc_message = "";

	/** Holds the class for the message : updated or error. Default is updated */
	private $aDBc_class_message = "updated";

	/** Holds tasks that will be displayed */
	private $aDBc_tasks_to_display = array();

	/** Holds counts + info of tasks categories */
	private $aDBc_tasks_categories_info	= array();

	/** Should we display "run search" or "continue search" button (after a timeout failed). Default is "run search" */
	private $aDBc_which_button_to_show = "new_search";

	// This array contains belongs_to info about plugins and themes
	private $array_belongs_to_counts = array();

	// Holds msg that will be shown if the scan has finished with success
	private $aDBc_search_has_finished_msg = "";

	// Holds msg that will be shown if folder adbc_uploads cannot be created by the plugin (This is verified after clicking on scan button)
	private $aDBc_permission_adbc_folder_msg = "";		

    function __construct(){

        parent::__construct(array(
            'singular'  => __('Task', 'advanced-database-cleaner'),		//singular name of the listed records
            'plural'    => __('Tasks', 'advanced-database-cleaner'),	//plural name of the listed records
            'ajax'      => false	//does this table support ajax?
		));

		$this->aDBc_prepare_and_count_tasks();
		$this->aDBc_print_page_content();
    }

	/** Prepare tasks to display and count tasks for each category */
	function aDBc_prepare_and_count_tasks(){

		// Verify if the search has finished to let user know about it and invite it to double check against our server
		$search_finished = get_option("aDBc_last_search_ok_tasks");
		if(!empty($search_finished)){
			$this->aDBc_search_has_finished_msg = __('The process of scanning has finished with success!','advanced-database-cleaner');
			// Once we display success msg, we delete that option to not be loaded
			delete_option("aDBc_last_search_ok_tasks");
		}

		// Verify if the adbc_uploads cannot be created
		$adbc_folder_permission = get_option("aDBc_permission_adbc_folder_needed");
		if(!empty($adbc_folder_permission)){
			$this->aDBc_permission_adbc_folder_msg = sprintf(__('The plugin needs to create the following directory "%1$s" to save the scan results but this was not possible automatically. Please create that directory manually and set correct permissions so it can be writable by the plugin.','advanced-database-cleaner'), ADBC_UPLOAD_DIR_PATH_TO_ADBC);
			// Once we display the msg, we delete that option from DB
			delete_option("aDBc_permission_adbc_folder_needed");
		}

		// Process bulk action if any before preparing tasks to display
		$this->process_bulk_action();

		// Prepare data
		aDBc_prepare_items_to_display(
			$this->aDBc_tasks_to_display,
			$this->aDBc_tasks_categories_info,
			$this->aDBc_which_button_to_show,
			array(),
			array(),
			$this->array_belongs_to_counts,			
			$this->aDBc_message,
			$this->aDBc_class_message,
			"tasks"
		);

		// Call WP prepare_items function
		$this->prepare_items();
	}

	/** WP: Get columns */
	function get_columns(){
		$aDBc_belongs_to_toolip = "<span class='aDBc-tooltips-headers'>
									<img class='aDBc-info-image' src='".  ADBC_PLUGIN_DIR_PATH . '/images/information2.svg' . "'/>
									<span>" . __('Indicates the creator of the task: either a plugin, a theme or WordPress itself. If not sure about the creator, an estimation (%) will be displayed. The higher the percentage is, the more likely that the task belongs to that creator.','advanced-database-cleaner') ." </span>
								  </span>";		
		$columns = array(
			'cb'        		=> '<input type="checkbox" />',
			'hook_name' 		=> __('Hook name','advanced-database-cleaner'),
			'arguments' 		=> __('Arguments','advanced-database-cleaner'),
			'next_run'  		=> __('Next run - Frequency','advanced-database-cleaner'),
			'site_id'   		=> __('Site','advanced-database-cleaner'),
			'hook_belongs_to'  	=> __('Belongs to','advanced-database-cleaner') . $aDBc_belongs_to_toolip
		);
		return $columns;
	}

	function get_sortable_columns() {

		$sortable_columns = array(
			'hook_name'   	=> array('hook_name',false),
			'site_id'    		=> array('site_id',false)
		);

		return $sortable_columns;
	}

	/** WP: Prepare items to display */
	function prepare_items() {
		$columns 	= $this->get_columns();
		$hidden 	= $this->get_hidden_columns();
		$sortable 	= $this->get_sortable_columns();
		$this->_column_headers  = array($columns, $hidden, $sortable);
		$per_page 	= 50;
		if(!empty($_GET['per_page'])){
			$per_page = absint($_GET['per_page']);
		}		
		$current_page = $this->get_pagenum();
		// Prepare sequence of options to display
		$display_data = array_slice($this->aDBc_tasks_to_display,(($current_page-1) * $per_page), $per_page);
		$this->set_pagination_args( array(
			'total_items' => count($this->aDBc_tasks_to_display),
			'per_page'    => $per_page
		));
		$this->items = $display_data;
	}

	/** WP: Get columns that should be hidden */
    function get_hidden_columns(){
		// If MU, nothing to hide, else hide Side ID column
		if(function_exists('is_multisite') && is_multisite()){
			return array();
		}else{
			return array('site_id');
		}
    }	

	/** WP: Column default */
	function column_default($item, $column_name){
		switch($column_name){
			case 'arguments':
				if($item[$column_name] == "none"){
					return "<span style='color:#888'>".__('None', 'advanced-database-cleaner')."</span>";
				}else{
					$unserialized_args = unserialize($item[$column_name]);
					return "<span style='background:#eee;padding:2px;border-radius:2px'>" . implode(" / ", $unserialized_args) . "</span>";
				}
				break;
			case 'hook_name':
			case 'next_run':
			case 'site_id':
			case 'hook_belongs_to':
			  return $item[$column_name];
			default:
			  return print_r($item, true) ; //Show the whole array for troubleshooting purposes
		}
	}

	/** WP: Column cb for check box */
	function column_cb($item) {
		return sprintf("<input type='checkbox' name='aDBc_elements_to_process[]' value='%s' />", $item['site_id']."|".$item['hook_name']."|".$item['timestamp']."|".$item['arguments']);
	}

	/** WP: Get bulk actions */
	function get_bulk_actions() {
		$actions = array(
			'delete'    => __('Delete','advanced-database-cleaner')
		);
		return $actions;
	}

	/** WP: Message to display when no items found */
	function no_items() {
		_e('No tasks found!','advanced-database-cleaner');
	}

	/** WP: Process bulk actions */
    public function process_bulk_action() {
        // security check!
		if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])){
			$nonce  = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
			$action = 'bulk-' . $this->_args['plural'];
			if (!wp_verify_nonce( $nonce, $action))
				wp_die('Security check failed!');
		}else{
			// If $_POST['_wpnonce'] is not set, return
			return;
		}

		// Check role
		if(!current_user_can('administrator'))
			wp_die('Security check failed!');		

        $action = $this->current_action();

        if($action == 'delete'){
			// If the user wants to clean the tasks he/she selected
			if(isset($_POST['aDBc_elements_to_process'])){
				if(function_exists('is_multisite') && is_multisite()){
					// Prepare tasks to delete in organized array to minimize switching from blogs
					$tasks_to_delete = array();
					foreach($_POST['aDBc_elements_to_process'] as $task){
						$task_info 	= explode("|", $task, 2);
						$site_id 	= sanitize_html_class($task_info[0]);
						if(is_numeric($site_id)){
							if(empty($tasks_to_delete[$site_id])){
								$tasks_to_delete[$site_id] = array();
							}
							array_push($tasks_to_delete[$site_id], $task);
						}
					}
					// Delete tasks
					foreach($tasks_to_delete as $site_id => $tasks_info){
						switch_to_blog($site_id);
						foreach($tasks_info as $task) {
							$aDBc_cron_info = explode("|", $task, 4);
							$hook 			= sanitize_text_field($aDBc_cron_info[1]);
							// We delete some characters we believe they should not appear in the name: & < > = # ( ) [ ] { } ? " ' 
							$hook 			= preg_replace("/[&<>=#\(\)\[\]\{\}\?\"\' ]/", '', $hook);
							$timestamp 		= sanitize_html_class($aDBc_cron_info[2]);
							$args 			= sanitize_text_field($aDBc_cron_info[3]);
							if(is_numeric($timestamp)){
								if($args == "none"){
									wp_unschedule_event($timestamp, $hook);
								}else{
									$args = unserialize(stripslashes($args));
									wp_unschedule_event($timestamp, $hook, $args);
									// Check if the user has deleted a task beloging to this plugin. If so, update his data in DB to inactive
									// A task of ADBC cannot be without an arg, not necessary to add this check to "none" args
									if($hook == "aDBc_clean_scheduler"){
										aDBc_update_task_in_db_after_delete(sanitize_html_class($args[0]), "aDBc_clean_schedule");
									}else if($hook == "aDBc_optimize_scheduler"){
										aDBc_update_task_in_db_after_delete(sanitize_html_class($args[0]), "aDBc_optimize_schedule");
									}								
								}
							}
						}
						restore_current_blog();
					}
				}else{
					foreach($_POST['aDBc_elements_to_process'] as $task){
						$aDBc_cron_info = explode("|", $task, 4);
						$hook 			= sanitize_text_field($aDBc_cron_info[1]);
						// We delete some characters we believe they should not appear in the name: & < > = # ( ) [ ] { } ? " ' 
						$hook 			= preg_replace("/[&<>=#\(\)\[\]\{\}\?\"\' ]/", '', $hook);
						$timestamp 		= sanitize_html_class($aDBc_cron_info[2]);
						$args 			= sanitize_text_field($aDBc_cron_info[3]);
						if(is_numeric($timestamp)){
							if($args == "none"){
								wp_unschedule_event($timestamp, $hook);
							}else{
								$args = unserialize(stripslashes($args));
								wp_unschedule_event($timestamp, $hook, $args);
								// Check if the user has deleted a task beloging to this plugin. If so, update his data in DB to inactive
								// A task of ADBC cannot be without an arg, not necessary to add this check to "none" args
								if($hook == "aDBc_clean_scheduler"){
									aDBc_update_task_in_db_after_delete(sanitize_html_class($args[0]), "aDBc_clean_schedule");
								}else if($hook == "aDBc_optimize_scheduler"){
									aDBc_update_task_in_db_after_delete(sanitize_html_class($args[0]), "aDBc_optimize_schedule");
								}
							}
						}
					}
				}
				// Update the message to show to the user
				$this->aDBc_message = __('Selected scheduled tasks cleaned successfully!', 'advanced-database-cleaner');
			}
        }
    }

	/** Print the page content */
	function aDBc_print_page_content(){
		// Print a message if any
		if($this->aDBc_message != ""){
			echo '<div id="aDBc_message" class="' . $this->aDBc_class_message . ' notice is-dismissible"><p>' . $this->aDBc_message . '</p></div>';
		}

		// Verify if the ajax call is still searching in background to prevent enabling the button
		$still_searching = get_option("aDBc_temp_still_searching_tasks");
		if(!empty($still_searching)){
			// This means that the ajax call is still searching
			$aDBc_still_searching_msg  = __('The process of categorization is still scanning tasks in background. Maybe you have reloaded the page before it finishes the scan. The scan will stop automatically after scanning all items or after timeout.','advanced-database-cleaner');
			echo '<div class="error notice is-dismissible"><p>' . $aDBc_still_searching_msg . '</p></div>';
		}

		// If the search has finished, show a msg to users
		if(!empty($this->aDBc_search_has_finished_msg)){
			echo '<div class="updated notice is-dismissible"><p>' . $this->aDBc_search_has_finished_msg . '</p></div>';
		}

		// If the folder adbc_uploads cannot be created, show a msg to users
		if(!empty($this->aDBc_permission_adbc_folder_msg)){
			echo '<div class="error notice is-dismissible"><p>' . $this->aDBc_permission_adbc_folder_msg . '</p></div>';
		}

		?>
		<div class="aDBc-content-max-width">

			<?php
			// zzz
			$msg1 = __('Tasks below seem to be orphan! However, please delete only those you are sure to be orphan!','advanced-database-cleaner');
			$msg2 = __('Some of your tasks are not categorized yet! Please click on the button below to categorize them!','advanced-database-cleaner');
			?>

			<div class="aDBc-clear-both" style="margin-top:15px"></div>

			<!-- Code for "run new search" button + Show loading image -->
			<div style="float:left;">
				<?php 
				if($this->aDBc_which_button_to_show == "new_search" ){
					$aDBc_search_text  = __('Scan tasks','advanced-database-cleaner');
				}else{
					$aDBc_search_text  = __('Continue scannig ...','advanced-database-cleaner');
				}
				?>

				<!-- This hidden input is used by ajax to know which item type we are dealing with -->
				<input type="hidden" id="aDBc_item_type" value="tasks"/>
				<?php 
				// These hidden inputs are used by ajax to see if we should execute scanning process automatically by ajax after reloading a page
				$iteration = get_option("aDBc_temp_last_iteration_tasks");
				?>
				<input type="hidden" id="aDBc_still_searching" value="<?php echo $still_searching; ?>"/>
				<input type="hidden" id="aDBc_iteration" value="<?php echo $iteration; ?>"/>

				<div class="aDBc_premium_tooltip">
					<input id="aDBc_new_search_button" type="submit" class="aDBc-run-new-search" value="<?php echo $aDBc_search_text; ?>"  name="aDBc_new_search_button" style="opacity:0.5" disabled/>
					<span style="width:380px" class="aDBc_premium_tooltiptext"><?php _e('Please <a href="?page=advanced_db_cleaner&aDBc_tab=premium">upgrade</a> to Pro to categorize and detect orphaned tasks','advanced-database-cleaner') ?></span>
				</div>

			</div>

			<!-- Print numbers of tasks found in each category -->
			<div class="aDBc-category-counts">
				<?php
				$aDBc_new_URI = $_SERVER['REQUEST_URI'];
				// Remove the paged parameter to start always from the first page when selecting a new category of tables
				$aDBc_new_URI = remove_query_arg('paged', $aDBc_new_URI);
				$iterations = 0;
				foreach($this->aDBc_tasks_categories_info as $abreviation => $category_info){
					$iterations++;
					$aDBc_new_URI = add_query_arg('aDBc_cat', $abreviation, $aDBc_new_URI);?>
					<span class="<?php echo $abreviation == $_GET['aDBc_cat'] ? 'aDBc-selected-category' : ''?>" style="<?php echo $abreviation == $_GET['aDBc_cat'] ? 'border-bottom: 1px solid ' . $category_info['color'] : '' ?>">

						<?php 
						if($abreviation == "all"|| $abreviation == "u"){
							$aDBc_link_style = "color:" . $category_info['color'];
							$aDBc_category_info_count = "(". $category_info['count'] . ")";
						}else{
							$aDBc_link_style = "color:" . $category_info['color'] . ";cursor:default;pointer-events:none";
							$aDBc_category_info_count = "(*)";
							$aDBc_new_URI = "";
						}
						?>

						<span class="aDBc_premium_tooltip">
							<a href="<?php echo $aDBc_new_URI; ?>" class="aDBc-category-counts-links" style="<?php echo $aDBc_link_style ; ?>">
								<span><?php echo $category_info['name']; ?></span>
								<span><?php echo $aDBc_category_info_count ;?></span>
							</a>
							<?php if($abreviation != "all" && $abreviation != "u"){ ?>
								<span style="width:150px" class="aDBc_premium_tooltiptext"><?php _e('Available in Pro version!','advanced-database-cleaner') ?></span>
							<?php } ?>
						</span>

					</span>
					<?php
					if($iterations < 6){
						echo '<span class="aDBc-category-separator">|</span>';
					}
				}?>
			</div>		

			<div class="aDBc-clear-both"></div>

			<div id="aDBc_progress_container">
				<div style="background:#ccc;width:100%;height:20px">
					<div id="aDBc-progress-bar" class="aDBc_progress-bar"></div>
				</div>
				<div id="aDBc-response_container"></div>
			</div>

			<?php include_once 'header_page_filter.php'; ?>

			<div class="aDBc-clear-both"></div>

			<form id="aDBc_form" action="" method="post">
				<?php
				// Print the tasks
				$this->display();
				?>
			</form>

		</div>

	<?php
	}
}

new ADBC_Tasks_List();

?>