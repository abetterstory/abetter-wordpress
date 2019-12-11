jQuery(document).ready(function(){

	var aDBc_item_type = jQuery("#aDBc_item_type").attr('value');

	if(aDBc_item_type == "tables" || aDBc_item_type == "options" || aDBc_item_type == "tasks"){

		var still_searching = jQuery("#aDBc_still_searching").attr('value');
		var iteration = jQuery("#aDBc_iteration").attr('value');

		// When we load a page, test if ajax is still processing in background. If so, disable button
		if(still_searching == "yes"){
			jQuery('#aDBc_new_search_button').attr("value", aDBc_ajax_obj.sentence_scanning);
			jQuery('#aDBc_new_search_button').css('background-image', 'url(' + aDBc_ajax_obj.images_path + 'loading20px.svg)');
			jQuery('#aDBc_new_search_button').attr("disabled", true);
		}

		// After reload page, check if we should call ajax processing, if so, proceed even before clicking the button to continue after timeout
		if(still_searching == "" && iteration != ""){

			jQuery('#aDBc_new_search_button').attr("value", aDBc_ajax_obj.sentence_scanning);
			jQuery('#aDBc_new_search_button').css('background-image', 'url(' + aDBc_ajax_obj.images_path + 'loading20px.svg)');
			jQuery('#aDBc_new_search_button').attr("disabled", true);
			jQuery('#aDBc_progress_container').show();

			jQuery.ajax({
				type : "post",
				url: aDBc_ajax_obj.ajaxurl,
				cache: false,
				data: {
					'action': 'aDBc_new_run_search_for_items',
					'aDBc_item_type': aDBc_item_type
				},
				success: function(result) {

				},
				complete: function(){
					// wait for 1 sec then reload the page.
					setTimeout(function(){ 
					   location.reload();
					}, 1000);				
				}
			});
			setTimeout(getProgress, 500);
		}

	}

	jQuery('#aDBc_new_search_button').on('click', function(e){

		var me = jQuery(this);
        e.preventDefault();

		me.attr("value", aDBc_ajax_obj.sentence_scanning);
		me.css('background-image', 'url(' + aDBc_ajax_obj.images_path + 'loading20px.svg)');
		me.attr("disabled", true);
		jQuery('#aDBc-progress-bar').html("0%");
		jQuery('#aDBc_progress_container').show();

		var aDBc_item_type = jQuery("#aDBc_item_type").attr('value');

		jQuery.ajax({
			type : "post",
			url: aDBc_ajax_obj.ajaxurl,
			cache: false,
			data: {
				'action': 'aDBc_new_run_search_for_items',
				'aDBc_item_type': aDBc_item_type
			},
			success: function(result) {

				jQuery('#aDBc-progress-bar').html("100 %");
				jQuery('#aDBc-progress-bar').css("width", "100%");

			},
			complete: function(){
				// wait for 1 sec then reload the page.
				setTimeout(function(){ 
				   location.reload();
				}, 1000);				
				
			}
		});
		setTimeout(getProgress, 500);
		return false;
	});

	function getProgress(){

		var aDBc_item_type = jQuery("#aDBc_item_type").attr('value');

		jQuery.ajax({
			type : "post",
			url: aDBc_ajax_obj.ajaxurl,
			data: {
				'action': 'aDBc_get_progress_bar_width',
				'aDBc_item_type': aDBc_item_type
			},
			dataType : 'json',
			success: function(result) {
				var current = result['aDBc_progress'];
				var total 	= result['aDBc_total_items'];
				var stop 	= result['aDBc_stop'];
				if(stop == false){
					// xxx to delete console log
					// console.log(result);
					if(current > 0 && total > 0){
						jQuery('#aDBc-progress-bar').html(parseInt(current * (100/total)) + "%");
						jQuery('#aDBc-progress-bar').css("width", parseInt(current * (100/total)) + "%");
					}
					setTimeout(getProgress, 1000);
				}
			}
		});
	}

	jQuery('.aDBc_keep_link').click(function(event){

		var idelement = (event.target.id).split("_");
		var itemname = idelement[idelement.length-1];

		jQuery("#aDBc_edit_keep_"+itemname).hide();
		jQuery("#aDBc_keep_label_"+itemname).hide();

		jQuery('#aDBc_keep_input_'+itemname).show();
		jQuery('#aDBc_keep_button_'+itemname).show();
		jQuery('#aDBc_keep_cancel_'+itemname).show();

		jQuery('.aDBc_keep_link').css("pointer-events", "none");
		jQuery('.aDBc_keep_link').css("cursor", "default");
		jQuery('.aDBc_keep_link').css("color", "#eee");

	});

	jQuery('.aDBc_keep_cancel_link').click(function(event){

		var idelement = (event.target.id).split("_");
		var itemname = idelement[idelement.length-1];

		jQuery('#aDBc_keep_input_'+itemname).hide();
		jQuery('#aDBc_keep_button_'+itemname).hide();
		jQuery('#aDBc_keep_cancel_'+itemname).hide();

		jQuery("#aDBc_edit_keep_"+itemname).show();
		jQuery("#aDBc_keep_label_"+itemname).show();

		jQuery('.aDBc_keep_link').css("pointer-events", "");
		jQuery('.aDBc_keep_link').css("cursor", "pointer");
		jQuery('.aDBc_keep_link').css("color", "");

	});

});


jQuery(function($) {

	var $delete_warning = $("#aDBc_dialog1");
	$delete_warning.dialog({
		'dialogClass'   : 'wp-dialog',
		'modal'         : true,
		'width'			: 500,
		'autoOpen'      : false,
		'closeOnEscape' : true,
		'buttons'       : {
			"Close": function() {
				$(this).dialog('close');
			},
			"Continue": function() {
				$('form[id="aDBc_form"]').submit();
			}
		}
	});

	var $empty_warning = $("#aDBc_dialog2");
	$empty_warning.dialog({
		'dialogClass'   : 'wp-dialog',
		'modal'         : true,
		'width'			: 500,
		'autoOpen'      : false,
		'closeOnEscape' : true,
		'buttons'       : {
			"Close": function() {
				$(this).dialog('close');
			},
			"Continue": function() {
				$('form[id="aDBc_form"]').submit();
			}
		}
	});

	var $select_action = $("#aDBc_dialogx");
	$select_action.dialog({
		'dialogClass'   : 'wp-dialog',
		'modal'         : true,
		'width'			: 300,
		'autoOpen'      : false,
		'closeOnEscape' : true,
		'buttons'       : {
			"Close": function() {
				$(this).dialog('close');
			}
		}
	});

	$("#doaction").click(function(event) {
		var $bulk_action = $('#bulk-action-selector-top').attr('value');
		if($bulk_action == 'delete' || $bulk_action == 'clean'){
			event.preventDefault();
			$delete_warning.dialog('open');
		}else if($bulk_action == 'empty'){
			event.preventDefault();
			$empty_warning.dialog('open');
		}else if($bulk_action == '-1'){
			event.preventDefault();
			$select_action.dialog('open');
		}
	});

	$("#doaction2").click(function(event) {
		var $bulk_action = $('#bulk-action-selector-bottom').attr('value');
		if($bulk_action == 'delete' || $bulk_action == 'clean'){
			event.preventDefault();
			$delete_warning.dialog('open');
		}else if($bulk_action == 'empty'){
			event.preventDefault();
			$empty_warning.dialog('open');
		}else if($bulk_action == '-1'){
			event.preventDefault();
			$select_action.dialog('open');
		}
	});

});
