<?php

// If the user wants to perform custom cleaning
if(isset($_GET['aDBc_view'])){
	if($_GET['aDBc_view'] == "revision"){
		include_once 'custom-clean-view/class_clean_revision.php';
	}else if($_GET['aDBc_view'] == "draft" || 
			 $_GET['aDBc_view'] == "auto-draft" || 
			 $_GET['aDBc_view'] == "trash-posts"){
		include_once 'custom-clean-view/class_clean_draft_trash.php';
		new ADBC_Clean_Draft($_GET['aDBc_view']);
	}else if($_GET['aDBc_view'] == "moderated-comments" || 
			 $_GET['aDBc_view'] == "spam-comments" || 
			 $_GET['aDBc_view'] == "trash-comments"){
		include_once 'custom-clean-view/class_clean_comment.php';
		new ADBC_Clean_Comment($_GET['aDBc_view']);
	}else if($_GET['aDBc_view'] == "orphan-postmeta"){
		include_once 'custom-clean-view/class_clean_postmeta.php';
	}else if($_GET['aDBc_view'] == "orphan-commentmeta"){
		include_once 'custom-clean-view/class_clean_commentmeta.php';
	}else if($_GET['aDBc_view'] == "orphan-relationships"){
		include_once 'custom-clean-view/class_clean_relationships.php';
	}else if($_GET['aDBc_view'] == "dashboard-transient-feed"){
		include_once 'custom-clean-view/class_clean_dashboard_transient_feed.php';
	}
}else{
	// Else return the general clean-up page
	include_once 'class_general_cleanup.php';
}

?>