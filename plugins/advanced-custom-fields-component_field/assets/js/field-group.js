(function($, undefined) {

	var inArray = function(niddle, haystack) {
		return jQuery.inArray(niddle, haystack) >= 0;
	};

	var hasChanged = function(field) {
		function _changed(field) {
			return field.get('save') == 'settings';
		}

		var changed = _changed(field);

		acf.getFieldObjects({parent: field.$el})
			.map(function(field) {
				changed = _changed(field) || field.changed;
			});

		return changed;
	};

	var getFieldElement = function(object) {
		return object.$el? object.$el : (object.data('type')? object : object.closest('.acf-field-object'));
	}

	var canBeConverted = function(object) {
		return inArray(getFieldElement(object).data('type'), ['repeater', 'component_field']);
	}

	var addConvertButton = function($el) {
		if ($el.find('.row-options .convert-field').length > 0) return;

		jQuery('<a class="convert-field" title="' + acf.__('convert_title') + '" href="#" style="margin-left: 3px;">' + acf.__('convert_text') + '</a>')
			.insertAfter($el.find('.row-options .move-field'))
	};

	var removeConvertButton = function($el) {
		if ($el.find('.row-options .convert-field').length == 0) return;

		$el.find('.row-options .convert-field').remove();
	};

	var componentFieldGroupSelectAppended = function(field) {
		disableCurrentGroupFromComponentSelect(field);

		field.set('field_group_key', field.val());
		field.$el.find('select').on('change', function() {
			if (field.changeTimeout) {
				clearTimeout(field.changeTimeout);
			}

			var timeout = setTimeout(function() {
				loadComponentSettings(field);
			}, 300);

			field.changeTimeout = timeout;
		});
	};

	var disableCurrentGroupFromComponentSelect = function(field) {
		var fieldObjectKey = field.$el.closest('.acf-field-object').attr('data-key');

		field.$el.find('option[value="' + fieldObjectKey + '"]').prop('disabled', true);
	};

	var loadComponentSettings = function(field) {
		var fieldObjectKey = field.$el.closest('.acf-field-object').attr('data-key');
		var $fieldObject = acf.getFieldObject(fieldObjectKey);
		var groupKey = field.val();

		var fieldType = $fieldObject.prop('type');
		var $settingFields = field.$el.siblings('[data-setting="' + fieldType + '"]');
		field.set('component_group-' + field.get('field_group_key'), $settingFields);
		field.set('field_group_key', groupKey);
		$settingFields.detach();

		if (field.has('xhr')) {
			field.get('xhr').abort();
		}

		// show settings
		if (field.has('component_group-' + groupKey) ) {
			var $newSettingFields = field.get('component_group-' + groupKey);
			$fieldObject.$setting('conditional_logic').before($newSettingFields);
			return;
		}

		var $loading = $('<tr class="acf-field"><td class="acf-label"></td><td class="acf-input"><div class="acf-loading"></div></td></tr>');
		$fieldObject.$setting('conditional_logic').before($loading);

		var ajaxData = {
			action          : 'acf/field_types/component_field/load_settings',
			field_group_key : groupKey,
			prefix          : $fieldObject.getInputName()
		};

		var xhr = $.ajax({
			url: acf.get('ajaxurl'),
			data: acf.prepareForAjax(ajaxData),
			type: 'post',
			dataType: 'html',
			success: function(html) {
				if (! html) return;

				$loading.after(html);
				acf.doAction('append', field.$el.parent('tbody'));
				// field.set('component_group-' + groupKey, html);
			},
			complete: function() {
				$loading.remove();
			}
		});

		field.set('xhr', xhr);
	};

	$(document).on('change.adjust_acf_metabox', '#is_acf_component_checkbox', function(e) {
		if ($(this).is(":checked")) {
			$("#acf-field-group-locations, #acf-field-group-options").hide();
			$("#acf-component-field-default-metabox").show();
		} else {
			$("#acf-field-group-locations, #acf-field-group-options").show();
			$("#acf-component-field-default-metabox").hide();
		}
	});

	acf.addAction('ready', function() {
		acf.getFieldObjects()
			.filter(canBeConverted)
			.map(function(object) {
				addConvertButton(getFieldElement(object));
			});

		$("[data-name='field_group_key']").each(function() {
			var field = acf.getField($(this));

			componentFieldGroupSelectAppended(field);
		});

		$('#is_acf_component_checkbox').trigger('change.adjust_acf_metabox');
	});

	acf.addAction('append_field/name=field_group_key', function(field) {
		componentFieldGroupSelectAppended(field);
	});

	acf.addAction('append', function($el) {
		var $fieldObject = getFieldElement($el);

		canBeConverted($fieldObject)? addConvertButton($fieldObject) : removeConvertButton($fieldObject);
	});

	acf.FieldObject.prototype.events['click .convert-field'] = 'onConvert';

	acf.FieldObject.prototype.onConvert = function() {
		var field = this;
		var popup = false;

		if (hasChanged(field)) {
			alert(acf.__('convert_warning'));
			return;
		}

		var checkSettings = function() {
			popup = acf.newPopup({
				title   : acf.__('convert_popup_title'),
				loading : true,
				width   : '500px'
			});

			var ajaxData = {
				action    : 'acf/component_field/features/convert_check',
				field_key : field.get('key')
			};

			$.ajax({
				url      : acf.get('ajaxurl'),
				data     : acf.prepareForAjax(ajaxData),
				type     : 'post',
				dataType : 'html',
				success  : convertSettings
			});
		};

		var convertSettings = function(html) {
			popup.loading(false);
			popup.content(html);

			acf.doAction('append', popup.$el);
			popup.on('submit', 'form', convertFields);
		};

		var convertFields = function(e) {
			e.preventDefault();

			acf.startButtonLoading( popup.$('.button') );

			var $convertTo = popup.$('input[name="convert-to"]');
			var deleteComponent = popup.$('input[name="delete-component"]:checked').length > 0;

			var ajaxData = {
				action           : 'acf/component_field/features/convert',
				field_key        : field.get('key'),
				convert_to       : $convertTo.val(),
				delete_component : deleteComponent,
				order            : field.get('menu_order')
			};

			if (deleteComponent) {
				if (! confirm(acf.__('convert_delete_confirm_message'))) {
					acf.stopButtonLoading(popup.$('.button'));
					return false;
				}
			}

			$.ajax({
				url      : acf.get('ajaxurl'),
				data     : acf.prepareForAjax(ajaxData),
				type     : 'post',
				dataType : 'json',
				success  : convertFinished
			});

			return false;
		};

		var convertFinished = function(json) {
			if (json.data.field) {
				var $html = $(json.data.field)
				field.$el.before($html);
				field.removeAnimate();

				var newField = acf.newFieldObject($html);
				acf.doAction('append', newField.$el);
			}

			popup.content(json.data.popup);
		};

		checkSettings();
	}
})(jQuery);
