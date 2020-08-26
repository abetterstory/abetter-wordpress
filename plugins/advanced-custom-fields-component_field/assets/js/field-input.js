(function($, undefined) {
	var Field = acf.models.RepeaterField.extend({
		type: 'component_field',
		// events: $.extend({}, acf.models.RepeaterField.prototype.events, {
		// 	'mouseenter a[data-event="add-row"]': 'asdf'
		// }),
		// asdf: function() {

		// }
	});

	// console.log(acf.models.RepeaterField.prototype.events)

	acf.registerFieldType(Field);
})(jQuery);
