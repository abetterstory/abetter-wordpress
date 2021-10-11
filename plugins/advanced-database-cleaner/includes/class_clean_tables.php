<?php

class ADBC_Tables_List extends WP_List_Table {

	/** Holds the message to be displayed if any */
	private $aDBc_message = "";

	/** Holds the class for the message : updated or error. Default is updated */
	private $aDBc_class_message = "updated";

	/** Holds tables that will be displayed */
	private $aDBc_tables_to_display = array();

	/** Holds counts + info of tables categories */
	private $aDBc_tables_categories_info	= array();

	/** Should we display "run search" or "continue search" button (after a timeout failed). Default is "run search" */
	private $aDBc_which_button_to_show = "new_search";

	private $aDBc_total_tables_to_optimize = 0;
	private $aDBc_total_lost = 0;
	private $aDBc_tables_name_to_optimize = array();

	private $aDBc_total_tables_to_repair = 0;
	private $aDBc_tables_name_to_repair = array();

	// This array contains belongs_to info about plugins and themes
	private $array_belongs_to_counts = array();

	// Holds msg that will be shown if the scan has finished with success
	private $aDBc_search_has_finished_msg = "";

	// Holds msg that will be shown if folder adbc_uploads cannot be created by the plugin (This is verified after clicking on scan button)
	private $aDBc_permission_adbc_folder_msg = "";		

    function __construct(){

        parent::__construct(array(
            'singular'  => __('Table', 'advanced-database-cleaner'),		//singular name of the listed records
            'plural'    => __('Tables', 'advanced-database-cleaner'),	//plural name of the listed records
            'ajax'      => false	//does this table support ajax?
		));

		$this->aDBc_prepare_and_count_tables();
		$this->aDBc_print_page_content();
    }

	/** Prepare tables to display and count tables for each category */
	function aDBc_prepare_and_count_tables(){

		// Verify if the search has finished to let user know about it and invite it to double check against our server
		$search_finished = get_option("aDBc_last_search_ok_tables");
		if(!empty($search_finished)){
			$this->aDBc_search_has_finished_msg = __('The process of scanning has finished with success!','advanced-database-cleaner');
			// Once we display success msg, we delete that option to not be loaded
			delete_option("aDBc_last_search_ok_tables");
		}

		// Verify if the adbc_uploads cannot be created
		$adbc_folder_permission = get_option("aDBc_permission_adbc_folder_needed");
		if(!empty($adbc_folder_permission)){
			$this->aDBc_permission_adbc_folder_msg = sprintf(__('The plugin needs to create the following directory "%1$s" to save the scan results but this was not possible automatically. Please create that directory manually and set correct permissions so it can be writable by the plugin.','advanced-database-cleaner'), ADBC_UPLOAD_DIR_PATH_TO_ADBC);
			// Once we display the msg, we delete that option from DB
			delete_option("aDBc_permission_adbc_folder_needed");
		}

		// Test if user wants to delete a scheduled task
		if(isset($_POST['aDBc_delete_schedule'])){

			//Quick nonce security check!
			if(!check_admin_referer('delete_optimize_schedule_nonce', 'delete_optimize_schedule_nonce'))
				return; //get out if we didn't click the delete link

			// We delete the schedule
			$aDBc_sanitized_schedule_name = sanitize_html_class($_POST['aDBc_delete_schedule']);
			wp_clear_scheduled_hook('aDBc_optimize_scheduler', array($aDBc_sanitized_schedule_name));

			// We delete the item from database
			$aDBc_schedules = get_option('aDBc_optimize_schedule');
			unset($aDBc_schedules[$aDBc_sanitized_schedule_name]);
			update_option('aDBc_optimize_schedule', $aDBc_schedules, "no");

			$this->aDBc_message = __('The clean-up schedule deleted successfully!', 'advanced-database-cleaner');
		}

		// Process bulk action if any before preparing tables to display
		$this->process_bulk_action();

		// Get the names of all tables that should be optimized and count them to print it in the right side of the page
		global $wpdb;
		$aDBc_tables_to_optimize = $wpdb->get_results("SELECT table_name, data_free FROM information_schema.tables WHERE table_schema = '" . DB_NAME ."' and Engine <> 'InnoDB' and data_free > 0");
		$this->aDBc_total_tables_to_optimize = count($aDBc_tables_to_optimize);
		foreach($aDBc_tables_to_optimize as $table){

			// Get table name
			$table_name = "";
			// This test to prevent issues in MySQL 8 where tables are not shown
			// MySQL 5 uses $table->table_name while MySQL 8 uses $table->TABLE_NAME
			if(property_exists($table, "table_name")){
				$table_name = $table->table_name;
			}else if(property_exists($table, "TABLE_NAME")){
				$table_name = $table->TABLE_NAME;
			}

			array_push($this->aDBc_tables_name_to_optimize, $table_name);
			$this->aDBc_total_lost += $table->data_free;
		}

		// Get the names of all tables that should be repaired and count them to print it in the right side of the page
		$aDBc_tables_maybe_repair = $wpdb->get_results("SELECT table_name FROM information_schema.tables WHERE table_schema = '" . DB_NAME ."' and Engine IN ('CSV', 'MyISAM', 'ARCHIVE')");
		foreach($aDBc_tables_maybe_repair as $table){

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
						array_push($this->aDBc_tables_name_to_repair, $table_name);
					}
				}
			}
		}
		$this->aDBc_total_tables_to_repair = count($this->aDBc_tables_name_to_repair);

		// Prepare data
		aDBc_prepare_items_to_display(
			$this->aDBc_tables_to_display,
			$this->aDBc_tables_categories_info,
			$this->aDBc_which_button_to_show,
			$this->aDBc_tables_name_to_optimize,
			$this->aDBc_tables_name_to_repair,
			$this->array_belongs_to_counts,
			$this->aDBc_message,
			$this->aDBc_class_message,
			"tables"
		);

		// Call WP prepare_items function
		$this->prepare_items();
	}

	/** WP: Get columns */
	function get_columns(){
		$aDBc_belongs_to_toolip = "<span class='aDBc-tooltips-headers'>
									<img class='aDBc-info-image' src='".  ADBC_PLUGIN_DIR_PATH . '/images/information2.svg' . "'/>
									<span>" . __('Indicates the creator of the table: either a plugin, a theme or WordPress itself. If not sure about the creator, an estimation (%) will be displayed. The higher the percentage is, the more likely that the table belongs to that creator.','advanced-database-cleaner') ." </span>
								  </span>";	
		$columns = array(
			'cb'          		=> '<input type="checkbox" />',
			'table_prefix' 		=> __('Prefix','advanced-database-cleaner'),
			'table_name' 		=> __('Table name','advanced-database-cleaner'),
			'table_rows' 		=> __('Rows','advanced-database-cleaner'),
			'table_size' 		=> __('Size','advanced-database-cleaner'),
			'table_lost' 		=> __('Lost','advanced-database-cleaner'),
			'site_id'   		=> __('Site','advanced-database-cleaner'),
			'table_belongs_to'  => __('Belongs to','advanced-database-cleaner') . $aDBc_belongs_to_toolip
		);
		return $columns;
	}

	function get_sortable_columns() {

		$sortable_columns = array(
			'table_name'   		=> array('table_name',false),
			'table_rows'    	=> array('table_rows',false),
			'table_size'    	=> array('table_size',false),
			'site_id'    		=> array('site_id',false)
		);

		return $sortable_columns;
	}	
	
	/** WP: Prepare items to display */
	function prepare_items() {
		$columns 	= $this->get_columns();
		$hidden 	= $this->get_hidden_columns();
		$sortable 	= $this->get_sortable_columns();
		$this->_column_headers 	= array($columns, $hidden, $sortable);
		$per_page 	= 50;
		if(!empty($_GET['per_page'])){
			$per_page = absint($_GET['per_page']);
		}
		$current_page = $this->get_pagenum();
		// Prepare sequence of tables to display
		$display_data = array_slice($this->aDBc_tables_to_display,(($current_page-1) * $per_page), $per_page);
		$this->set_pagination_args( array(
			'total_items' => count($this->aDBc_tables_to_display),
			'per_page'    => $per_page
		));
		$this->items = $display_data;
	}

	/** WP: Get columns that should be hidden */
    function get_hidden_columns(){
		// If MU, nothing to hide, else hide Side ID column
		if(function_exists('is_multisite') && is_multisite()){
			return array('table_prefix','table_lost');
		}else{
			return array('table_prefix','table_lost', 'site_id');
		}
    }

	/** WP: Column default */
	function column_default($item, $column_name){
		switch($column_name){
			case 'table_name':
				$prefix_and_name = $item['table_prefix'] . $item[$column_name];
				$return_name = "<span style='font-weight:bold;'>" . $item['table_prefix'] . "</span>" . $item[$column_name] ;
				if($item['table_lost'] > 0 && in_array($prefix_and_name, $this->aDBc_tables_name_to_optimize)){
					$lost = aDBc_get_size_from_bytes($item['table_lost']);
					$return_name .= "<br/><span style='color:red;font-size:12px'><b>".__('Lost space','advanced-database-cleaner')."</b></span><span style='font-size:12px'> : " . $lost . "</span> <span style='color:grey'> (" .  __('to optimize','advanced-database-cleaner') . ")</span>";
				}
				if(in_array($prefix_and_name, $this->aDBc_tables_name_to_repair)){
					$return_name .= "<br/><span style='color:red;font-size:12px'><b>".__('Corrupted!','advanced-database-cleaner')."</b></span><span style='color:grey'> (" .  __('to repair','advanced-database-cleaner') . ")</span>";
				}
				return $return_name;
				break;
			case 'table_size':
				return aDBc_get_size_from_bytes($item['table_size']);
				break;
			case 'table_lost':
				return aDBc_get_size_from_bytes($item['table_lost']);
				break;
			case 'table_prefix':				
			case 'table_rows':
			case 'site_id':
			case 'table_belongs_to':
			  return $item[$column_name];
			default:
			  return print_r($item, true) ; //Show the whole array for troubleshooting purposes
		}
	}

	/** WP: Column cb for check box */
	function column_cb($item) {
		return sprintf('<input type="checkbox" name="aDBc_elements_to_process[]" value="%s" />', $item['table_prefix']."|".$item['table_name']);
	}

	/** WP: Get bulk actions */
	function get_bulk_actions() {

		$actions = array(
			'optimize'  => __('Optimize','advanced-database-cleaner'),
			'repair'    => __('Repair','advanced-database-cleaner'),
			'empty'    	=> __('Empty rows','advanced-database-cleaner'),
			'delete'    => __('Delete','advanced-database-cleaner')
		);
		return $actions;
	}

	/** WP: Message to display when no items found */
	function no_items() {
		_e('No tables found!','advanced-database-cleaner');
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

		// Prepare an array containing names of tables deleted
		$names_deleted = array();

        if($action == 'delete'){
			// If the user wants to clean the tables he/she selected
			if(isset($_POST['aDBc_elements_to_process'])){
				global $wpdb;
				foreach($_POST['aDBc_elements_to_process'] as $table){
					$table_info 	= explode("|", $table);
					$table_prefix 	= sanitize_html_class($table_info[0]);
					$table_name 	= sanitize_text_field($table_info[1]);
					// We delete some characters we believe they should not appear in the name: & < > = # ( ) [ ] { } ? " ' 
					$table_name 	= preg_replace("/[&<>=#\(\)\[\]\{\}\?\"\' ]/", '', $table_name);					
					if($wpdb->query("DROP TABLE " . $table_prefix . $table_name)){
						array_push($names_deleted, $table_name);
					}
				}

				// Update the message to show to the user
				$this->aDBc_message = __('Selected tables cleaned successfully!', 'advanced-database-cleaner');
			}
        }else if($action == 'optimize'){
			// If the user wants to optimize the tables he/she selected
			if(isset($_POST['aDBc_elements_to_process'])){
				global $wpdb;
				foreach($_POST['aDBc_elements_to_process'] as $table) {
					$table_info 	= explode("|", $table);
					$table_prefix 	= sanitize_html_class($table_info[0]);
					$table_name 	= sanitize_text_field($table_info[1]);
					// We delete some characters we believe they should not appear in the name: & < > = # ( ) [ ] { } ? " ' 
					$table_name 	= preg_replace("/[&<>=#\(\)\[\]\{\}\?\"\' ]/", '', $table_name);
					$wpdb->query("OPTIMIZE TABLE " . $table_prefix . $table_name);
				}
				// Update the message to show to the user
				$this->aDBc_message = __('Selected tables optimized successfully!', 'advanced-database-cleaner');
			}
        }else if($action == 'empty'){
			// If the user wants to empty the tables he/she selected
			if(isset($_POST['aDBc_elements_to_process'])){
				global $wpdb;
				foreach($_POST['aDBc_elements_to_process'] as $table) {
					$table_info 	= explode("|", $table);
					$table_prefix 	= sanitize_html_class($table_info[0]);
					$table_name 	= sanitize_text_field($table_info[1]);
					// We delete some characters we believe they should not appear in the name: & < > = # ( ) [ ] { } ? " ' 
					$table_name 	= preg_replace("/[&<>=#\(\)\[\]\{\}\?\"\' ]/", '', $table_name);
					$wpdb->query("TRUNCATE TABLE " . $table_prefix . $table_name);
				}
				// Update the message to show to the user
				$this->aDBc_message = __('Selected tables emptied successfully!', 'advanced-database-cleaner');
			}
        }else if($action == 'repair'){
			// If the user wants to repair the tables he/she selected
			if(isset($_POST['aDBc_elements_to_process'])){
				global $wpdb;
				$cannot_repair = 0;
				foreach($_POST['aDBc_elements_to_process'] as $table) {
					$table_info 	= explode("|", $table);
					$table_prefix 	= sanitize_html_class($table_info[0]);
					$table_name 	= sanitize_text_field($table_info[1]);
					// We delete some characters we believe they should not appear in the name: & < > = # ( ) [ ] { } ? " ' 
					$table_name 	= preg_replace("/[&<>=#\(\)\[\]\{\}\?\"\' ]/", '', $table_name);
					$query_result 	= $wpdb->get_results("REPAIR TABLE " . $table_prefix . $table_name);
					foreach($query_result as $row){
						if($row->Msg_type == 'error'){
							if(preg_match('/corrupt/i', $row->Msg_text)){
								$cannot_repair++;
							}
						}
					}
				}
				// Update the message to show to the user
				if($cannot_repair == 0){
					$this->aDBc_message = __('Selected tables repaired successfully!', 'advanced-database-cleaner');
				}else{
					$this->aDBc_class_message = "error";
					$this->aDBc_message = __('Some of your tables cannot be repaired!', 'advanced-database-cleaner');
				}
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
		$still_searching = get_option("aDBc_temp_still_searching_tables");
		if(!empty($still_searching)){
			// This means that the ajax call is still searching
			$aDBc_still_searching_msg  = __('The process of categorization is still scanning tables in background. Maybe you have reloaded the page before it finishes the scan. The scan will stop automatically after scanning all items or after timeout.','advanced-database-cleaner');
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

			<div class="aDBc-clear-both" style="margin-top:15px"></div>

			<!-- Code for "run new search" button + Show loading image -->
			<div style="float:left;">
				<?php 
				if($this->aDBc_which_button_to_show == "new_search" ){
					$aDBc_search_text  = __('Scan tables','advanced-database-cleaner');
				}else{
					$aDBc_search_text  = __('Continue scannig ...','advanced-database-cleaner');
				}
				?>

				<!-- This hidden input is used by ajax to know which item type we are dealing with -->
				<input type="hidden" id="aDBc_item_type" value="tables"/>
				<?php 
				// These hidden inputs are used by ajax to see if we should execute scanning process automatically by ajax after reloading a page
				$iteration = get_option("aDBc_temp_last_iteration_tables");
				?>
				<input type="hidden" id="aDBc_still_searching" value="<?php echo $still_searching; ?>"/>
				<input type="hidden" id="aDBc_iteration" value="<?php echo $iteration; ?>"/>

				<div class="aDBc_premium_tooltip">
					<input id="aDBc_new_search_button" type="submit" class="aDBc-run-new-search" value="<?php echo $aDBc_search_text; ?>"  name="aDBc_new_search_button" style="opacity:0.5" disabled/>
					<span style="width:390px" class="aDBc_premium_tooltiptext"><?php _e('Please <a href="?page=advanced_db_cleaner&aDBc_tab=premium">upgrade</a> to Pro to categorize and detect orphaned tables','advanced-database-cleaner') ?></span>
				</div>

			</div>

			<!-- Print numbers of tables found in each category -->
			<div class="aDBc-category-counts">
				<?php
				$aDBc_new_URI = $_SERVER['REQUEST_URI'];
				// Remove the paged parameter to start always from the first page when selecting a new category of tables
				$aDBc_new_URI = remove_query_arg('paged', $aDBc_new_URI);
				$iterations = 0;
				foreach($this->aDBc_tables_categories_info as $abreviation => $category_info){
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
				<div class="aDBc-left-content">
					<?php
					// Print the tables
					$this->display();
					?>
				</div>
			</form>

			<div class="aDBc-right-box">	
				<div style="text-align:center">
					<?php if($this->aDBc_total_tables_to_optimize == 0 && $this->aDBc_total_tables_to_repair == 0){ ?>
						<img width="58px" src="<?php echo ADBC_PLUGIN_DIR_PATH . '/images/db_clean.svg'?>"/>
						<div class="aDBc-text-status-db"><?php _e('Your database is optimized!','advanced-database-cleaner'); ?></div>
					<?php } else {

							// Add link to numbers of tables that should be optimized
							$aDBc_new_URI = $_SERVER['REQUEST_URI'];
							$aDBc_new_URI = remove_query_arg(array('paged', 's', 'belongs_to'), $aDBc_new_URI);
							$aDBc_new_URI = add_query_arg('t_type', 'optimize', $aDBc_new_URI);
							$aDBc_new_URI = add_query_arg('aDBc_cat', 'all', $aDBc_new_URI);
							?>

						<img width="55px" src="<?php echo ADBC_PLUGIN_DIR_PATH . '/images/warning.svg'?>"/>

						<?php if($this->aDBc_total_tables_to_optimize > 0){ ?>
							<div class="aDBc-text-status-db">
								<b><a href="<?php echo $aDBc_new_URI; ?>"><?php echo $this->aDBc_total_tables_to_optimize; ?></a></b> <?php _e('table(s) should be optimized!','advanced-database-cleaner'); ?>
							</div>
							<div>
							<?php 
								$aDBc_table_size = aDBc_get_size_from_bytes($this->aDBc_total_lost);
								echo __('You can save around','advanced-database-cleaner') . " : " . $aDBc_table_size; 
							?>
							</div>
						<?php } ?>

						<?php if($this->aDBc_total_tables_to_repair > 0){
							$aDBc_new_URI = add_query_arg('t_type', 'repair', $aDBc_new_URI);
						?>
							<div class="aDBc-text-status-db" style="<?php echo $this->aDBc_total_tables_to_optimize > 0 ? 'padding-top:10px;margin-top:10px;border-top:1px dashed grey' : ''; ?>">
								<b><a href="<?php echo $aDBc_new_URI; ?>"><?php echo $this->aDBc_total_tables_to_repair; ?></a></b> <?php _e('table(s) should be repaired!','advanced-database-cleaner'); ?>
							</div>
						<?php } ?>							

					<?php }  ?>
				</div>

				<div class="aDBc-schedule-box" style="text-align:center">

					<img width="60px" src="<?php echo ADBC_PLUGIN_DIR_PATH . '/images/alarm-clock.svg'?>"/>

					<?php
					$aDBc_schedules = get_option('aDBc_optimize_schedule');
					$aDBc_schedules = is_array($aDBc_schedules) ? $aDBc_schedules : array();

					// Count schedules available
					$count_schedules = count($aDBc_schedules);
					echo "<div class='aDBc-schedule-text'><b>" . $count_schedules ."</b> " .__('optimize schedule(s) set','advanced-database-cleaner') . "</div>";

					foreach($aDBc_schedules as $hook_name => $hook_params){
						echo "<div class='aDBc-schedule-hook-box'>";
						echo "<b>".__('Name','advanced-database-cleaner') . "</b> : " . $hook_name;
						echo "</br>";

						// We convert hook name to a string because the arg maybe only a digit!
						$timestamp = wp_next_scheduled("aDBc_optimize_scheduler", array($hook_name . ''));
						if($timestamp){
							$next_run = get_date_from_gmt(date('Y-m-d H:i:s', $timestamp), 'M j, Y - H:i');
						}else{
							$next_run = "---";
						}
						echo "<b>".__('Next run','advanced-database-cleaner') . "</b> : " . $next_run . "</br>";

						$operation1 = in_array('optimize', $hook_params['operations']) ? __('Optimize','advanced-database-cleaner') : '';
						$operation2 = in_array('repair', $hook_params['operations']) ? __('Repair','advanced-database-cleaner') : '';
						$plus = !empty($operation1) && !empty($operation2) ? " + " : "";
						echo "<b>".__('Perform','advanced-database-cleaner') . "</b> : " . $operation1 . $plus . $operation2 . "</br>";

						$repeat = $hook_params['repeat'];
						switch($repeat){
							case "once" :
								$repeat = __('Once','advanced-database-cleaner');
								break;
							case "hourly" :
								$repeat = __('Hourly','advanced-database-cleaner');
								break;
							case "twicedaily" :
								$repeat = __('Twice a day','advanced-database-cleaner');
								break;
							case "daily" :
								$repeat = __('Daily','advanced-database-cleaner');
								break;
							case "weekly" :
								$repeat = __('Weekly','advanced-database-cleaner');
								break;
							case "monthly" :
								$repeat = __('Monthly','advanced-database-cleaner');
								break;									
						}

						echo "<b>".__('Frequency','advanced-database-cleaner') . "</b> : " . $repeat . "</br>";

						echo $hook_params['active'] == "1" ? "<img class='aDBc-schedule-on-off' src='". ADBC_PLUGIN_DIR_PATH . "/images/switch-on.svg" . "'/>" : "<img class='aDBc-schedule-on-off' src='". ADBC_PLUGIN_DIR_PATH . "/images/switch-off.svg" . "'/>";

						$aDBc_new_URI = $_SERVER['REQUEST_URI'];
						$aDBc_new_URI = add_query_arg('aDBc_view', 'edit_optimize_schedule', $aDBc_new_URI);
						$aDBc_new_URI = add_query_arg('hook_name', $hook_name, $aDBc_new_URI);

					?>

						<span class="aDBc-edit-schedule">
							<a href="<?php echo $aDBc_new_URI ?>" style="text-decoration:none;margin-right:3px">
							<?php _e('Edit','advanced-database-cleaner') ?>
							</a> | 
							<form action="" method="post" style="float:right;margin-left:3px">
								<input type="hidden" name="aDBc_delete_schedule" value="<?php echo $hook_name ?>" />
								<input class="aDBc-submit-link" type="submit" value="<?php _e('Delete','advanced-database-cleaner') ?>" />
								<?php wp_nonce_field('delete_optimize_schedule_nonce', 'delete_optimize_schedule_nonce') ?>
							</form>
						</span>
						</div>
					<?php

					}

					$aDBc_new_URI = $_SERVER['REQUEST_URI'];
					$aDBc_new_URI = add_query_arg('aDBc_view', 'add_optimize_schedule', $aDBc_new_URI);
					?>	

					<a href="<?php echo $aDBc_new_URI ?>" id="aDBc_add_schedule" style="margin-top:20px;width:100%" class="button-primary">
					<?php _e('Add new schedule','advanced-database-cleaner'); ?>
					</a>					

				</div>

			</div>	

			<div class="aDBc-clear-both"></div>

		</div>	

	<?php
	}
}

new ADBC_Tables_List();

?>