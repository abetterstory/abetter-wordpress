<?php
class ADBC_SCHEDULE_CLEANUP extends WP_List_Table {

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

		// Test if user wants to save the scheduled task
		if(isset($_POST['aDBc_schedule_name'])){

			//Quick nonce security check!
			if(!check_admin_referer('add_cleanup_schedule_nonce', 'add_cleanup_schedule_nonce'))
				return; //get out if we didn't click the save_schedule button

			if(!empty($_POST['aDBc_elements_to_process'])){
				if(!empty(trim($_POST['aDBc_schedule_name']))){
					if(preg_match('/^[a-zA-Z0-9_]+$/',$_POST['aDBc_schedule_name'])){

						// Test if the name is used by other schedules.
						$clean_schedule_setting = get_option('aDBc_clean_schedule');
						$clean_schedule_setting = is_array($clean_schedule_setting) ? $clean_schedule_setting : array();

						$optimize_schedule_setting = get_option('aDBc_optimize_schedule');
						$optimize_schedule_setting = is_array($optimize_schedule_setting) ? $optimize_schedule_setting : array();

						if(!array_key_exists($_POST['aDBc_schedule_name'], $clean_schedule_setting) &&
						   !array_key_exists($_POST['aDBc_schedule_name'], $optimize_schedule_setting)){

							if(!empty($_POST['aDBc_date'])){
								if(!empty($_POST['aDBc_time'])){

									// We will create the new schedule
									$sanitized_elements_to_process = array();
									foreach($_POST['aDBc_elements_to_process'] as $element){
										array_push($sanitized_elements_to_process, sanitize_html_class($element));
									}

									$new_schedule_params['elements_to_clean'] 	= $sanitized_elements_to_process;
									$new_schedule_params['repeat'] 				= sanitize_html_class($_POST['aDBc_schedule_repeat']);
									$new_schedule_params['start_date'] 			= preg_replace("/[^0-9-]/", '', $_POST['aDBc_date']);
									$new_schedule_params['start_time'] 			= preg_replace("/[^0-9:]/", '', $_POST['aDBc_time']);
									$new_schedule_params['active'] 				= sanitize_html_class($_POST['aDBc_status']);
									$clean_schedule_setting[$_POST['aDBc_schedule_name']] = $new_schedule_params;
									update_option('aDBc_clean_schedule', $clean_schedule_setting, "no");

									list($year, $month, $day) 	= explode('-', preg_replace("/[^0-9-]/", '', $_POST['aDBc_date']));
									list($hours, $minutes) 		= explode(':', preg_replace("/[^0-9:]/", '', $_POST['aDBc_time']));
									$seconds = "0";
									$timestamp =  mktime($hours, $minutes, $seconds, $month, $day, $year);

									if($_POST['aDBc_status'] == "1"){
										if($_POST['aDBc_schedule_repeat'] == "once"){
											wp_schedule_single_event($timestamp, "aDBc_clean_scheduler", array($_POST['aDBc_schedule_name']));
										}else{
											wp_schedule_event($timestamp, sanitize_html_class($_POST['aDBc_schedule_repeat']), "aDBc_clean_scheduler", array($_POST['aDBc_schedule_name']));
										}
										$this->aDBc_message = __('The clean-up schedule saved successfully!', 'advanced-database-cleaner');
									}else{
										$this->aDBc_message = __('The clean-up schedule saved successfully but it is inactive!', 'advanced-database-cleaner');
									}										

								}else{
									$this->aDBc_class_message = "error";
									$this->aDBc_message = __('Please specify a valide time!', 'advanced-database-cleaner');
								}
							}else{
								$this->aDBc_class_message = "error";
								$this->aDBc_message = __('Please specify a valide date!', 'advanced-database-cleaner');
							}
						}else{
							$this->aDBc_class_message = "error";
							$this->aDBc_message = __('The name you have specified is already used by another schedule! Please change it!', 'advanced-database-cleaner');
						}
					}else{
						$this->aDBc_class_message = "error";
						$this->aDBc_message = __('Please change the name! Only letters, numbers and underscores are allowed!', 'advanced-database-cleaner');
					}
				}else{
					$this->aDBc_class_message = "error";
					$this->aDBc_message = __('Please give a name to your schedule!', 'advanced-database-cleaner');
				}
			}else{
				$this->aDBc_class_message = "error";
				$this->aDBc_message = __('Please select at least one item to include in the schedule from the table below!', 'advanced-database-cleaner');
			}
			
		}
		
		// yyy should this $wpdb be cleaned?
		global $wpdb;

		// Get all unused elements
		$aDBc_unused_elements = aDBc_return_array_all_elements_to_clean();

		// Get settings from DB
		$settings = get_option('aDBc_settings');

		foreach($aDBc_unused_elements as $element_type => $element_name){

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

					$keep_info = "<span>" . $keep_number . " " . __('days','advanced-database-cleaner');
			}else{
				$keep_info = __('N/A','advanced-database-cleaner') ;
			}

			array_push($this->aDBc_elements_to_display, array(
				'element_to_schedule' 	=> "<a href='" . $element_name['URL_blog'] . "' target='_blank' class='aDBc_info_icon'>&nbsp;</a>" . $element_name['name'],
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

		$aDBc_keep_last_toolip = "<span class='aDBc-tooltips-headers'>
									<img class='aDBc-info-image' src='".  ADBC_PLUGIN_DIR_PATH . '/images/information2.svg' . "'/>
									<span>" . __('Only data that is older than the number you have specified will be cleaned based on you schedule parameters. To change this value, click on "go back" button.','advanced-database-cleaner') ." </span>
								  </span>";	

		$columns = array(
			'cb'        		=> '<input type="checkbox" />',
			'element_to_schedule' 	=> __('Elements to include in the schedule','advanced-database-cleaner'),
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
			case 'element_to_schedule':
			case 'keep':
			case 'type':
				return $item[$column_name];
			default:
			  return print_r($item, true) ; //Show the whole array for troubleshooting purposes
		}
	}

	/** WP: Column cb for check box */
	function column_cb($item) {
		$checked = "";
		if(isset($_POST['aDBc_elements_to_process'])){
			if(in_array($item['type'], $_POST['aDBc_elements_to_process'])){
				$checked = "checked";
			}
		}
		return sprintf('<input type="checkbox" name="aDBc_elements_to_process[]" value="%s"' .  $checked . '/>', $item['type']);
	}

	/** WP: Get bulk actions */
	function get_bulk_actions() {
		return array();
	}

	/** WP: Message to display when no items found */
	function no_items() {
		_e('Your database is clean!','advanced-database-cleaner');
	}


	/** Print the page content */
	function aDBc_print_page_content(){
		// Print a message if any
		if($this->aDBc_message != ""){
			echo '<div id="aDBc_message" class="' . $this->aDBc_class_message . ' notice is-dismissible"><p>' . $this->aDBc_message . '</p></div>';
		}
		?>
		<div style="width:636px">
		
			<div>
				<div class="aDBc-float-left aDBc-margin-t-10">
					<a href="?page=advanced_db_cleaner&aDBc_tab=general">
						<img width="40px" src="<?php echo ADBC_PLUGIN_DIR_PATH . '/images/go_back.svg'?>"/>
					</a>
				</div>
				<div class="aDBc-float-right" style="border:1px solid #f0f0f0;box-shadow:0 0 10px #eee;border-radius:5px;text-align:center;width:190px;background:#fff;padding:10px;font-size:16px;margin-top: 15px;margin-bottom: 15px;color: #0992CC;">
					<img style="margin-right:10px;vertical-align:middle" width="15px" src="<?php echo ADBC_PLUGIN_DIR_PATH . '/images/add_schedule.svg'?>"/>
					<?php echo __('Add cleanup schedule','advanced-database-cleaner') ; ?>
				</div>
			</div>
			
			<div class="aDBc-clear-both"></div>
	<form id="aDBc_form" action="" method="post">

			<div style="float: left;width: 400px;margin-right: 25px">
				<?php
				// Print the elements to clean
				$this->display();
				?>
			</div>

			<div class="aDBc-right-box">

				<div style="text-align:center">

					<img width="60px" src="<?php echo ADBC_PLUGIN_DIR_PATH . '/images/alarm-clock.svg'?>"/>
					<br/><br/>

						<div id="add_schedule" style="border-top:1px dashed #ccc">
							<br/>
							<div style="text-align:left"><?php _e('Name your schedule','advanced-database-cleaner');?></div>
							<input style="width:100%;margin-bottom:10px;height:30px;border-radius:5px;box-shadow:0 0 10px #e0e0e0" type="text" name="aDBc_schedule_name" placeholder="Schedule name" value="<?php echo isset($_POST['aDBc_schedule_name']) ? esc_attr($_POST['aDBc_schedule_name']) : ""?>" maxlength="25">
							
							<div style="text-align:left"><?php _e('Frequency of execution','advanced-database-cleaner');?></div>
							<select style="width:100%;margin-bottom:10px;height:30px;border-radius:5px;box-shadow:0 0 10px #e0e0e0" class="aDBc-schedule-select" name="aDBc_schedule_repeat">	
							<?php
								$schedules_repeat = array('once' 		=> __('Once','advanced-database-cleaner'),
														  'hourly' 		=> __('Hourly','advanced-database-cleaner'),
														  'twicedaily' 	=> __('Twice a day','advanced-database-cleaner'),
														  'daily' 		=> __('Daily','advanced-database-cleaner'),
													      'weekly' 		=> __('Weekly','advanced-database-cleaner'),
													      'monthly' 	=> __('Monthly','advanced-database-cleaner'));

								foreach($schedules_repeat as $code_repeat => $name_repeat){
									if(isset($_POST['aDBc_schedule_repeat']) && $_POST['aDBc_schedule_repeat'] == $code_repeat){
										echo "<option value='$code_repeat' selected='selected'>$name_repeat</option>";
									}else{
										echo "<option value='$code_repeat'>$name_repeat</option>";
									}
								}
							?>
							</select>

							<div style="text-align:left"><?php _e('Start date','advanced-database-cleaner');?></div>
							<input style="width:100%;margin-bottom:10px;height:30px;border-radius:5px;box-shadow:0 0 10px #e0e0e0" type="date" name="aDBc_date" placeholder="" value="<?php echo isset($_POST['aDBc_date']) ? esc_attr($_POST['aDBc_date']) : date("Y-m-d"); ?>" min="<?php echo date("Y-m-d"); ?>">

							<div style="text-align:left"><?php _e('Start time (GMT)','advanced-database-cleaner');?></div>
							<input style="width:100%;margin-bottom:10px;height:30px;border-radius:5px;box-shadow:0 0 10px #e0e0e0" type="time" name="aDBc_time" value="<?php echo isset($_POST['aDBc_time']) ? esc_attr($_POST['aDBc_time']) : date("H:i", time()); ?>">

							<div style="text-align:left"><?php _e('Schedule status','advanced-database-cleaner');?></div>

							<div style="margin-top:2px;text-align:left;background:#fff;padding:5px;box-shadow:0 0 10px #e0e0e0;border-radius:5px">
									<input type="radio" name="aDBc_status" value="1" checked> 
									<span style="margin-right:20px"><?php _e('Active','advanced-database-cleaner');?></span>

									<input type="radio" name="aDBc_status" value="0" <?php echo (isset($_POST['aDBc_status']) && $_POST['aDBc_status'] == "0") ? 'checked' : ''; ?>>
									<?php _e('Inactive','advanced-database-cleaner');?>
							</div>

							<div style="width:100%;margin-top:20px">
								<input class="button-primary" type="submit"  value="<?php _e('Save the schedule','advanced-database-cleaner'); ?>" style="width:100%;"/>
							</div>

						</div>
				</div>
			</div>	

			<?php wp_nonce_field('add_cleanup_schedule_nonce', 'add_cleanup_schedule_nonce'); ?>

	</form>
			<div class="aDBc-clear-both"></div>
		</div>

	<?php

	}
}

?>