<?php
class ADBC_Clean_DB_List extends WP_List_Table {

	private $aDBc_message = "";
	private $aDBc_class_message = "updated";
	private $aDBc_elements_to_display = array();
	private $aDBc_total_elements_to_clean = 0;	

    /**
     * Constructor
     */
    function __construct(){

        parent::__construct(array(
            'singular'  => __('Element', 'advanced-database-cleaner'),		//singular name of the listed records
            'plural'    => __('Elements', 'advanced-database-cleaner'),	//plural name of the listed records
            'ajax'      => false	//does this table support ajax?
		));

		if(isset($_POST['aDBc_save_schedule'])){
			wp_clear_scheduled_hook('aDBc_clean_scheduler');
			if($_POST['aDBc_clean_schedule'] == 'no_schedule'){
				delete_option('aDBc_clean_schedule');
			}else{
				update_option('aDBc_clean_schedule', $_POST['aDBc_clean_schedule']);
				wp_schedule_event(time()+60, $_POST['aDBc_clean_schedule'], 'aDBc_clean_scheduler');
			}
			$this->aDBc_message = __('The clean-up schedule saved successfully!', 'advanced-database-cleaner');
		}		

		$this->aDBc_prepare_elements_to_clean();
		$this->aDBc_print_page_content();
    }

	/** Prepare elements to display */
	function aDBc_prepare_elements_to_clean(){
		global $wpdb;
		// Process bulk action if any before preparing elements to clean
		$this->process_bulk_action();		
		// Get all unused elements
		$aDBc_unused_elements = aDBc_count_all_elements_to_clean();
		$aDBc_new_URI = $_SERVER['REQUEST_URI'];
		foreach($aDBc_unused_elements as $element_type => $element_info){
			// Count total unused elements
			$this->aDBc_total_elements_to_clean += $element_info['count'];
			if($element_info['count'] > 0){
				$aDBc_count = $element_info['count'];
				$aDBc_new_URI = add_query_arg('aDBc_view', $element_type, $aDBc_new_URI);
				$aDBc_see = "<a href='$aDBc_new_URI'><img alt='view' src='".ADBC_PLUGIN_DIR_PATH . '/images/see.png'."'/></a>";
			}else{
				$aDBc_count = "<img src='".ADBC_PLUGIN_DIR_PATH . '/images/check_ok.png'."'/>";
				$aDBc_see = "<img alt='-' src='".ADBC_PLUGIN_DIR_PATH . '/images/nothing_to_see.png'."'/>";
			}
			array_push($this->aDBc_elements_to_display, array(
				'element_to_clean' 	=> $element_info['name'],
				'count' 			=> $aDBc_count,
				'view'   			=> $aDBc_see,
				'type'				=> $element_type
				)
			);
		}
		// Call WP prepare_items function
		$this->prepare_items();
	}

	/** WP: Get columns */
	function get_columns(){
		$columns = array(
			'cb'        		=> '<input type="checkbox" />',
			'element_to_clean' 	=> __('Element to clean','advanced-database-cleaner'),
			'count' 			=> __('Count','advanced-database-cleaner'),
			'view'   			=> __('View','advanced-database-cleaner'),
			'type'   			=> 'Type'
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
		// Prepare sequence of elements to display
		$display_data = array_slice($this->aDBc_elements_to_display,(($current_page-1) * $per_page), $per_page);
		$this->set_pagination_args( array(
			'total_items' => count($this->aDBc_elements_to_display),
			'per_page'    => $per_page
		));
		$this->items = $display_data;
	}

	/** WP: Get columns that should be hidden */
    function get_hidden_columns(){
		return array('type');
    }	

	/** WP: Column default */
	function column_default($item, $column_name){
		switch($column_name){
			case 'element_to_clean':
			case 'count':	
			case 'view':
			case 'type':
				return $item[$column_name];
			default:
			  return print_r($item, true) ; //Show the whole array for troubleshooting purposes
		}
	}

	/** WP: Column cb for check box */
	function column_cb($item) {
		return sprintf('<input type="checkbox" name="aDBc_elements_to_clean[]" value="%s" />', $item['type']);
	}

	/** WP: Get bulk actions */
	function get_bulk_actions() {
		$actions = array(
			'clean'    => __('Clean','advanced-database-cleaner')
		);
		return $actions;
	}

	/** WP: Message to display when no items found */
	function no_items() {
		_e('Your database is clean!','advanced-database-cleaner');
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
        if($action == 'clean'){
			// If the user wants to clean the elements he/she selected
			if(isset($_POST['aDBc_elements_to_clean'])){
				global $wpdb;
				foreach($_POST['aDBc_elements_to_clean'] as $element) {
					aDBc_clean_all_elements_type($element);
				}
				// Update the message to show to the user
				$this->aDBc_message = __('Selected elements successfully cleaned!', 'advanced-database-cleaner');
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
					// Print the elements to clean
					$this->display();
					?>
				</form>
			</div>
			<div class="aDBc-right-box">
				<div style="text-align:center">
					<?php if($this->aDBc_total_elements_to_clean == 0){ ?>
						<img src="<?php echo ADBC_PLUGIN_DIR_PATH . '/images/db_clean.png'?>"/>
						<div class="aDBc-text-status-db"><?php _e('Your database is clean!','advanced-database-cleaner'); ?></div>
					<?php } else { ?>
						<img src="<?php echo ADBC_PLUGIN_DIR_PATH . '/images/db_not_clean.png'?>"/>
						<div class="aDBc-text-status-db"><b><?php echo $this->aDBc_total_elements_to_clean; ?></b> <?php _e('element(s) should be cleaned!','advanced-database-cleaner'); ?></div>		
					<?php }  ?>
				</div>
				<div class="aDBc-schedule-box">
					<div class="aDBc-schedule-text">&nbsp;<?php _e('Schedule','advanced-database-cleaner'); ?></div>
					<form action="" method="post">
						<select class="aDBc-schedule-select" name="aDBc_clean_schedule">
							<?php
							$aDBc_schedule = get_option('aDBc_clean_schedule');
							?>				
							<option value="no_schedule" <?php echo $aDBc_schedule == 'no_schedule' ? 'selected="selected"' : ''; ?>>
								<?php _e('Not scheduled','advanced-database-cleaner');?>
							</option>
							<option value="hourly" <?php echo $aDBc_schedule == 'hourly' ? 'selected="selected"' : ''; ?>>
								<?php _e('Run clean-up hourly','advanced-database-cleaner');?>
							</option>
							<option value="twicedaily" <?php echo $aDBc_schedule == 'twicedaily' ? 'selected="selected"' : ''; ?>>
								<?php _e('Run clean-up twice a day','advanced-database-cleaner');?>
							</option>
							<option value="daily" <?php echo $aDBc_schedule == 'daily' ? 'selected="selected"' : ''; ?>>
								<?php _e('Run clean-up daily','advanced-database-cleaner');?>
							</option>
							<option value="weekly" <?php echo $aDBc_schedule == 'weekly' ? 'selected="selected"' : ''; ?>>
								<?php _e('Run clean-up weekly','advanced-database-cleaner');?>
							</option>
							<option value="monthly" <?php echo $aDBc_schedule == 'monthly' ? 'selected="selected"' : ''; ?>>
								<?php _e('Run clean-up monthly','advanced-database-cleaner');?>
							</option>
						</select>
						<input name="aDBc_save_schedule" type="submit" class="button-primary" value="<?php _e('Save','advanced-database-cleaner'); ?>" />
					</form>
					<div style="padding-top:10px">
						&nbsp;<?php _e('Next run:','advanced-database-cleaner'); ?>
						<span style="color:green">
							<?php 
							if(wp_next_scheduled('aDBc_clean_scheduler')){
								echo get_date_from_gmt(date('Y-m-d H:i:s', wp_next_scheduled('aDBc_clean_scheduler')), 'M j, Y - H:i:s');
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
		<div id="aDBc_dialog1" title="<?php _e('Cleaning...','advanced-database-cleaner'); ?>" class="aDBc-jquery-dialog">
			<p class="aDBc-box-warning">
				<?php _e('You are about to clean some of your unused data. This operation is irreversible. Don\'t forget to make a backup first.','advanced-database-cleaner'); ?>
			</p>
			<p>
				<?php _e('Are you sure to continue?','advanced-database-cleaner'); ?>
			</p>
		</div>		
		<div id="aDBc_dialog2" title="<?php _e('Action required','advanced-database-cleaner'); ?>" class="aDBc-jquery-dialog">
			<p class="aDBc-box-info">
				<?php _e('Please select an action!','advanced-database-cleaner'); ?>
			</p>
		</div>		
	<?php
	}
}

new ADBC_Clean_DB_List();
?>