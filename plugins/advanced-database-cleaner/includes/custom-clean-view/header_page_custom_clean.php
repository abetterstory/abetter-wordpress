<div class="aDBc-float-left aDBc-margin-t-10">
	<a href="?page=advanced_db_cleaner&aDBc_tab=general">
		<img width="40px" src="<?php echo ADBC_PLUGIN_DIR_PATH . '/images/go_back.svg'?>"/>
	</a>
</div>

<div>
	<div class="aDBc-float-right aDBc-custom-clean-text">
		<div>
		<?php echo __('Custom cleaning of','advanced-database-cleaner') . " : <strong>" . $this->aDBc_plural_title . "</strong> - " . __('Total Found','advanced-database-cleaner') . " : <b><span style='background:#ffe4b5;border-radius:8px;padding:2px 6px'>" . count($this->aDBc_elements_to_display) . "</span></b>"; ?>
		</div>		

	</div>

	<div style="clear:both;margin-bottom:25px;width:100%;background:#f9f9f9" class="aDBc-float-right">

		<div style="float:left;padding:8px;height:30px">

			<span class="aDBc_premium_tooltip">

				<form style="float:left;pointer-events:none;opacity:0.5" method="get">

					<?php 
					// Generate current parameters in URL
					foreach($_GET as $name => $value){
						if($name != "s" && $name != "in" && $name != "paged")
							echo "<input type='hidden' name='$name' value='$value'/>";
					}
					// Return paged to page 1
					echo "<input type='hidden' name='paged' value='1'/>";				
					?>

					<fieldset style="padding-right:5px;float:left">
						<input style="font-size:13px;width:140px;height:30px;border:1px solid #e5e5e5;border-radius:2px;box-shadow:0 0 10px #f1f1f1;" type="search" placeholder="<?php _e('Search for','advanced-database-cleaner') ?>" name="s" value="<?php echo empty($_GET['s']) ? '' : esc_attr($_GET['s']); ?>"/>
					</fieldset>

					<fieldset style="border:1px solid #f1f1f1;border-radius:2px;box-shadow:0 0 10px #f1f1f1;padding:6px;float:left">
						<span style="padding:2px 10px 2px 5px;"><?php _e('Search in','advanced-database-cleaner') ?></span>
						<input type="radio" name="in" value="key" checked <?php echo (empty($_GET['in']) || (!empty($_GET['in']) && $_GET['in'] == "key")) ? 'checked' : ''; ?>><?php _e('Name','advanced-database-cleaner') ?> &nbsp; 
						<input type="radio" name="in" value="value" <?php echo (!empty($_GET['in']) && $_GET['in'] == "value") ? 'checked' : ''; ?>><?php _e('Value','advanced-database-cleaner') ?>
					</fieldset>
					<span style="padding-left:5px;float:left">
						<input style="float:left;height:30px;margin-top:0px" type="submit" class="button-secondary" value="<?php _e('Filter','advanced-database-cleaner') ?>"/>
					</span>

				</form>

				<span style="width:150px" class="aDBc_premium_tooltiptext"><?php _e('Available in Pro version!','advanced-database-cleaner') ?></span>

			</span>
		</div>

		<div style="float:right;padding:8px;height:30px;margin-left:20px">

			<form style="float:left" method="get">

				<?php 
				// Generate current parameters in URL
				foreach($_GET as $name => $value){
					if($name != "per_page" && $name != "paged")
						echo "<input type='hidden' name='$name' value='$value'/>";
				}
				// Return paged to page 1
				echo "<input type='hidden' name='paged' value='1'/>";
				?>

					<span style="padding-right:8px;float:left;font-size:13px;padding-top:6px"><?php _e('Items per page','advanced-database-cleaner') ?></span>
					<span style="padding-right:5px;float:left">

						<input type="number" style="font-size:13px;width:55px;height:30px;border:1px solid #e5e5e5;border-radius:2px;box-shadow:0 0 10px #f1f1f1;" id="revisions-search-input" name="per_page" value="<?php echo empty($_GET['per_page']) ? '50' : esc_attr($_GET['per_page']); ?>"/>
					</span>

					<span style="float:left">
						<input style="float:left;height:30px;" type="submit" class="button-secondary" value="<?php _e('Show','advanced-database-cleaner') ?>"/>
					</span>
			</form>
		</div>
	</div>
</div>