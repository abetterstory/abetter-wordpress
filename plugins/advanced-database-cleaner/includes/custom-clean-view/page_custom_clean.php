<?php

// Print a message if any
if($this->aDBc_message != ""){
	echo '<div id="aDBc_message" class="' . $this->aDBc_class_message . ' notice is-dismissible"><p>' . $this->aDBc_message . '</p></div>';
}

?>

<div class="aDBc-content-max-width">

	<?php include_once 'header_page_custom_clean.php'; ?>

	<div>
		<form id="aDBc_form" action="" method="post">
			<?php
			// Print the elements to clean
			$this->display();
			?>
		</form>
	</div>
</div>
<div id="aDBc_dialog1" title="<?php _e("Cleaning '$this->aDBc_plural_title'","advanced-database-cleaner"); ?>" class="aDBc-jquery-dialog">
	<p class="aDBc-box-warning">
		<?php echo __("You are about to clean some of your $this->aDBc_plural_title. This operation is irreversible!","advanced-database-cleaner")  . "<span style='color:red'> " . __('Don\'t forget to make a backup of your database first.','advanced-database-cleaner') . "</span>" ; ?>
	</p>
	<p>
		<?php _e('Are you sure to continue?','advanced-database-cleaner'); ?>
	</p>
</div>		
<div id="aDBc_dialogx" title="<?php _e('Action required','advanced-database-cleaner'); ?>" class="aDBc-jquery-dialog">
	<p class="aDBc-box-info">
		<?php _e('Please select an action!','advanced-database-cleaner'); ?>
	</p>
</div>