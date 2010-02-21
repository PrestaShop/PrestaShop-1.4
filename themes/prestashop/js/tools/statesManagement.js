$(document).ready(function(){
	$('select#id_country').change(function(){
		updateState();
		updateNeedIDNumber();
	});
	updateState();
	updateNeedIDNumber();
});

function updateState()
{
	$('select#id_state option:not(:first-child)').remove();
	var states = countries[$('select#id_country').val()];
	if(typeof(states) != 'undefined')
	{
		for (indexState in states)
		{
			//ie bug fix
			if (indexState != 'indexOf')
				$('select#id_state').append('<option value="'+indexState+'"'+ (idSelectedCountry == indexState ? ' selected="selected' : '') + '">'+states[indexState]+'</option>');
		}
		$('p.id_state:hidden').slideDown('slow');
	}
	else
		$('p.id_state').slideUp('fast');
}

function updateNeedIDNumber()
{
	var idCountry = parseInt($('select#id_country').val());
	
	if ($.inArray(idCountry, countriesNeedIDNumber) >= 0)
		$('fieldset.dni').slideDown('slow');
	else
		$('fieldset.dni').slideUp('fast');
}
