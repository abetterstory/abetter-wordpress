<?php

if(isset($_GET['aDBc_view'])){

	if($_GET['aDBc_view'] == "add_optimize_schedule"){

		include_once 'custom-schedule-view/class_add_optimize_schedule.php';
		new ADBC_SCHEDULE_OPTIMIZE();

	}else if($_GET['aDBc_view'] == "edit_optimize_schedule"){

		include_once 'custom-schedule-view/class_edit_optimize_schedule.php';
		new EDIT_SCHEDULE_OPTIMIZE();

	}

}else{
	// Else return the general clean-up page
	include_once 'class_clean_tables.php';
}

?>