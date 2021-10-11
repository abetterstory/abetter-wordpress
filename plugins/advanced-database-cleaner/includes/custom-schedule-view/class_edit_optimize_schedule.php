<?php
class EDIT_SCHEDULE_OPTIMIZE extends WP_List_Table {

	private $aDBc_message = "";
	private $aDBc_class_message = "updated";

    /**
     * Constructor
     */
    function __construct(){

		$this->aDBc_prepare_elements_to_clean();
		$this->aDBc_print_page_content();
    }

	/** Prepare elements to display */
	function aDBc_prepare_elements_to_clean(){

		// Test if user wants to save the edited scheduled task
		if(isset($_POST['aDBc_schedule_name'])){

			//Quick nonce security check!
			if(!check_admin_referer('edit_optimize_schedule_nonce', 'edit_optimize_schedule_nonce'))
				return; //get out if we didn't click the save_schedule button

			if(!empty(trim($_POST['aDBc_schedule_name']))){
				if(preg_match('/^[a-zA-Z0-9_]+$/',$_POST['aDBc_schedule_name'])){

					// Test if the name is used by other schedules.
					$clean_schedule_setting = get_option('aDBc_clean_schedule');
					$clean_schedule_setting = is_array($clean_schedule_setting) ? $clean_schedule_setting : array();

					$optimize_schedule_setting = get_option('aDBc_optimize_schedule');
					$optimize_schedule_setting = is_array($optimize_schedule_setting) ? $optimize_schedule_setting : array();

					if($_POST['aDBc_schedule_name'] == $_GET['hook_name'] ||
					  ($_POST['aDBc_schedule_name'] != $_GET['hook_name'] && 
					   !array_key_exists($_POST['aDBc_schedule_name'], $clean_schedule_setting) &&
					   !array_key_exists($_POST['aDBc_schedule_name'], $optimize_schedule_setting))){

						if(!empty($_POST['aDBc_date'])){
							if(!empty($_POST['aDBc_time'])){

								if(!empty($_POST['aDBc_operation1']) || !empty($_POST['aDBc_operation2'])){

									// Delete the old schedule and replace it with the new

									// We will create the new schedule
									$new_schedule_params['repeat'] 				= sanitize_html_class($_POST['aDBc_schedule_repeat']);
									$new_schedule_params['start_date'] 			= preg_replace("/[^0-9-]/", '', $_POST['aDBc_date']);
									$new_schedule_params['start_time'] 			= preg_replace("/[^0-9:]/", '', $_POST['aDBc_time']);

									// Prepare operations to perform
									$operations = array();
									if(!empty($_POST['aDBc_operation1']))
										array_push($operations, sanitize_html_class($_POST['aDBc_operation1']));
									if(!empty($_POST['aDBc_operation2']))
										array_push($operations, sanitize_html_class($_POST['aDBc_operation2']));
									$new_schedule_params['operations'] 			= $operations;

									$new_schedule_params['active'] 				= sanitize_html_class($_POST['aDBc_status']);
									$optimize_schedule_setting[$_POST['aDBc_schedule_name']] = $new_schedule_params;
									update_option('aDBc_optimize_schedule', $optimize_schedule_setting, "no");

									list($year, $month, $day) 	= explode('-', preg_replace("/[^0-9-]/", '', $_POST['aDBc_date']));
									list($hours, $minutes) 		= explode(':', preg_replace("/[^0-9:]/", '', $_POST['aDBc_time']));
									$seconds = "0";
									$timestamp =  mktime($hours, $minutes, $seconds, $month, $day, $year);

									// Clear scheduled event
									wp_clear_scheduled_hook('aDBc_optimize_scheduler', array($_POST['aDBc_schedule_name'].''));

									if($_POST['aDBc_status'] == "1"){
										if($_POST['aDBc_schedule_repeat'] == "once"){
											wp_schedule_single_event($timestamp, "aDBc_optimize_scheduler", array($_POST['aDBc_schedule_name']));
										}else{
											wp_schedule_event($timestamp, sanitize_html_class($_POST['aDBc_schedule_repeat']), "aDBc_optimize_scheduler", array($_POST['aDBc_schedule_name']));
										}
										$this->aDBc_message = __('The clean-up schedule saved successfully!', 'advanced-database-cleaner');
									}else{
										$this->aDBc_message = __('The clean-up schedule saved successfully but it is inactive!', 'advanced-database-cleaner');
									}
								}else{
									$this->aDBc_class_message = "error";
									$this->aDBc_message = __('Please choose at least one operation to perform!', 'advanced-database-cleaner');
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
		}
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
					<a href="?page=advanced_db_cleaner&aDBc_tab=tables&aDBc_cat=all">
						<img width="40px" src="<?php echo ADBC_PLUGIN_DIR_PATH . '/images/go_back.svg'?>"/>
					</a>
				</div>
				<div class="aDBc-float-right" style="border:1px solid #f0f0f0;box-shadow:0 0 10px #eee;border-radius:5px;text-align:center;width:190px;background:#fff;padding:10px;font-size:16px;margin-top: 15px;margin-bottom: 25px;color: #0992CC;">
					<img style="margin-right:10px" width="15px" src="<?php echo ADBC_PLUGIN_DIR_PATH . '/images/edit_schedule.svg'?>"/> 
					<?php echo __('Edit cleanup schedule','advanced-database-cleaner') ; ?>
				</div>
			</div>
			
			<div class="aDBc-clear-both"></div>
	<form id="aDBc_form" action="" method="post">
			<div style="float:left;width:400px;margin-right: 25px;background:#f0f5fa;margin-top:49px;padding-top:50px;height:100px;text-align:center;border-radius:4px;border:1px solid #eee">
				<?php echo __('By default, all your database tables will be optimized (if needed) according to your schedule settings','advanced-database-cleaner') ; ?>
			</div>

			<?php
			// Prepare info of the original schedule to fill it into inputs...

			if(isset($_POST['aDBc_schedule_name'])){

				$hook_name 			= sanitize_html_class($_POST['aDBc_schedule_name']);
				$schedule_repeat 	= sanitize_html_class($_POST['aDBc_schedule_repeat']);
				$schedule_date 		= preg_replace("/[^0-9-]/", '', $_POST['aDBc_date']);
				$schedule_time		= preg_replace("/[^0-9:]/", '', $_POST['aDBc_time']);
				$operation1 		= isset($_POST['aDBc_operation1']) ? sanitize_html_class($_POST['aDBc_operation1']) : "";
				$operation2 		= isset($_POST['aDBc_operation2']) ? sanitize_html_class($_POST['aDBc_operation2']) : "";
				$schedule_status	= sanitize_html_class($_POST['aDBc_status']);

			}else{

				$schedule_settings 	= get_option('aDBc_optimize_schedule');
				$schedule_params 	= $schedule_settings[sanitize_html_class($_GET['hook_name'])];

				$hook_name 			= sanitize_html_class($_GET['hook_name']);
				$schedule_repeat 	= $schedule_params['repeat'];
				$timestamp 			= wp_next_scheduled("aDBc_optimize_scheduler", array(sanitize_html_class($_GET['hook_name']) . ''));
				if($timestamp){
					$schedule_date 	= date("Y-m-d", $timestamp);
					$schedule_time 	= date("H:i", $timestamp);
				}else{
					$schedule_date 	= date("Y-m-d");
					$schedule_time 	= date("H:i", time());
				}

				$operation1 = in_array('optimize', $schedule_params['operations']) ? 'optimize' : '';
				$operation2 = in_array('repair', $schedule_params['operations']) ? 'repair' : '';

				$schedule_status	= $schedule_params['active'];
			}
			
			?>
			
			<div class="aDBc-right-box">

				<div style="text-align:center">

					<img width="60px" src="<?php echo ADBC_PLUGIN_DIR_PATH . '/images/alarm-clock.svg'?>"/>
					<br/><br/>

						<div id="add_schedule" style="border-top:1px dashed #ccc">
							<br/>
							<div style="text-align:left"><?php _e('Schedule name','advanced-database-cleaner');?></div>
							<input style="width:100%;margin-bottom:10px;height:30px;border-radius:5px;box-shadow:0 0 10px #e0e0e0;background-color: #ccc !important;color: #000 !important;" type="text" name="aDBc_schedule_name_x" placeholder="Schedule name" value="<?php echo $hook_name; ?>" maxlength="25" disabled>
							<input type="hidden" name="aDBc_schedule_name" value="<?php echo $hook_name; ?>" maxlength="25">
							
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
									if($code_repeat == $schedule_repeat){
										echo "<option value='$code_repeat' selected='selected'>$name_repeat</option>";
									}else{
										echo "<option value='$code_repeat'>$name_repeat</option>";
									}
								}
							?>							
							</select>

							<div style="text-align:left"><?php _e('Start date','advanced-database-cleaner');?></div>
							<input style="width:100%;margin-bottom:10px;height:30px;border-radius:5px;box-shadow:0 0 10px #e0e0e0" type="date" name="aDBc_date" placeholder="" value="<?php echo $schedule_date; ?>" min="<?php echo date("Y-m-d"); ?>">

							<div style="text-align:left"><?php _e('Start time (GMT)','advanced-database-cleaner');?></div>
							<input style="width:100%;margin-bottom:10px;height:30px;border-radius:5px;box-shadow:0 0 10px #e0e0e0" type="time" name="aDBc_time" value="<?php echo $schedule_time; ?>">

							<div style="text-align:left"><?php _e('Perform operations','advanced-database-cleaner');?></div>
							<div style="margin-bottom:10px;margin-top:2px;text-align:left;background:#fff;padding:5px;box-shadow:0 0 10px #e0e0e0;border-radius:5px">
								<input type="checkbox" name="aDBc_operation1" value="optimize" <?php echo $operation1 == "optimize" ? 'checked' : ''; ?>>
								<span style="margin-right:20px"><?php _e('Optimize','advanced-database-cleaner');?></span>
								
								<input type="checkbox" name="aDBc_operation2" value="repair" <?php echo $operation2 == "repair" ? 'checked' : ''; ?>>
								<?php _e('Repair','advanced-database-cleaner');?>							
							</div>

							<div style="text-align:left"><?php _e('Schedule status','advanced-database-cleaner');?></div>

							<div style="margin-top:2px;text-align:left;background:#fff;padding:5px;box-shadow:0 0 10px #e0e0e0;border-radius:5px">
									<input type="radio" name="aDBc_status" value="1" checked> 
									<span style="margin-right:20px"><?php _e('Active','advanced-database-cleaner');?></span>
									
									<input type="radio" name="aDBc_status" value="0" <?php echo $schedule_status == "0" ? 'checked' : ''; ?>>
									<?php _e('Inactive','advanced-database-cleaner');?>
							</div>

							<div style="width:100%;margin-top:20px">
								<input class="button-primary" type="submit"  value="<?php _e('Save the schedule','advanced-database-cleaner'); ?>" style="width:100%;"/>
							</div>

						</div>
				</div>
			</div>

			<?php wp_nonce_field('edit_optimize_schedule_nonce', 'edit_optimize_schedule_nonce'); ?>

	</form>
			<div class="aDBc-clear-both"></div>
		</div>

	<?php
	}
}

?>