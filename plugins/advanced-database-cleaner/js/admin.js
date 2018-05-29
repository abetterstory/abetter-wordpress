jQuery(document).ready(function(){

	// hide the loading gif and message loading for options ...
	jQuery('#aDBc_new_search_button').click(function(){
		jQuery("#aDBc_new_search_button").hide();
		jQuery('#aDBc-please-wait').css("display", "block");
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
	var $select_action = $("#aDBc_dialog2");
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
		}else if($bulk_action == '-1'){
			event.preventDefault();
			$select_action.dialog('open');
		}
	});

});
