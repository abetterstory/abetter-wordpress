<?php

class ADBC_Tables_To_Optimize_List extends WP_List_Table {

	private $aDBc_message = "";
	private $aDBc_class_message = "updated";
	private $aDBc_tables_to_display = array();
	private $aDBc_total_lost = 0;
	private $aDBc_total_tables_to_optimize = 0;	

    /**
     * Constructor
     */
    function __construct(){

        parent::__construct(array(
            'singular'  => __('Table', 'advanced-database-cleaner'),		//singular name of the listed records
            'plural'    => __('Tables', 'advanced-database-cleaner'),	//plural name of the listed records
            'ajax'      => false	//does this table support ajax?
		));

		if(isset($_POST['aDBc_save_schedule'])){
			wp_clear_scheduled_hook('aDBc_optimize_scheduler');
			if($_POST['aDBc_optimize_schedule'] == 'no_schedule'){
				delete_option('aDBc_optimize_schedule');
			}else{
				update_option('aDBc_optimize_schedule', $_POST['aDBc_optimize_schedule']);
				wp_schedule_event(time()+60, $_POST['aDBc_optimize_schedule'], 'aDBc_optimize_scheduler');
			}
			$this->aDBc_message = __('The optimization schedule saved successfully!', 'advanced-database-cleaner');
		}		

		$this->aDBc_prepare_tables_to_optimize();
		$this->aDBc_print_page_content();
    }

	/** Prepare tasks to display and count tasks for each category */
	function aDBc_prepare_tables_to_optimize(){

		global $wpdb;

		// Process bulk action if any before preparing tables to optimize
		$this->process_bulk_action();		

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

		// Get the names of all tables that should be optimized
		$aDBc_tables_to_optimize = $wpdb->get_results("SELECT table_name, data_free FROM information_schema.tables WHERE table_schema = '" . DB_NAME ."' and Engine <> 'InnoDB' and data_free > 0");

		$aDBc_all_tables_info = array();
		foreach($aDBc_tables_to_optimize as $table){
			$this->aDBc_total_lost += number_format(($table->data_free / 1024), 2);
			$aDBc_all_tables_info[$table->table_name]['lost_space'] = number_format(($table->data_free / 1024), 2, ",", "");
		}

		$this->aDBc_total_tables_to_optimize = count($aDBc_all_tables_info);

		foreach($aDBc_all_tables_info as $aDBc_table => $info){
			// Holds the possible prefixes found for the current table
			$aDBc_found_prefixes = array();
			// Test if the table name starts with a valid prefix
			foreach($aDBc_prefix_list as $prefix => $blog_id){
				if(substr($aDBc_table, 0, strlen($prefix)) === $prefix){
					array_push($aDBc_found_prefixes, $prefix);
				}
			}
			// If the table do not start with any valid prefix, we add it as it is
			$count_found_prefixes = count($aDBc_found_prefixes);
			if($count_found_prefixes == 0){
				$aDBc_all_tables_info[$aDBc_table]['table_prefix'] = "";
				$aDBc_all_tables_info[$aDBc_table]['table_name'] = $aDBc_table;
				$aDBc_all_tables_info[$aDBc_table]['site_id'] = "-";
			}else if($count_found_prefixes == 1){
				$aDBc_all_tables_info[$aDBc_table]['table_prefix'] = $aDBc_found_prefixes[0];
				$aDBc_all_tables_info[$aDBc_table]['table_name'] = substr($aDBc_table, strlen($aDBc_found_prefixes[0]));
				$aDBc_all_tables_info[$aDBc_table]['site_id'] = $aDBc_prefix_list[$aDBc_found_prefixes[0]];			
			}else{
				// If the number of possible prefixes found >= 2, we delete the longest prefix
				$aDBc_longest_prefix = "";
				foreach($aDBc_found_prefixes as $aDBc_prefix){
					if(strlen($aDBc_prefix) > strlen($aDBc_longest_prefix)){
						$aDBc_longest_prefix = $aDBc_prefix;
					}
				}
				$aDBc_all_tables_info[$aDBc_table]['table_prefix'] = $aDBc_longest_prefix;
				$aDBc_all_tables_info[$aDBc_table]['table_name'] = substr($aDBc_table, strlen($aDBc_longest_prefix));
				$aDBc_all_tables_info[$aDBc_table]['site_id'] = $aDBc_prefix_list[$aDBc_longest_prefix];					
			}
		}

		foreach($aDBc_all_tables_info as $table_name => $table_info){
			array_push($this->aDBc_tables_to_display, array(
					'o_table_prefix' 	=> $table_info['table_prefix'],
					'o_table_name' 		=> $table_info['table_name'],
					'site_id'   		=> $table_info['site_id'],
					'lost_space'  		=> $table_info['lost_space']
					)
			);
		}

		// Call WP prepare_items function
		$this->prepare_items();
	}

	/** WP: Get columns */
	function get_columns(){
		$aDBc_lost_toolip = "<a style='line-height: 18px' class='aDBc-tooltips'>
									<img class='aDBc-margin-l-3' src='".  ADBC_PLUGIN_DIR_PATH . '/images/notice.png' . "'/>
									<span>" . __('Indicates the total lost space in the table','advanced-database-cleaner') ." </span>
								</a>";	
		$columns = array(
			'cb'        	=> '<input type="checkbox" />',
			'o_table_prefix' 	=> __('Prefix','advanced-database-cleaner'),
			'o_table_name' 	=> __('Table name','advanced-database-cleaner'),
			'site_id'   	=> __('Site id','advanced-database-cleaner'),
			'lost_space'  	=> __('Lost','advanced-database-cleaner') . $aDBc_lost_toolip
		);
		return $columns;
	}

	/** WP: Prepare items to display */
	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$per_page = 50;
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
			return array();
		}else{
			return array('site_id');
		}
    }	

	/** WP: Column default */
	function column_default($item, $column_name){
		switch($column_name){
			case 'lost_space':
				return $item[$column_name] . ' KB';
			case 'o_table_prefix':
			case 'o_table_name':
			case 'site_id':
			  return $item[$column_name];
			default:
			  return print_r($item, true) ; //Show the whole array for troubleshooting purposes
		}
	}

	/** WP: Column cb for check box */
	function column_cb($item) {
		return sprintf('<input type="checkbox" name="aDBc_tables_to_optimize[]" value="%s" />', $item['o_table_prefix'].$item['o_table_name']);
	}

	/** WP: Get bulk actions */
	function get_bulk_actions() {
		$actions = array(
			'optimize'    => __('Optimize','advanced-database-cleaner')
		);
		return $actions;
	}

	/** WP: Message to display when no items found */
	function no_items() {
		_e('All tables are optimized!','advanced-database-cleaner');
	}

	/** WP: Process bulk actions */
    public function process_bulk_action() {
        // security check!
        if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])){
            $nonce  = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];
            if (!wp_verify_nonce( $nonce, $action))
                wp_die('Security check failed!');
        }
        $action = $this->current_action();
        if($action == 'optimize'){
			// If the user wants to optimize tables he/she selected
			if(isset($_POST['aDBc_tables_to_optimize'])){
				global $wpdb;
				foreach($_POST['aDBc_tables_to_optimize'] as $table) {
					$wpdb->query('OPTIMIZE TABLE '.$table);
				}
				// Update the message to show to the user
				$this->aDBc_message = __('Selected tables successfully optimized!', 'advanced-database-cleaner');
			}
        }
    }

	/** Print the page content */
	function aDBc_print_page_content(){
		// Print a message if any
		if($this->aDBc_message != ""){
			echo '<div id="aDBc_message" class="' . $this->aDBc_class_message . ' notice is-dismissible"><p>' . $this->aDBc_message . '</p></div>';
		}
		?>
		<div class="aDBc-content-max-width">
			<div class="aDBc-left-content">
				<form id="aDBc_form" action="" method="post">
					<?php
					// Print the tasks
					$this->display();
					?>
				</form>
			</div>
			<div class="aDBc-right-box">	
				<div style="text-align:center">
					<?php if($this->aDBc_total_tables_to_optimize == 0){ ?>
						<img src="<?php echo ADBC_PLUGIN_DIR_PATH . '/images/db_clean.png'?>"/>
						<div class="aDBc-text-status-db"><?php _e('Your database is optimized!','advanced-database-cleaner'); ?></div>
					<?php } else { ?>
						<img src="<?php echo ADBC_PLUGIN_DIR_PATH . '/images/db_not_clean.png'?>"/>
						<div class="aDBc-text-status-db"><b><?php echo $this->aDBc_total_tables_to_optimize; ?></b> <?php _e('table(s) should be optimized!','advanced-database-cleaner'); ?></div>		
					<?php }  ?>
				</div>

				<div class="aDBc-schedule-box">
					<div class="aDBc-schedule-text">&nbsp;<?php _e('Schedule','advanced-database-cleaner'); ?></div>
					<form action="" method="post">
						<select class="aDBc-schedule-select" name="aDBc_optimize_schedule">
							<?php
							$aDBc_schedule = get_option('aDBc_optimize_schedule');
							?>
							<option value="no_schedule" <?php echo $aDBc_schedule == 'no_schedule' ? 'selected="selected"' : ''; ?>>
								<?php _e('Not scheduled','advanced-database-cleaner');?>
							</option>
							<option value="hourly" <?php echo $aDBc_schedule == 'hourly' ? 'selected="selected"' : ''; ?>>
								<?php _e('Run optimization hourly','advanced-database-cleaner');?>
							</option>
							<option value="twicedaily" <?php echo $aDBc_schedule == 'twicedaily' ? 'selected="selected"' : ''; ?>>
								<?php _e('Run optimization twice a day','advanced-database-cleaner');?>
							</option>
							<option value="daily" <?php echo $aDBc_schedule == 'daily' ? 'selected="selected"' : ''; ?>>
								<?php _e('Run optimization daily','advanced-database-cleaner');?>
							</option>
							<option value="weekly" <?php echo $aDBc_schedule == 'weekly' ? 'selected="selected"' : ''; ?>>
								<?php _e('Run optimization weekly','advanced-database-cleaner');?>
							</option>
							<option value="monthly" <?php echo $aDBc_schedule == 'monthly' ? 'selected="selected"' : ''; ?>>
								<?php _e('Run optimization monthly','advanced-database-cleaner');?>
							</option>
						</select>	
						<input name="aDBc_save_schedule" type="submit" class="button-primary" value="<?php _e('Save','advanced-database-cleaner'); ?>" />
					</form>
					<div style="padding-top:10px">
						&nbsp;<?php _e('Next run:','advanced-database-cleaner'); ?>
						<span style="color:green">
							<?php 
							if(wp_next_scheduled('aDBc_optimize_scheduler')){
								echo get_date_from_gmt(date('Y-m-d H:i:s', wp_next_scheduled('aDBc_optimize_scheduler')), 'M j, Y - H:i:s');
							}else{
								echo _e('not set','advanced-database-cleaner');
							}
							?>
						</span>
					</div>
				</div>
			</div>		
			<div class="aDBc-clear-both"></div>
		</div>
		<div id="aDBc_dialog2" title="<?php _e('Action required','advanced-database-cleaner'); ?>" class="aDBc-jquery-dialog">
			<p class="aDBc-box-info">
				<?php _e('Please select an action!','advanced-database-cleaner'); ?>
			</p>
		</div>		
	<?php
	}
}

new ADBC_Tables_To_Optimize_List();

?>