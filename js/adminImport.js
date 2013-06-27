/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
function validateImportation(mandatory)
{
    var type_value = [];
	var seted_value = [];
	var elem;
	var col = 'unknow';

	toggle(getE('error_duplicate_type'), false);
	toggle(getE('required_column'), false);
    for (i = 0; elem = getE('type_value['+i+']'); i++)
    {
		if (seted_value[elem.options[elem.selectedIndex].value])
		{
			scroll(0,0);
			toggle(getE('error_duplicate_type'), true);
			return false;
		}
		else if (elem.options[elem.selectedIndex].value != 'no')
			seted_value[elem.options[elem.selectedIndex].value] = true;
	}
	for (needed in mandatory)
		if (!seted_value[mandatory[needed]])
		{
			scroll(0,0);
			toggle(getE('required_column'), true);
			getE('missing_column').innerHTML = mandatory[needed];
			elem = getE('type_value[0]');
			for (i = 0; i < elem.length; ++i)
			{
				if (elem.options[i].value == mandatory[needed])
				{
					getE('missing_column').innerHTML = elem.options[i].innerHTML;
					break ;
				}
			}
			return false
		}
}

function askFeatureName(selected, selector)
{
	var elem;

	if (selected.value == 'feature')
	{
		$('#features_' + selector).show();
		$('#feature_name_' + selector).attr('name', selected.name);
	}
	else
	{
		$('#features_' + selector).hide();
		$('#feature_name_' + selector).removeAttr('name');
	}

}

function replaceFeature(toReplace, selector)
{
	var elem;

	if ($('#feature_name_' + selector).val() == '')
		return false;
	elem = getE(toReplace);
	elem.options[elem.selectedIndex].text = $('#feature_name_' + selector).val();
	elem.options[elem.selectedIndex].value = '#F_' + $('#feature_name_' + selector).val();
	$('#features_' + selector).toggle();
	$('#feature_name_' + selector).val('');
	$('#feature_name_' + selector).attr('name', '');
}

$(document).ready(function(){
	$('#saveImportMatchs').unbind('click').click(function(){
		var newImportMatchs = $('#newImportMatchs').attr('value');
		if (newImportMatchs == '')
			alert(errorEmpty);
		else
		{
			var matchFields = '';
			$('.type_value').each( function () {
				matchFields += '&' + encodeURIComponent($(this).attr('id')) + '=' + encodeURIComponent($(this).attr('value'));
			});
			$.ajax({
		       type: 'GET',
		       url: 'ajax.php',
		       async: false,
		       cache: false,
		       dataType : "json",
		       data: 'ajax=true&saveImportMatchs&skip='+$('input[name=skip]').attr('value')+'&newImportMatchs='+newImportMatchs+matchFields,
		       success: function(jsonData)
		       {
					$('#valueImportMatchs').append('<option id="'+jsonData.id+'" value="'+matchFields+'" selected="selected">'+newImportMatchs+'</option>');
					$('#selectDivImportMatchs').fadeIn('slow');
		       },
		      error: function(XMLHttpRequest, textStatus, errorThrown) 
		       {
		       		alert('TECHNICAL ERROR Details: '+XMLHttpRequest.responseText);
		       }
		   });
	
		}
	});
	
	$('#loadImportMatchs').unbind('click').click(function(){
		var idToLoad = $('select#valueImportMatchs option:selected').attr('id');
		$.ajax({
		       type: 'GET',
		       url: 'ajax.php',
		       async: false,
		       cache: false,
		       dataType : "json",
		       data: 'ajax=true&loadImportMatchs&idImportMatchs='+idToLoad,
		       success: function(jsonData)
		       {
					var matchs = jsonData.matchs.split('|')
					$('input[name=skip]').val(jsonData.skip);
					for (i=0; i < matchs.length; i++)
					{
						$('#type_value\\[' + i + '\\]').val(matchs[i]).attr('selected',true);
						if(matchs[i].substring(0, 3) == '#F_')
						{
							$('#feature_name_' + i).val(matchs[i].replace(matchs[i].substring(0, 3), ''));
							$('#feature_name_' + i).attr('name', 'type_value[' + i + ']');
							replaceFeature($('#feature_name_' + i).attr('name'), i);
							$('#features_' + i).hide();
						}
					}
		       },
		      error: function(XMLHttpRequest, textStatus, errorThrown) 
		       {
		       		alert('TECHNICAL ERROR Details: '+XMLHttpRequest.responseText);
		       }
		   });
	});
	
	$('#deleteImportMatchs').unbind('click').click(function(){
		var idToDelete = $('select#valueImportMatchs option:selected').attr('id');
		$.ajax({
		       type: 'GET',
		       url: 'ajax.php',
		       async: false,
		       cache: false,
		       dataType : "json",
		       data: 'ajax=true&deleteImportMatchs&idImportMatchs='+idToDelete,
		       success: function(jsonData)
		       {
					$('select#valueImportMatchs option[id=\''+idToDelete+'\']').remove();
					if ($('select#valueImportMatchs option').length == 0)
						$('#selectDivImportMatchs').fadeOut();
		       },
		      error: function(XMLHttpRequest, textStatus, errorThrown) 
		       {
		       		alert('TECHNICAL ERROR Details: '+XMLHttpRequest.responseText);
		       		
		       }
		   });
	});
});