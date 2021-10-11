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
