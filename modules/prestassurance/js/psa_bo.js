var cat_level = 0;
var cat_psa_cache = new Object();
var ajaxQueries = new Array();
var id_ps_category = 0;
var id_psa_category = 0;


$(document).ready(function() 
{
	configureHelpPsa();
	impactTypeSelect();
	//configureFancy();
	
	$('.fancyBox').fancybox({showCloseButton : false});
	
	$('#tab-pane-1').tabs({plain:true});
	
	$('#tab-pane-1').tabs('add',{  
	    title: tab_1,  
	    content: $('#tab_1').html()
	});
	$('#tab-pane-1').tabs('add',{  
	    title: tab_2,  
	    content: $('#tab_2').html()
	});
	$('#tab-pane-1').tabs('add',{  
	    title: tab_3,  
	    content: $('#tab_3').html()
	});  
		
	$('#tab-pane-1').tabs('select', pos_select);
	$('.panel-body').css('padding', '3px');
	tooglePreprodIp($('input[name=PSA_ENVIRONMENT]:checked'));
	
	$('input[name=PSA_ENVIRONMENT]').change(function () {
		tooglePreprodIp($(this));
	});
	
	configureTreeGrid();
	configureHelpPsa();
	initColorPicker();
});

function tooglePreprodIp(elt)
{
	if (elt.attr('id') == 'PSA_ENVIRONMENT_PROD')
		$('#preprod_ip').fadeOut();
	else
		$('#preprod_ip').fadeIn();
}

function initColorPicker() 
{
	$('.color-picker').each(function() 
	{
		var $colorpkr = $(this);
		$colorpkr.find('div').css('background-color', $colorpkr.parent().find('input').val());
		$colorpkr.ColorPicker({
			color: $colorpkr.parent().find('input').val(),
			onBeforeShow: function(colpkr) {
				$(colpkr).stop().css({
					display: 'none',
					opacity: ''
				});
				return false;
			},
			onShow: function(colpkr) {
				$(colpkr).fadeIn(500);
				return false;
			},
			onHide: function(colpkr) {
				$(colpkr).stop().fadeOut(500);
				return false;
			},
			onChange: function(hsb, hex, rgb) {
				$colorpkr.find('div').css('background-color', '#' + hex);
				$colorpkr.parent().find('input').val('#' + hex);
			}
		});
	});
}
/*
function configureFancy() 
{
	$('.fancybox_set').each(function() 
	{
		id_category = $(this).attr('id');
		name = $(this).attr('rel');
		$('#' + id_category).fancybox({
			onStart: function(elt) {
				resizeFancy();
				id_ps_category = $(elt).attr('id').replace('set_match_', '');
			},
			onComplete: function() 
{
				resizeFancy();
			},
			onClosed: function(elt) {
				id = $(elt).attr('id').replace('set_match_', '');
				refreshSelectCategoryWidget(id);
			},
			scrolling: 'no',
			autoDimensions: true,
			ajax: {
				type: 'POST',
				data: 'token=' + token + '&setCategory=true&id_category=' + id_category + '&name=' + name,
			}
		});
	});
	$('.fancybox_edit').each(function() 
{
		id_category = $(this).attr('id');
		name = $(this).attr('rel');
		$('#' + id_category).fancybox({
			onStart: function(elt) {
				resizeFancy();
				id_ps_category = $(elt).attr('id').replace('edit_match_', '');
			},
			onComplete: function() 
{
				resizeFancy();
			},
			onClosed: function(elt) {
				id = $(elt).attr('id').replace('edit_match_', '');
				refreshSelectCategoryWidget(id);
			},
			scrolling: 'no',
			autoDimensions: true,
			ajax: {
				type: 'POST',
				data: 'token=' + token + '&setCategory=true&id_category=' + id_category + '&name=' + name,
			}
		});
	});
	$('.fancybox_edit_impact').each(function() 
	{	
		id_category = $(this).attr('id');
		name = $(this).attr('rel');
		$('#' + id_category).fancybox({
			onStart: function(elt) {
				resizeFancy();
				id_ps_category = $(elt).attr('id').replace('edit_impact_', '');
			},
			onComplete: function(elt) {
				id = $(elt).attr('id').replace('edit_impact_', '');
				$('#impact_value_' + id).typeWatch(typeWatchOption);
				resizeFancy();
				impactTypeSelect();
				configureSaveImpactButton();
			},
			onClosed: function(elt) {
				id = $(elt).attr('id').replace('edit_impact_', '');
				refreshSelectCategoryWidget(id);
			},
			scrolling: 'no',
			autoDimensions: true,
			ajax: {
				type: 'POST',
				data: 'token=' + token + '&setImpact=true&id_category=' + id_category.replace('edit_impact_', '') + '&name=' + name,
			}
		});
	});
	resizeFancy();
}
*/

function makeSelectSubCategories(data, id)
{
	if (data.length) cat_psa_cache[cat_level + 1] = data;
	if ($('#fma_category_level_' + (cat_level + 1)).length || data.length) { // clear all select except select one
		$('.fma_categories').each(function() 
{
			if (parseInt($(this).attr('id').replace('fma_category_level_', '')) > cat_level) $(this).parent().remove();
		});
	}
	if (data.length)
	{
		$('#fma_category tr').append('<td id="level_' + (cat_level + 1) + '"><select class="fma_categories" size="30" style="float:right" id="fma_category_level_' + (cat_level + 1) + '" onchange="getFmaSubCategories(parseInt(this.value), ' + (cat_level + 1) + ');"></select></td>');
		dropdownList = $('#fma_category_level_' + (cat_level + 1));
		$.each(data, function() 
		{
			var option = new Option(this.name, this.id);
			dropdownList.append(option);
		});
	}
	else
		displaySaveMatchButton(cat_level);
		
	if (isSelectable(cat_level, id))
		displaySaveMatchButton(cat_level + 1);
	$.fancybox.hideActivity();
	resizeFancy();
}

function getFmaCategoryPrice(level, id_cat)
{
	price = 0;
	$(cat_psa_cache[level]).each(function() 
{
		if (parseInt(this.id) == parseInt(id_cat)) price = this.price;
	});
	return price;
}

function getFmaCategoryMaximumPrice(level, id_cat)
{
	price = 0;
	$(cat_psa_cache[level]).each(function() 
{
		if (parseInt(this.id) == parseInt(id_cat)) maximum_price = this.maximum_price;
	});
	return maximum_price;
}

function getFmaCategoryMaximumProductPrice(level, id_cat)
{
	price = 0;
	$(cat_psa_cache[level]).each(function() 
{
		if (parseInt(this.id) == parseInt(id_cat)) maximum_product_price = this.maximum_product_price;
	});
	return maximum_product_price;
}

function getFmaCategoryMinimumProductPrice(level, id_cat)
{
	price = 0;
	$(cat_psa_cache[level]).each(function() 
{
		if (parseInt(this.id) == parseInt(id_cat)) minimum_product_price = this.minimum_product_price;
	});
	return minimum_product_price;
}

function isSelectable(level, id_cat)
{
	selectable = false;
	$(cat_psa_cache[level]).each(function() 
{
		if (parseInt(this.id) == parseInt(id_cat)) selectable = this.selectable;
	});
	return selectable;
}

function getFmaCategoryName(level, id_cat)
{
	name = '';
	$(cat_psa_cache[level]).each(function() 
{
		if (parseInt(this.id) == parseInt(id_cat)) name = this.name;
	});
	return name;
}

function resizeFancy() 
{
	if ($('#fancybox-content #fma_category').length) {
		$('#fancybox-content').width($('#fancybox-content #fma_category').width() + 30);
		$('#fancybox-outer').width($('#fancybox-content #fma_category').width() + 45);
	} else {
		$('#fancybox-content').width($('#fancybox-content #fma_impact').width() + 30);
		$('#fancybox-outer').width($('#fancybox-content #fma_impact').width() + 45);
	}
	$.fancybox.center();
}



function impactTypeSelect() 
{
	$('.impact_type').change(function() 
	{
		id = $(this).attr('id').replace('impact_type_', '');
		if ($(this).attr('value') == 'percentage') {
			$('.impact_fixed_input').hide();
			$('.impact_percentage_input').fadeIn();
		} else {
			$('.impact_percentage_input').hide();
			$('.impact_fixed_input').fadeIn();
		}
		calcFinalPriceAndBenefit();
	});
}

function calcFinalPriceAndBenefit() 
{
	$('#ajax-loader').fadeIn();
	minimum_price = parseFloat($('input[name="minimum_price_' + id_ps_category + '"]').attr('value'));
	maximum_price = parseFloat($('input[name="maximum_price_' + id_ps_category + '"]').attr('value'));
	impact_value = parseFloat($('#impact_value_' + id_ps_category).attr('value').replace(',', '.'));

	if (impact_value < minimum_price)
	{
		impact_value = minimum_price;
		$('#impact_value_' + id_ps_category).attr('value', minimum_price);
	}
	
	if (impact_value > maximum_price)
	{
		impact_value = maximum_price;
		$('#impact_value_' + id_ps_category).attr('value', maximum_price);
	}
	
	selling_price = minimum_price;
	$.ajax({
		type: 'POST',
		url: ajax_url + 'ajax.php',
		async: true,
		cache: false,
		dataType: "json",
		data: {
			token: token,
			calcFinalPriceAndBenefit: 1,
			minimum_price: minimum_price,
			maximum_price: maximum_price,
			impact_value: impact_value
		},
		success: function(jsonData) {
			
			if (impact_value > jsonData.selling_price)
			$('#impact_value_' + id_ps_category).val(jsonData.selling_price)
			
			$('.selling_price_' + id_ps_category).each(function() 
{
				$(this).html(formatCurrency(jsonData.selling_price, currencyFormat, currencySign, 1))
			});
			$('input[name="selling_price_' + id_ps_category + '"]').val(jsonData.selling_price);
			$('.benefit_' + id_ps_category).each(function() 
{
				$(this).html(formatCurrency(jsonData.benefit, currencyFormat, currencySign, 1));
			});
			$('input[name="benefit_' + id_ps_category + '"]').val(jsonData.benefit);
			$('#ajax-loader').fadeOut();
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert('TECHNICAL ERROR\nDetails:\nError thrown: ' + XMLHttpRequest + '\n' + 'Text status: ' + textStatus);
			$('#ajax-loader').fadeOut();
		}
	});
	$('#ajax-loader').fadeOut();
}


function getFmaSubCategories(id_category, level)
{
	if (isNaN(id_category)) return;
	id_psa_category = id_category; //abort all ajaxQuery running
	for (i = 0; i < ajaxQueries.length; i++) ajaxQueries[i].abort();
	ajaxQueries = new Array();
	cat_level = level;
	$.fancybox.showActivity();
	ajaxQuery = $.ajax({
		type: 'POST',
		url: ajax_url + 'ajax.php',
		async: true,
		cache: false,
		dataType: "json",
		data: 'token=' + token + '&getFmaSubCategories=true&id_category=' + id_category,
		success: function(jsonData) {
			if ($('#save_match').length) $('#save_match').parent().parent().remove();
			makeSelectSubCategories(jsonData, id_category);
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert('TECHNICAL ERROR\nDetails:\nError thrown: ' + XMLHttpRequest + '\n' + 'Text status: ' + textStatus);
		}
	});
	ajaxQueries.push(ajaxQuery);
}

function displaySaveMatchButton(level)
{
	button = '<button class="my_button" id="save_match" style="margin-left:10px;padding:10px;display:none"><img style="margin-right:5px" src="' + img_url + '/img/save.png">Sauvegarder</button>';
	if (!$('#save_match').length)
	{
		$('#fma_category').append('<tr><td colspan="' + (level + 1) + '">'+button+'</td></tr>');
		$('#save_match').fadeIn();
	}
	$('#save_match').unbind('click').click(function() 
	{
		$.ajax({
			type: 'POST',
			url: ajax_url + 'ajax.php',
			async: true,
			cache: false,
			dataType: "json",
			data: {
				token: token,
				saveMatchCategory: 1,
				id_category: id_ps_category,
				id_psa_category: id_psa_category,
				minimum_price: getFmaCategoryPrice(cat_level, id_psa_category),
				name: getFmaCategoryName(cat_level, id_psa_category),
				maximum_price: getFmaCategoryMaximumPrice(cat_level, id_psa_category),
				maximum_product_price: getFmaCategoryMaximumProductPrice(cat_level, id_psa_category),
				minimum_product_price: getFmaCategoryMinimumProductPrice(cat_level, id_psa_category)
			},
			success: function(jsonData) {
				if (jsonData.hasError)
					alert('TECHNICAL ERROR\n Text status: can\'t save matching');
				else
				{
					$('#treegrid').treegrid('loading');
					closeWindow();
					refreshRow(id_ps_category, jsonData.html.psa_cat, jsonData.html.price, jsonData.html.action);
					$('#treegrid').treegrid('loaded');
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				alert('TECHNICAL ERROR\nDetails:\nError thrown: ' + XMLHttpRequest + '\n' + 'Text status: ' + textStatus);
			}
		});
	});
}

function refreshRow(id_node, psa_cat, price, action)
{
	$('#menu_action_'+id_node).remove();
	$('tr[node-id='+id_node+'] td[field=psa_cat]').html('<div style="width:270px;text-align:left;" class="datagrid-cell ">'+psa_cat+'</div>');
	$('tr[node-id='+id_node+'] td[field=price]').html('<div style="width:90px;text-align:center;" class="datagrid-cell ">'+price+'</div>');
	$('tr[node-id='+id_node+'] td[field=action]').html('<div style="width:70px;text-align:center;" class="datagrid-cell ">'+action+'</div>');
	configureMenuAction();
}

function configureSaveImpactButton() 
{
	$(document).find('#win_set_price button#save_impact').click(function() 
	{
		impact_type = $('#impact_type_' + id_ps_category).attr('value');
		impact_value = $('#impact_value_' + id_ps_category).attr('value');
		selling_price = parseFloat($('input[name="selling_price_' + id_ps_category + '"]').attr('value'));
		benefit = parseFloat($('input[name="benefit_' + id_ps_category + '"]').attr('value'));
		
		$.ajax({
			type: 'POST',
			url: ajax_url + 'ajax.php',
			async: true,
			cache: false,
			dataType: "json",
			data: {
				token: token,
				saveImpact: 1,
				id_category: id_ps_category,
				impact_type: impact_type,
				impact_value: impact_value,
				selling_price: selling_price,
				benefit: benefit
			},
			success: function(jsonData) {
				if (jsonData.hasError)
					alert('TECHNICAL ERROR\n Text status: can\'t save matching');
				else
				{
					$('#treegrid').treegrid('loading');
					
					refreshRow(id_ps_category, jsonData.html.psa_cat, jsonData.html.price, jsonData.html.action);
					$('#treegrid').treegrid('loaded');
					closeWindow();
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				alert('TECHNICAL ERROR\nDetails:\nError thrown: ' + XMLHttpRequest + '\n' + 'Text status: ' + textStatus);
			}
		});
	});
	clearInterval(timer);
}

function applyMatchingChildren(id_ps_category)
{
	$('#treegrid').treegrid('loading');
	$.ajax({
		type: 'POST',
		url: ajax_url + 'ajax.php',
		async: true,
		cache: false,
		dataType: "json",
		data: {
			token: token,
			applyMatchingChildren: 1,
			id_category: id_ps_category
		},
		success: function(jsonData) {
			if (jsonData.hasError)
				alert('TECHNICAL ERROR\n Text status: can\'t save matching');
			else
			{
				$(jsonData.html).each(function (){
					refreshRow(this.id_category, this.psa_cat, this.price, this.action);
				});	
			}
			$('#treegrid').treegrid('loaded');
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert('TECHNICAL ERROR\nDetails:\nError thrown: ' + XMLHttpRequest + '\n' + 'Text status: ' + textStatus);
		}
	});
}

function applyPriceChildren(id_ps_category)
{
	$('#treegrid').treegrid('loading');
	$.ajax({
		type: 'POST',
		url: ajax_url + 'ajax.php',
		async: true,
		cache: false,
		dataType: "json",
		data: {
			token: token,
			applyPriceChildren: 1,
			id_category: id_ps_category
		},
		success: function(jsonData) {
			if (jsonData.hasError)
				alert('TECHNICAL ERROR\n Text status: can\'t save matching');
			else
			{
				$(jsonData.html).each(function (){
					refreshRow(this.id_category, this.psa_cat, this.price, this.action);
				});	
			}
			$('#treegrid').treegrid('loaded');
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert('TECHNICAL ERROR\nDetails:\nError thrown: ' + XMLHttpRequest + '\n' + 'Text status: ' + textStatus);
		}
	});
}

function deleteMatching(id_ps_category) 
{
	$('#treegrid').treegrid('loading');
	$.ajax({
		type: 'POST',
		url: ajax_url + 'ajax.php',
		async: true,
		cache: false,
		dataType: "json",
		data: {
			token: token,
			deleteMatching: 1,
			id_category: id_ps_category
		},
		success: function(jsonData) {
			if (jsonData.hasError)
				alert('TECHNICAL ERROR\n Text status: can\'t save matching');
			else
				refreshRow(id_ps_category, jsonData.html.psa_cat, jsonData.html.price, jsonData.html.action);
			$('#treegrid').treegrid('loaded');
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			$('#treegrid').treegrid('loaded');
			alert('TECHNICAL ERROR\nDetails:\nError thrown: ' + XMLHttpRequest + '\n' + 'Text status: ' + textStatus);
		}
	});
}

function acceptCGV() 
{
	$('#psa_cgv').fadeOut();
	$.ajax({
		type: 'POST',
		url: ajax_url + 'ajax.php',
		async: true,
		cache: false,
		dataType: "json",
		data: 'token=' + token + '&updateCGVDate',
		success: function(jsonData) {
			if (jsonData.ok) {
				$('#psa_content').fadeIn();
				$('#psa_cgv').fadeOut();
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert('TECHNICAL ERROR\nDetails:\nError thrown: ' + XMLHttpRequest + '\n' + 'Text status: ' + textStatus);
		}
	});
	return false;
}

function addRemoteAddr() 
{
	var length = $('input[name=PSA_IP_ADDRESS]').attr('value').length;
	if (length > 0) $('input[name=PSA_IP_ADDRESS]').attr('value', $('input[name=PSA_IP_ADDRESS]').attr('value') + ',' + addr);
	else $('input[name=PSA_IP_ADDRESS]').attr('value', addr);
}

function importConfig() 
{
	$('#error_return').html('');
	$('#error_return').fadeOut();
	if ($('#unique_id').val() == '' || $('#email').val() == '') {
		$('#error_return').html('<ul><li>' + fill_all_input + '</li></ul>');
		$('#error_return').fadeIn();
		return false;
	}
	$('#ajax-loader-export').fadeIn('fast', function() 
	{
		$.ajax({
			type: 'POST',
			url: ajax_url + 'ajax.php',
			async: true,
			cache: false,
			dataType: "json",
			data: 'token=' + token + '&importConfig=true&unique_id=' + $('#unique_id').val() + '&email=' + $('#email').val(),
			success: function(jsonData) {
				if (jsonData.hasErrors) {
					$('#error_return').html('<ul><li>' + no_config_found + '</li></ul>');
					$('#error_return').fadeIn();
				} else {
					document.location.reload();
				}
				$('#ajax-loader-export').fadeOut();
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				alert('TECHNICAL ERROR\nDetails:\nError thrown: ' + XMLHttpRequest + '\n' + 'Text status: ' + textStatus);
				$('#ajax-loader-export').fadeOut();
			}
		});
	});
	return false;
}

function exportConfig() 
{
	$('#error_return').html('');
	$('#error_return').fadeOut();
	$('#unique_id').html('');
	$('#conf_return').fadeOut();
	$('#ajax-loader-export').fadeIn('fast', function() 
	{
		$.ajax({
			type: 'POST',
			url: ajax_url + 'ajax.php',
			async: true,
			cache: false,
			dataType: "json",
			data: 'token=' + token + '&exportConfig=true',
			success: function(jsonData) {
				if (jsonData.hasErrors) {
					var errors = '<ul>';
					for (error in jsonData.errors) errors += '<li>' + jsonData.errors[error] + '</li>';
					$('#error_return').html(errors + '</ul>');
					$('#error_return').fadeIn();
				} else {
					$('#unique_id').html('' + jsonData.id + '');
					$('#conf_return').fadeIn();
				}
				$('#ajax-loader-export').fadeOut();
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				alert('TECHNICAL ERROR\nDetails:\nError thrown: ' + XMLHttpRequest + '\n' + 'Text status: ' + textStatus);
				$('#ajax-loader-export').fadeOut();
			}
		});
	});
	return false;
}

function configureHelpPsa()
{
	$(".help_psa").CreateBubblePopup({
					position : "right",
					align	 : "left",
					innerHtmlStyle: {color:"black", "text-align":"center"},
					themeName: 	"azure",
					themePath: themePath,
					width: 300
				});
				
	$(".help_psa").each( function () {
		$(this).SetBubblePopupInnerHtml($(this).children('img').attr('alt'));
	});
}

