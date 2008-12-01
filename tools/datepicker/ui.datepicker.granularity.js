jQuery(function($){

/* We extend the datepicker */
$.extend($.datepicker, {

	_fillDateInputFields: function (inst) {
		/*$(inst._settings['dateInputDay']).val((inst._granularity == 'd' ? inst._currentDay : ''));
		$(inst._settings['dateInputMonth']).val((inst._granularity != 'y' ? inst._currentMonth : ''));
		$(inst._settings['dateInputYear']).val(inst._currentYear);*/
		$(inst._settings['dateInputDay']).val(inst._currentDay);
		$(inst._settings['dateInputMonth']).val(inst._currentMonth);
		$(inst._settings['dateInputYear']).val(inst._currentYear);
	},

	_getDefaultDateFormat: function(inst) {
		if (inst._defaultDateFormat == undefined || inst._defaultDateFormat == null) {
			inst._defaultDateFormat = inst._get('dateFormat');
		}
		return (inst._defaultDateFormat);
	},
	
	_getDefaultDateFormatSeparator: function(inst) {
		if (inst._defaultDateFormatSeparator == undefined || inst._defaultDateFormatSeparator == null) {
			var pattern = /[^\w]/;
			var sepPos = this._getDefaultDateFormat(inst).search(pattern);
			inst._defaultDateFormatSeparator = inst._defaultDateFormat.charAt(sepPos);
		}
		return (inst._defaultDateFormatSeparator);
	},
	
	_setDateFormat: function(inst) {
		var ddf = this._getDefaultDateFormat(inst);
		var ddfs = this._getDefaultDateFormatSeparator(inst);
		if (inst._granularity == 'm') {
			s = '';
			arr = ddf.split(ddfs);
			for (i = 0; i < arr.length; i++) {
				if (arr[i] != 'dd') {
					if (s != '') {
						s += ddfs;
					}
					s += arr[i]
				}
			}
			inst._settings['dateFormat'] = s;
		}
		else if (inst._granularity == 'y') {
			inst._settings['dateFormat'] = 'yy';
		}
		else {
			inst._settings['dateFormat'] = ddf;
		}
	},
	
	_setGranularity: function(inst, granularity) {
		inst._granularity = granularity;
		$(inst._settings['dateInputGranularity']).val(inst._granularity);
	},
	
	_generateGranularity: function(inst) {
		var lthis = this;/* Use for anonymous function captures */
		var garr = ['d','m','y'];
		var inst_datepickerdiv_id = inst._datepickerDiv.attr('id');
		
		if (inst._granularity == undefined || inst._granularity == null) {
			var gran = inst._get('granularity');
			inst._granularity = ((gran==undefined||gran==null)?garr[0]:gran);
		}
		
		$('#'+inst_datepickerdiv_id+' .datepicker_titleRow:last > *:last').after('<td class="granularity_cell"><div>&nbsp;</div></td>');
		$('#'+inst_datepickerdiv_id+' .datepicker_daysRow:first > *:last').after('<td class="granularity_cell" valign="top" align="center" rowspan="10"><input id="granularity_d_'+inst._id+'" name="granularity_d_'+inst._id+'" type="button" value="D" /><input id="granularity_m_'+inst._id+'" name="granularity_m_'+inst._id+'" type="button" value="M" /><input id="granularity_y_'+inst._id+'" name="granularity_y_'+inst._id+'" type="button" value="Y" /></div></td>');
		
		/* Trigget onSelect event to update the InputField */
		this._setDateFormat(inst);
		var dateStr = inst._formatDate(inst._currentDay, inst._currentMonth, inst._currentYear);
		var onSelect = inst._get('onSelect');
		if (onSelect)
			onSelect.apply((inst._input ? inst._input[0] : null), [dateStr, inst]);  // trigger custom callback

		/* Ataching New Events */

		var jNode  = $('#'+inst_datepickerdiv_id+' select.datepicker_newMonth');
		/* Get a rid of existing onchange event */
		jNode.get(0).onchange = null;
		jNode.change(function(obj) {
			var select = obj.target;
			inst._currentMonth = select.selectedIndex;
			lthis._setDateFormat(inst);
			lthis._selectDate(inst._id, inst._formatDate(inst._currentDay, inst._currentMonth, inst._currentYear));
			lthis._fillDateInputFields(inst);
			/* Copied from original code and modified to reestablish the original */
			jQuery.datepicker._selectMonthYear(inst._id, select, 'M');
		});

		jNode = $('#'+inst_datepickerdiv_id+' select.datepicker_newYear');
		/* Get a rid of existing onchange event */
		jNode.get(0).onchange = null;
	
		jNode.change(function(obj) {
			var select = obj.target;	
			inst._currentYear = select.options[select.selectedIndex].value - 0;
			lthis._setDateFormat(inst);
			lthis._selectDate(inst._id, inst._formatDate(inst._currentDay, inst._currentMonth, inst._currentYear));
			lthis._fillDateInputFields(inst);
			/* Copied from original code and modified to reestablish the original */
			jQuery.datepicker._selectMonthYear(inst._id, select, 'Y');
		});
		
		for (i = 0; i < garr.length; i++) {$('#granularity_'+garr[i]+'_'+inst._id).addClass((inst._granularity == garr[i] ? 'current_' : '')+'granularity_button');}
		
		$('#granularity_d_'+inst._id).click(function(obj) {
			$('#granularity_'+inst._granularity+'_'+inst._id).removeClass('current_granularity_button').addClass('granularity_button');
			$('#'+obj.target.id).removeClass('granularity_button').addClass('current_granularity_button');
			lthis._setGranularity(inst, 'd');
			lthis._setDateFormat(inst);
			lthis._fillDateInputFields(inst);
			lthis._selectDate(inst._id, inst._formatDate(inst._currentDay, inst._currentMonth, inst._currentYear));
		});		
		$('#granularity_m_'+inst._id).click(function(obj) {
			$('#granularity_'+inst._granularity+'_'+inst._id).removeClass('current_granularity_button').addClass('granularity_button');
			$('#'+obj.target.id).removeClass('granularity_button').addClass('current_granularity_button');
			lthis._setGranularity(inst, 'm');
			lthis._setDateFormat(inst);
			lthis._fillDateInputFields(inst);
			lthis._selectDate(inst._id, inst._formatDate(inst._currentDay, inst._currentMonth, inst._currentYear));
		});		
		$('#granularity_y_'+inst._id).click(function(obj) {
			$('#granularity_'+inst._granularity+'_'+inst._id).removeClass('current_granularity_button').addClass('granularity_button');
			$('#'+obj.target.id).removeClass('granularity_button').addClass('current_granularity_button');
			lthis._setGranularity(inst, 'y');
			lthis._setDateFormat(inst);
			lthis._fillDateInputFields(inst);
			lthis._selectDate(inst._id, inst._formatDate(inst._currentDay, inst._currentMonth, inst._currentYear));
		});
	}
});

});