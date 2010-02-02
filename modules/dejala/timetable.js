/* Cache les heures non dispo pour le jour dayIndex */
function hideUnusedHours(dayIndex){
	var i = 0;
	var t_obj = document.getElementById('divhr0');

	while (t_obj)
	{
		var cal = djl_calendar[dayIndex];
		var calStart = 0;
		var calStop = 0;
		if (cal) {
			if (cal[2])
				calStart = djl_calendar[dayIndex][2];
			if (cal[3])
				calStop =	djl_calendar[dayIndex][3];
		}
		
		if ( (i >= calStart) && (i <= calStop) ) {
			t_obj.style.display = '';
		} else {
			t_obj.style.display = 'none';
		}
		i++;
		t_obj = document.getElementById('divhr' + i);
	}
}

/* Sélectionne un des jours par son index */
function selectDay(dayIndex)
{
	var i = 0;
	var t_obj = document.getElementById('shipd0');
	var currentShipd = document.getElementById('shipd'+deliveryDateSelected);
	if (currentShipd) {
		currentShipd.parentNode.style.fontWeight='';
		currentShipd.parentNode.style.color = '';
	}
	while (t_obj)
	{
		t_obj.checked = false;
		if (i == dayIndex)
			t_obj.checked = true;
		i++;
		t_obj = document.getElementById('shipd' + i);
	}
	deliveryDateSelected = dayIndex;
	currentShipd = document.getElementById('shipd'+deliveryDateSelected);
	if (currentShipd) {
		currentShipd.parentNode.style.fontWeight='bold';
		currentShipd.parentNode.style.color = '#993300';
	}
	hideUnusedHours(dayIndex);
	
	var cal = djl_calendar[dayIndex];
	var calStart = 9;
	if (cal) {
		if (cal[2])
			calStart = djl_calendar[dayIndex][2];
		selectHour(calStart);
	}
}


function selectHour(hourIndex)
{
	var i = 0;
	var t_obj = document.getElementById('shiphr0');
	var currentShipHr = document.getElementById('shiphr' + deliveryHourSelected);
	if (currentShipHr) {
		currentShipHr.parentNode.style.fontWeight='';
		currentShipHr.parentNode.style.color = '';
	}
	while (t_obj)
	{
		t_obj.checked = false;
		if (i == hourIndex)
			t_obj.checked = true;
		i++;
		t_obj = document.getElementById('shiphr' + i);
	}
	deliveryHourSelected = hourIndex;
	currentShipHr = document.getElementById('shiphr' + deliveryHourSelected);
	if (currentShipHr) {
		currentShipHr.parentNode.style.fontWeight='bold';
		currentShipHr.parentNode.style.color = '#993300';
	}
}

/**
	toggle element visibility : make it visible if to_which is 1 and element is not visible, otherwise make it invisible
**/
function toggle_visibility(eltId, to_which)
{
	var el = document.getElementById(eltId);
	if ( (to_which == 0) && (el.style.display != 'none') ) {
		el.style.display = 'none';
	}
	else if (to_which == 1) {
		el.style.display = '';
	}
}
