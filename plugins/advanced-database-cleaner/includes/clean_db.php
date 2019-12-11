<?php

if(isset($_GET['aDBc_view'])){

	// If the user wants to perform custom cleaning

	if($_GET['aDBc_view'] == "revision" || 
	   $_GET['aDBc_view'] == "auto-draft" || 
	   $_GET['aDBc_view'] == "trash-posts"){

		include_once 'custom-clean-view/class_clean_revision_draft_trash.php';
		new ADBC_Clean_Revision_Trash_Draft($_GET['aDBc_view']);

	}else if($_GET['aDBc_view'] == "moderated-comments" || 
			 $_GET['aDBc_view'] == "spam-comments" || 
			 $_GET['aDBc_view'] == "trash-comments" || 
			 $_GET['aDBc_view'] == "pingbacks" || 
			 $_GET['aDBc_view'] == "trackbacks"){

		include_once 'custom-clean-view/class_clean_comment.php';
		new ADBC_Clean_Comment($_GET['aDBc_view']);

	}else if($_GET['aDBc_view'] == "orphan-postmeta" || 
			 $_GET['aDBc_view'] == "orphan-commentmeta" || 
			 $_GET['aDBc_view'] == "orphan-usermeta" || 
			 $_GET['aDBc_view'] == "orphan-termmeta"){

		include_once 'custom-clean-view/class_clean_meta_comment_post_user_term.php';
		new ADBC_Clean_Meta_Comment_Post_User_Term($_GET['aDBc_view']);

	}else if($_GET['aDBc_view'] == "orphan-relationships"){

		include_once 'custom-clean-view/class_clean_relationships.php';
		new ADBC_Clean_Relationship();

	}else if($_GET['aDBc_view'] == "expired-transients" || 
			 $_GET['aDBc_view'] == "transients-with-expiration" || 
			 $_GET['aDBc_view'] == "transients-with-no-expiration"){

		include_once 'custom-clean-view/class_clean_transient.php';
		new ADBC_Clean_Transient($_GET['aDBc_view']);

	}else if($_GET['aDBc_view'] == "add_cleanup_schedule"){

		include_once 'custom-schedule-view/class_add_cleanup_schedule.php';
		new ADBC_SCHEDULE_CLEANUP($_GET['aDBc_view']);

	}else if($_GET['aDBc_view'] == "edit_cleanup_schedule"){

		include_once 'custom-schedule-view/class_edit_cleanup_schedule.php';
		new EDIT_SCHEDULE_CLEANUP($_GET['aDBc_view']);

	}

}else{
	// Else return the general clean-up page
	include_once 'class_general_cleanup.php';
}

?>