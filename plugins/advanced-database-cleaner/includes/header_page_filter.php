
<div style="margin-top:30px;">

	<div style="clear:both;margin-bottom:25px;width:100%;background:#f9f9f9" class="aDBc-float-right">

		<?php 
		if(ADBC_PLUGIN_F_TYPE == "free"){
			$aDBc_form_style = "pointer-events:none;opacity:0.5";
			
		}else{
			$aDBc_form_style = "";
		}
		?>

		<div style="float:left;padding:8px;height:30px">

			<span class="aDBc_premium_tooltip">

				<form method="get" style="<?php echo $aDBc_form_style ?>">

					<?php 
					// Generate current parameters in URL
					foreach($_GET as $name => $value){
						if($name != "s" && $name != "paged" && $name != "aDBc_cat")
							echo "<input type='hidden' name='$name' value='$value'/>";
					}
					// Return always paged to page 1
					echo "<input type='hidden' name='paged' value='1'/>";
					// Return always aDBc_cat to all after filter
					echo "<input type='hidden' name='aDBc_cat' value='all'/>";				
					?>

					<fieldset style="padding-right:5px;float:left">
						<input style="font-size:13px;width:120px;height:30px;border:1px solid #e5e5e5;border-radius:2px;box-shadow:0 0 10px #f1f1f1;" type="search" placeholder="<?php _e('Search for','advanced-database-cleaner') ?>" name="s" value="<?php echo empty($_GET['s']) ? '' : esc_attr($_GET['s']); ?>"/>
					</fieldset>

					<fieldset style="float:left;">

					
						<?php 
						// Show this select only for tables
						if(isset($_GET['aDBc_tab']) && $_GET['aDBc_tab'] == 'tables'){ ?>
						<select name="t_type" style="font-size:13px;width:100px;height:30px;border:1px solid #e5e5e5;border-radius:2px;box-shadow:0 0 10px #f1f1f1;">
							<option value="all" <?php echo (isset($_GET['t_type']) && $_GET['t_type'] == 'all') ? "selected='selected'" : ""; ?>><?php _e('All tables','advanced-database-cleaner') ?></option>
							<option value="optimize" <?php echo (isset($_GET['t_type']) && $_GET['t_type'] == 'optimize') ? "selected='selected'" : ""; ?>><?php echo __('To optimize','advanced-database-cleaner') . " (" . count($this->aDBc_tables_name_to_optimize) . ")" ?></option>
							<option value="repair" <?php echo (isset($_GET['t_type']) && $_GET['t_type'] == 'repair') ? "selected='selected'" : ""; ?>><?php echo __('To repair','advanced-database-cleaner') . " (" . count($this->aDBc_tables_name_to_repair) . ")" ?></option>
						</select>
						<?php } ?>
						
						<?php 
						// Show autoload only for options
						if(isset($_GET['aDBc_tab']) && $_GET['aDBc_tab'] == 'options'){ ?>
						<select name="autoload" style="font-size:13px;width:100px;height:30px;border:1px solid #e5e5e5;border-radius:2px;box-shadow:0 0 10px #f1f1f1;">
							<option value="all" <?php echo (isset($_GET['autoload']) && $_GET['autoload'] == 'all') ? "selected='selected'" : ""; ?>><?php _e('Autoload','advanced-database-cleaner') ?></option>
							<option value="yes" <?php echo (isset($_GET['autoload']) && $_GET['autoload'] == 'yes') ? "selected='selected'" : ""; ?>><?php echo __('Yes','advanced-database-cleaner') ?></option>
							<option value="no" <?php echo (isset($_GET['autoload']) && $_GET['autoload'] == 'no') ? "selected='selected'" : ""; ?>><?php echo __('No','advanced-database-cleaner') ?></option>
						</select>
						<?php } ?>

						<select name="belongs_to" style="font-size:13px;width:135px;height:30px;border:1px solid #e5e5e5;border-radius:2px;box-shadow:0 0 10px #f1f1f1;">
							<option value="all"><?php _e('All "belongs to"','advanced-database-cleaner') ?></option>
							<?php 
							$total_plugins = 0;
							$total_themes = 0;
							foreach($this->array_belongs_to_counts as $name => $info){
								if($info['type'] == "p"){
									$total_plugins++;
								}else if($info['type'] == "t"){
									$total_themes++;
								}
							}
							?>
							<optgroup label="<?php echo __('Plugins','advanced-database-cleaner') . " (" . $total_plugins . ")"  ?>">
								<?php 

									foreach($this->array_belongs_to_counts as $name => $info){
										if($info['type'] == "p"){
											$selected = isset($_GET['belongs_to']) && $_GET['belongs_to'] == $name ? "selected='selected'" : "";
											echo "<option value='$name'" . $selected . ">" . $name . " (" . $info['count'] .")" . "</option>";

										}
									}
								?>
							</optgroup>
							<optgroup label="<?php echo __('Themes','advanced-database-cleaner') . " (" . $total_themes . ")"  ?>">
								<?php 
									foreach($this->array_belongs_to_counts as $name => $info){
										if($info['type'] == "t"){
											$selected = isset($_GET['belongs_to']) && $_GET['belongs_to'] == $name ? "selected='selected'" : "";
											echo "<option value='$name'" . $selected . ">" . $name . " (" . $info['count'] .")" . "</option>";
										}
									}
								?>
							</optgroup>				
						</select>

						<?php
						if(function_exists('is_multisite') && is_multisite()){
							echo "<select name='site' style='font-size:13px;width:75px;height:30px;border:1px solid #e5e5e5;border-radius:2px;box-shadow:0 0 10px #f1f1f1;'>";
							echo "<option value=''>" . __('All sites','advanced-database-cleaner') . "</option>";

							global $wpdb;
							$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
							foreach($blogs_ids as $blog_id){
								$blog_details = get_blog_details($blog_id);
								$selected = (isset($_GET['site']) && $_GET['site'] == $blog_id) ? "selected='selected'" : "";
								echo "<option value='$blog_id'". $selected .">" . __('Site','advanced-database-cleaner') . " ". $blog_id . " | " . $blog_details->blogname . "</option>";
							}

							echo "</select>";

						}
						?>

					</fieldset>

					<span style="padding-left:5px;float:left">
						<input style="float:left;height:30px;width:50px;margin-top:0px" class="button-secondary" type="submit" value="<?php _e('Filter','advanced-database-cleaner') ?>"/>
					</span>

				</form>

				<?php if(ADBC_PLUGIN_F_TYPE == "free"){ ?>
					<span style="width:150px" class="aDBc_premium_tooltiptext"><?php _e('Available in Pro version!','advanced-database-cleaner') ?></span>
				<?php } ?>

			</span>
		</div>

		<div style="float:right;padding:8px;height:30px">

			<form method="get">

				<?php 
				// Generate current parameters in URL
				foreach($_GET as $name => $value){
					if($name != "per_page" && $name != "paged")
						echo "<input type='hidden' name='$name' value='$value'/>";
				}
				// Return paged to page 1
				echo "<input type='hidden' name='paged' value='1'/>";
				?>

					<span style="padding-right:8px;float:left;font-size:13px;padding-top:5px"><?php _e('Items per page','advanced-database-cleaner') ?></span>
					<span style="padding-right:5px;float:left">

						<input type="number" style="font-size:13px;width:55px;height:30px;border:1px solid #e5e5e5;border-radius:2px;box-shadow:0 0 10px #f1f1f1;" id="revisions-search-input" name="per_page" value="<?php echo empty($_GET['per_page']) ? '50' : esc_attr($_GET['per_page']); ?>"/>
					</span>

					<span style="float:left">
						<input style="float:left;height:30px;" type="submit" class="button-secondary" value="<?php _e('Show','advanced-database-cleaner') ?>"/>
					</span>
			</form>
		</div>

		<?php

		if((!empty($_GET['s']) && trim($_GET['s']) != "") || !empty($_GET['t_type']) || !empty($_GET['belongs_to']) || !empty($_GET['site'])){

			$aDBc_new_URI = $_SERVER['REQUEST_URI'];
			// Remove args to delete custom filter
			$aDBc_new_URI = remove_query_arg(array('s', 't_type', 'belongs_to', 'site', 'autoload'), $aDBc_new_URI);
			$aDBc_new_URI = add_query_arg('aDBc_cat', 'all', $aDBc_new_URI);
			?>

			<div style="clear:both;padding:5px 0px 8px 8px;">
				<a style="color:red" href="<?php echo $aDBc_new_URI; ?>"><?php _e('Delete custom filter','advanced-database-cleaner') ?></a>
			</div>

		<?php } ?>

	</div>
</div>