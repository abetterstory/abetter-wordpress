jQuery(document).ready(function ($) {
	"use strict";

	var dialog = $('.js-wpml-translation-basket-dialog');

	var openDialog = function(result) {

		dialog.dialog({
			dialogClass: 'wpml-dialog otgs-ui-dialog',
			width: 600,
			title: dialog.data('title'),
			modal: true,
			closeOnEscape: false,
			resizable: false,
			draggable: false,
			buttons: [
				{
					text: dialog.data('button-done'),
					class: 'button-primary',
					click: function () {
						location.href = dialog.data('redirect-url');
					}
				}
			],
			open: function () {
				dialog.find('.js-dialog-content')
					.append('<p>' + result.call_to_action + '</p><p>' + result.ts_batch_link + '</p>');
				dialog.show();
				repositionDialog();
			}
		});
	};

	var repositionDialog = function() {
		var winH = $(window).height() - 180;
		$(".otgs-ui-dialog .ui-dialog-content").css({
			"max-height": winH
		});
		$(".otgs-ui-dialog").css({
			"max-width": "95%"
		});
		dialog.dialog("option", "position", {
			my: "center",
			at: "center",
			of: window
		});
	};

	$(window).resize(repositionDialog);

	var form = $('#translation-jobs-translators-form');

	form.on('wpml-tm-basket-submitted', function(event, response) {
		if(response.result && typeof response.result.call_to_action !== 'undefined') {
			openDialog(response.result);
		}
	});
});
