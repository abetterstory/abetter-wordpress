jQuery(document).ready(function(){

	// Prevent submitting forms via Enter key to prevent any unexpected form submission
	jQuery(window).keydown(function(event){
		if(event.keyCode == 13) {
			event.preventDefault();
			return false;
		}
	});

	// x10: After upgrading to WP 5.5, the button of #doaction2 at the button does not work anymore
	// This is because when submitting the form below via 'jQuery("#aDBc_form").submit()', the form is sent without the action selected at bottom
	// We make sure that both dropdowns have the same values so that the form is sent without issues
	jQuery('#bulk-action-selector-bottom').on("change", function(e) {
		var abdc_action = jQuery('#bulk-action-selector-bottom').val();
		jQuery('#bulk-action-selector-top').val(abdc_action);
	});
	jQuery('#bulk-action-selector-top').on("change", function(e) {
		var abdc_action = jQuery('#bulk-action-selector-top').val();
		jQuery('#bulk-action-selector-bottom').val(abdc_action);
	});

	// When a user clicks on doaction or doaction2 button
	jQuery('#doaction, #doaction2').on('click', function(e){

		// Get action from the clicked button
		if(this.id == 'doaction'){
		  var aDBc_action = jQuery("#bulk-action-selector-top").val();
		}else if(this.id == 'doaction2'){
		  var aDBc_action = jQuery('#bulk-action-selector-bottom').val();
		}

		// Get values of top_action and bottom action
		var abdc_top_action 	= jQuery('#bulk-action-selector-top').val();
		var abdc_bottom_action 	= jQuery('#bulk-action-selector-bottom').val();

		// Before performing any action, test first if #bulk-action-selector-top and #bulk-action-selector-bottom have the same value as in x10 above
		if(abdc_top_action != abdc_bottom_action){

			// Prevent doaction button from its default behaviour
			e.preventDefault();

			// If values are different, show an error msg
			Swal.fire({
			  icon					: 'error',
			  confirmButtonColor	: '#0085ba',
			  showCloseButton		: true,
			  text					: aDBc_ajax_obj.unexpected_error
			})

		// If no action selected
		}else if(aDBc_action == "-1"){

			// Prevent doaction button from its default behaviour
			e.preventDefault();

			// If no actions selected, show an error message
			Swal.fire({
			  icon					: 'error',
			  confirmButtonColor	: '#0085ba',
			  showCloseButton		: true,
			  text					: aDBc_ajax_obj.select_action
			})

		}else{

			// Test if the user has checked some items
			var aDBc_elements_to_process = [];

			// Get all selected items
			jQuery('input[name="aDBc_elements_to_process[]"]:checked').each(function(){aDBc_elements_to_process.push(this.value);});

			// If no items selected, show error message
			if(aDBc_elements_to_process.length === 0) {

				// Prevent doaction button from its default behaviour
				e.preventDefault();

				Swal.fire({
				  icon					: 'error',
				  confirmButtonColor	: '#0085ba',
				  showCloseButton		: true,
				  text					: aDBc_ajax_obj.no_items_selected
				})

			}else{

				// The default warning msg to show is
				var message_to_show = aDBc_ajax_obj.clean_items_warning;

				// If 'empty' action is selected for tables, override the warning msg
				if(aDBc_action == 'empty'){
					var message_to_show = aDBc_ajax_obj.empty_tables_warning;
				}

				// We show the warning box msg only when actions such as: delete, clean, empty... are selected
				if(aDBc_action == 'delete' || aDBc_action == 'clean' || aDBc_action == 'empty'){

					// Prevent doaction button from its default behaviour
					e.preventDefault();

					Swal.fire({
						title				: '<font size="4px">' + aDBc_ajax_obj.are_you_sure + '</font>',
						text				: message_to_show,
						footer				: '<font size="3px" color="red"><b>' + aDBc_ajax_obj.make_db_backup_first + '</b></font>',
						imageUrl			: aDBc_ajax_obj.images_path + 'alert_delete.svg',
						imageWidth			: 60,
						imageHeight			: 60,
						showCancelButton	: true,
						showCloseButton		: true,
						cancelButtonText	: aDBc_ajax_obj.cancel,
						cancelButtonColor	: '#555',
						confirmButtonText	: aDBc_ajax_obj.Continue,
						confirmButtonColor	: '#0085ba',
						focusCancel 		: true,
					}).then((result) => {
						// If the user clicked on "confirm", submit the form
						if(result.value){
							jQuery("#aDBc_form").submit();
						}
					})
				}
			}
		}
	});

	// Actions to do when the user clicks on 'Edit' link to change the 'Keep last' value
	jQuery('.aDBc_keep_link').click(function(event){

		var idelement 	= (event.target.id).split("_");
		var itemname 	= idelement[idelement.length-1];

		jQuery("#aDBc_edit_keep_" 	+ itemname).hide();
		jQuery("#aDBc_keep_label_" 	+ itemname).hide();

		jQuery('#aDBc_keep_input_' 	+ itemname).show();
		jQuery('#aDBc_keep_button_' + itemname).show();
		jQuery('#aDBc_keep_cancel_' + itemname).show();

		jQuery('.aDBc_keep_link').css("pointer-events", "none");
		jQuery('.aDBc_keep_link').css("cursor", "default");
		jQuery('.aDBc_keep_link').css("color", "#eee");

	});

	jQuery('.aDBc_keep_cancel_link').click(function(event){

		var idelement 	= (event.target.id).split("_");
		var itemname 	= idelement[idelement.length-1];

		jQuery('#aDBc_keep_input_'	+ itemname).hide();
		jQuery('#aDBc_keep_button_'	+ itemname).hide();
		jQuery('#aDBc_keep_cancel_'	+ itemname).hide();

		jQuery("#aDBc_edit_keep_"	+ itemname).show();
		jQuery("#aDBc_keep_label_"	+ itemname).show();

		jQuery('.aDBc_keep_link').css("pointer-events", "");
		jQuery('.aDBc_keep_link').css("cursor", "pointer");
		jQuery('.aDBc_keep_link').css("color", "");

	});

});
