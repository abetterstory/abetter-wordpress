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

		$this->aDBc_prepare_elements_to_clean();
		$this->aDBc_print_page_content();
    }

	/** Prepare elements to display */
	function aDBc_prepare_elements_to_clean(){

		// Test if user wants to delete a scheduled task
		if(isset($_POST['aDBc_delete_schedule'])){

			//Quick nonce security check!
			if(!check_admin_referer('delete_cleanup_schedule_nonce', 'delete_cleanup_schedule_nonce'))
				return; //get out if we didn't click the delete link

			// We delete the schedule
			$aDBc_sanitized_schedule_name = sanitize_html_class($_POST['aDBc_delete_schedule']);
			wp_clear_scheduled_hook('aDBc_clean_scheduler', array($aDBc_sanitized_schedule_name));

			// We delete the item from database
			$aDBc_schedules = get_option('aDBc_clean_schedule');
			unset($aDBc_schedules[$aDBc_sanitized_schedule_name]);
			update_option('aDBc_clean_schedule', $aDBc_schedules, "no");

			$this->aDBc_message = __('The clean-up schedule deleted successfully!', 'advanced-database-cleaner');
		}

		// Test if user wants to edit keep_last column for an item
		if(isset($_POST['aDBc_keep_input'])){

			$sanitized_keep_input 			= sanitize_html_class($_POST['aDBc_keep_input']);
			$sanitized_item_keep_to_edit 	= sanitize_html_class($_POST['aDBc_item_keep_to_edit']);
			$settings 						= get_option('aDBc_settings');

			if(empty($settings['keep_last'])){
				$keep_value = array($sanitized_item_keep_to_edit => intval($sanitized_keep_input));
			}else{
				$keep_value = $settings['keep_last'];
				$keep_value[$sanitized_item_keep_to_edit] = intval($sanitized_keep_input);
			}
			$settings['keep_last'] = $keep_value;	
			update_option('aDBc_settings', $settings, "no");

			// Test if the items belongs to a scheduled task. If so, show msg differently
			$aDBc_schedules = get_option('aDBc_clean_schedule');
			$aDBc_schedules = is_array($aDBc_schedules) ? $aDBc_schedules : array();			
			$msg_keep_last = __("The 'keep last' value saved successfully!", "advanced-database-cleaner");
			foreach($aDBc_schedules as $hook_name => $hook_params){
				$lits_of_elements = $hook_params['elements_to_clean'];
				if(in_array($sanitized_item_keep_to_edit, $lits_of_elements)){
					$msg_keep_last = __("The 'keep last' value saved successfully!", "advanced-database-cleaner") . " <span style='color:orange'>" .  __("Please keep in mind that this will change the value of 'keep last' of your corresponding scheduled tasks as well!", "advanced-database-cleaner") . "</span>";
					break;
				}
			}

			$this->aDBc_message = $msg_keep_last;
		}

		// Process bulk action if any before preparing elements to clean
		$this->process_bulk_action();

		// Get all unused elements
		$aDBc_unused_elements = aDBc_count_all_elements_to_clean();
		$aDBc_new_URI = $_SERVER['REQUEST_URI'];

		// Get settings from DB
		$settings = get_option('aDBc_settings');

		$aDBc_schedules = get_option('aDBc_clean_schedule');
		$aDBc_schedules = is_array($aDBc_schedules) ? $aDBc_schedules : array();

		foreach($aDBc_unused_elements as $element_type => $element_info){
			// Count total unused elements. DO not take into account transient with expiration and not expiring transients because they are not intended to be cleaned
			if($element_type != "transients-with-expiration" && $element_type != "transients-with-no-expiration")
				$this->aDBc_total_elements_to_clean += $element_info['count'];

			// If the item is scheduled, show green image, otherwise show grey one. Select also the text to show next green image
			$scheduled_img_name = "grey_clock.svg";
			$item_scheduled_in = "";
			foreach($aDBc_schedules as $hook_name => $hook_params){
				$lits_of_elements = $hook_params['elements_to_clean'];
				if(in_array ($element_type, $lits_of_elements)){
					$scheduled_img_name = "green_clock.svg";
					$item_scheduled_in .=  "<div style='background:#f1f5f5;color:#000;border-radius:4px;padding:1px;margin:2px'>" . $hook_name . "</div>";
				}
			}
			if(empty($item_scheduled_in)){
				$aDBc_scheduled = "<img style='width:20px' alt='-' src='".ADBC_PLUGIN_DIR_PATH . "/images/" . $scheduled_img_name . "'/>";
			}else{
				$aDBc_scheduled = "<span class='aDBc-tooltips-headers'>
								<img class='aDBc-info-image' style='width:20px' alt='-' src='".ADBC_PLUGIN_DIR_PATH . "/images/" . $scheduled_img_name . "'/><span style='width:190px'>" . __('Scheduled in:','advanced-database-cleaner') . $item_scheduled_in . "</span></span>";
			}

			if($element_info['count'] > 0){
				$color = "red";
				if($element_type == "transients-with-expiration" || $element_type == "transients-with-no-expiration"){
					$color = "#999";
				}
				$aDBc_count = "<font color='$color' style='font-weight:bold'>" . $element_info['count'] . "</font>";
				$aDBc_new_URI = add_query_arg('aDBc_view', $element_type, $aDBc_new_URI);
				$aDBc_see = "<a href='$aDBc_new_URI'><img width='20px' alt='view' src='".ADBC_PLUGIN_DIR_PATH . '/images/see.svg'."'/></a>";
			}else{
				$aDBc_count = "<font color='#ccc' style='font-weight:bold'>0</font>";
				$aDBc_see = "<img width='20px' alt='-' src='".ADBC_PLUGIN_DIR_PATH . '/images/nothing_to_see.svg'."'/>";
			}

			// Get "keep_last" option. This option is added in ADBC version 3.0, so test if it is not empty before using it
			if(empty($settings['keep_last'])){
				$keep_number = '0';
			}else{
				$keep_setting = $settings['keep_last'];
				if(empty($keep_setting[$element_type])){
					$keep_number = '0';
				}else{
					$keep_number = $keep_setting[$element_type];
				}
			}
			// If the item can have keep_last, then prepare it, otherwise echo N/A
			if($element_type == "revision" || 
				$element_type == "auto-draft" || 
				$element_type == "trash-posts" || 
				$element_type == "moderated-comments" || 
				$element_type == "spam-comments" || 
				$element_type == "trash-comments" || 
				$element_type == "pingbacks" || 
				$element_type == "trackbacks"){

					$save_button = __('Save','advanced-database-cleaner');

					$keep_info = "<span id='aDBc_keep_label_$element_type'>" . $keep_number . " " . __('days','advanced-database-cleaner') .  " | </span>" . "<a id='aDBc_edit_keep_$element_type' class='aDBc_keep_link'>Edit</a>";

					$keep_info .= "<form action='' method='post'>
						<input type='hidden' name='aDBc_item_keep_to_edit' value='$element_type'>
						<input id='aDBc_keep_input_$element_type' class='aDBc_keep_input' name='aDBc_keep_input' value='$keep_number'/>
						<input id='aDBc_keep_button_$element_type' class='aDBc_keep_button button-primary' type='submit'  value='$save_button' style='display: none;'/>
						<a id='aDBc_keep_cancel_$element_type' class='aDBc_keep_cancel_link'> " . __('Cancel','advanced-database-cleaner')  . "</a></form>";
			}else{
				$keep_info = __('N/A','advanced-database-cleaner') ;
			}
			
			
			if($element_type == "revision"){ 

			}else if($element_type == "revision"){
				$keep_info = __('N/A','advanced-database-cleaner') ;
			}			

			array_push($this->aDBc_elements_to_display, array(
				'element_to_clean' 	=> "<a href='". $element_info['URL_blog'] ."' target='_blank' class='aDBc_info_icon'>&nbsp;</a>" . $element_info['name'],
				'count' 			=> $aDBc_count,
				'view'   			=> $aDBc_see,
				'scheduled'   		=> $aDBc_scheduled,
				'keep'   			=> $keep_info,
				'type'				=> $element_type
				)
			);
		}
		// Call WP prepare_items function
		$this->prepare_items();
	}

	/** WP: Get columns */
	function get_columns(){

		$aDBc_scheduled_toolip = "<span class='aDBc-tooltips-headers'>
									<img class='aDBc-info-image' src='".  ADBC_PLUGIN_DIR_PATH . '/images/information2.svg' . "'/>
									<span>" . __('Indicates if you have selected the item to be cleaned automatically on a scheduled task. A green image indicates that the item is scheduled while a grey image indicated the opposite.','advanced-database-cleaner') ." </span>
								  </span>";

		$aDBc_keep_last_toolip = "<span class='aDBc-tooltips-headers'>
									<img class='aDBc-info-image' src='".  ADBC_PLUGIN_DIR_PATH . '/images/information2.svg' . "'/>
									<span>" . __('Keep the last x days’ data from being displayed, and therefore from being cleaned. The plugin will always show only data older than the number of days you have specified.','advanced-database-cleaner') ." </span>
								  </span>";	

		$columns = array(
			'cb'        		=> '<input type="checkbox" />',
			'element_to_clean' 	=> __('Elements to clean','advanced-database-cleaner'),
			'count' 			=> __('Count','advanced-database-cleaner'),
			'view'   			=> __('View','advanced-database-cleaner'),
			'scheduled'   		=> __('Scheduled','advanced-database-cleaner') . $aDBc_scheduled_toolip,
			'keep'   			=> __('Keep last','advanced-database-cleaner') . $aDBc_keep_last_toolip,
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
			case 'scheduled':
			case 'keep':
			case 'type':
				return $item[$column_name];
			default:
			  return print_r($item, true) ; //Show the whole array for troubleshooting purposes
		}
	}

	/** WP: Column cb for check box */
	function column_cb($item) {
		return sprintf('<input id="checkbox_%s" type="checkbox" name="aDBc_elements_to_process[]" value="%s" />', $item['type'], $item['type']);
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
        }else{
			// If $_POST['_wpnonce'] is not set, return
			return;
		}

		// Check role
		if(!current_user_can('administrator'))
			wp_die('Security check failed!');

        $action = $this->current_action();

        if($action == 'clean'){

			// If the user wants to clean the elements he/she selected
			if(isset($_POST['aDBc_elements_to_process'])){

				// Create an array containing allowed elements_types to clean for security
				$aDBc_allowed_types = array("revision", "auto-draft", "trash-posts", "moderated-comments", "spam-comments", "trash-comments", "pingbacks", "trackbacks", "orphan-postmeta", "orphan-commentmeta", "orphan-relationships", "orphan-usermeta", "orphan-termmeta", "expired-transients");

				foreach($_POST['aDBc_elements_to_process'] as $element){
					$aDBc_sanitized_element = sanitize_html_class($element);
					if(in_array($aDBc_sanitized_element, $aDBc_allowed_types)){
						aDBc_clean_all_elements_type($aDBc_sanitized_element);
					}
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
						<img width="58px" src="<?php echo ADBC_PLUGIN_DIR_PATH . '/images/db_clean.svg'?>"/>
						<div class="aDBc-text-status-db"><?php _e('Your database is clean!','advanced-database-cleaner'); ?></div>
					<?php } else { ?>
						<img width="55px" src="<?php echo ADBC_PLUGIN_DIR_PATH . '/images/warning.svg'?>"/>
						<div class="aDBc-text-status-db"><b><?php echo $this->aDBc_total_elements_to_clean; ?></b> <?php _e('Element(s) can be cleaned!','advanced-database-cleaner'); ?></div>		
					<?php }  ?>
				</div>

				<div class="aDBc-schedule-box" style="text-align:center">

					<img width="60px" src="<?php echo ADBC_PLUGIN_DIR_PATH . '/images/alarm-clock.svg'?>"/>

					<?php
					$aDBc_schedules = get_option('aDBc_clean_schedule');
					$aDBc_schedules = is_array($aDBc_schedules) ? $aDBc_schedules : array();

					// Count schedules available
					$count_schedules = count($aDBc_schedules);
					echo "<div class='aDBc-schedule-text'><b>" . $count_schedules ."</b> " .__('Cleanup schedule(s) set','advanced-database-cleaner') . "</div>";

					foreach($aDBc_schedules as $hook_name => $hook_params){
						echo "<div class='aDBc-schedule-hook-box'>";
						echo "<b>".__('Name','advanced-database-cleaner') . "</b> : " . $hook_name;
						echo "</br>";

						// We convert hook name to a string because the arg maybe only a digit!
						$timestamp = wp_next_scheduled("aDBc_clean_scheduler", array($hook_name . ''));
						if($timestamp){
							$next_run = get_date_from_gmt(date('Y-m-d H:i:s', $timestamp), 'M j, Y - H:i');
						}else{
							$next_run = "---";
						}
						echo "<b>".__('Next run','advanced-database-cleaner') . "</b> : " . $next_run . "</br>";
						
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

						echo "<b>".__('Items to clean','advanced-database-cleaner') . " : </b>" . count($hook_params['elements_to_clean'])."</br>";

						echo $hook_params['active'] == "1" ? "<img class='aDBc-schedule-on-off' src='". ADBC_PLUGIN_DIR_PATH . "/images/switch-on.svg" . "'/>" : "<img class='aDBc-schedule-on-off' src='". ADBC_PLUGIN_DIR_PATH . "/images/switch-off.svg" . "'/>";

						$aDBc_new_URI = $_SERVER['REQUEST_URI'];
						$aDBc_new_URI = add_query_arg('aDBc_view', 'edit_cleanup_schedule', $aDBc_new_URI);
						$aDBc_new_URI = add_query_arg('hook_name', $hook_name, $aDBc_new_URI);

					?>

						<span style="border-radius: 4px;font-size:11px;background:#f0f5fa;padding:2px 4px;float:right;margin-top:4px">
							<a href="<?php echo $aDBc_new_URI ?>" style="text-decoration:none;margin-right:3px">
							<?php _e('Edit','advanced-database-cleaner') ?>
							</a> | 
							<form action="" method="post" style="float:right;margin-left:3px">
								<input type="hidden" name="aDBc_delete_schedule" value="<?php echo $hook_name ?>" />
								<input class="aDBc-submit-link" type="submit" value="<?php _e('Delete','advanced-database-cleaner') ?>" /> 
								<?php wp_nonce_field('delete_cleanup_schedule_nonce', 'delete_cleanup_schedule_nonce') ?>
							</form>
						</span>
						</div>

					<?php

					}

					$aDBc_new_URI = $_SERVER['REQUEST_URI'];
					$aDBc_new_URI = add_query_arg('aDBc_view', 'add_cleanup_schedule', $aDBc_new_URI);
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

new ADBC_Clean_DB_List();
?>