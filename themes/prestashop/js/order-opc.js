/*
* 2007-2010 PrestaShop 
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
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

function updateCarrierList(carriers)
{
	/* contains all carrier available for this address */
	if (carriers.length == 0)
	{
		$('input[name=id_carrier]:checked').attr('checked', false);
		$('#noCarrierWarning').show();
		$('#extra_carrier').hide();
		$('#recyclable_block').hide();
		$('table#carrierTable:visible').hide();
	}
	else
	{
		var html = '';
		for (i=0;i<carriers.length; i++)
		{
			var itemType = '';
			
			if (i == 0)
				itemType = 'first_item ';
			else if (i == carriers.length-1)
				itemType = 'last_item ';
			if (i % 2)
				itemType = itemType + 'alternate_item';
			else
				itemType = itemType + 'item';
			
			var name = carriers[i].name;
			if (carriers[i].img != '')
				name = '<img src="'+carriers[i].img+'" alt="" />';
				
			html = html + 
			'<tr class="'+itemType+'">'+
				'<td class="carrier_action radio"><input type="radio" name="id_carrier" value="'+carriers[i].id_carrier+'" id="id_carrier'+carriers[i].id_carrier+'" onclick="updateCarrierSelectionAndGift();" /></td>'+
				'<td class="carrier_name"><label for="id_carrier'+carriers[i].id_carrier+'">'+name+'</label></td>'+
				'<td class="carrier_infos">'+carriers[i].delay+'</td>'+
				'<td class="carrier_price"><span class="price">'+formatCurrency(carriers[i].price, currencyFormat, currencySign, currencyBlank)+'</span>';
			if (taxEnabled && displayPrice == 0)
				html = html + ' ' + txtWithTax;
			else
				html = html + ' ' + txtWithoutTax;
			html = html + '</td>'+
			'</tr>';
		}
		$('#noCarrierWarning:visible').hide();
		$('#extra_carrier:hidden').show();
		$('table#carrierTable tbody').html(html);
		$('table#carrierTable:hidden').show();
		$('#recyclable_block:hidden').show();
	}
}

function updateAddressesStatus()
{
	var nameAddress_delivery = $('select#id_address_delivery option:selected').html();
	var nameAddress_invoice = $('input[type=checkbox]#addressesAreEquals:checked').length == 1 ? nameAddress_delivery : ($('select#id_address_invoice').length == 1 ? $('select#id_address_invoice option:selected').html() : nameAddress_delivery);

	$('span#order-opc_status-address_delivery').html(nameAddress_delivery);
	$('span#order-opc_status-address_invoice').html(nameAddress_invoice);
}

function updateCarrierStatus()
{
	if ($('input[name=id_carrier]:checked').length != 0)
	{
		var name = $('label[for='+$('input[name=id_carrier]:checked').attr('id')+']').html();
		$('#order-opc_status-carrier').css('color', 'green').html('"'+name+'" '+txtHasBeenSelected);
	}
	else
		$('#order-opc_status-carrier').css('color', 'red').html(txtNoCarrierIsSelected);
}

function updateTOSStatus()
{
	if ($('input#cgv:checked').length != 0)
		$('#order-opc_status-TOS').css('color', 'green').html(txtTOSIsAccepted);
	else
		$('#order-opc_status-TOS').css('color', 'red').html(txtTOSIsNotAccepted);
}

function showPaymentModule()
{
	closeAccordion();
	$.ajax({
	       type: 'POST',
	       url: baseDir + 'order-opc.php',
	       async: true,
	       cache: false,
	       dataType : "html",
	       data: 'ajax=true&method=getPaymentModule&token=' + static_token ,
	       success: function(html)
	        {
	       		if (html === 'freeorder')
	       			document.location.href = 'history.php';
	       		else
	       		{
	       			$('#proceed_to_checkout').hide();
	       			$('#payment_module_list').html(html);
	       			$('#payment_module_list_title').slideDown('slow', function() {$('#payment_module_list').slideDown('slow');});
	       		}
	    	},
	       error: function(XMLHttpRequest, textStatus, errorThrown) {alert("TECHNICAL ERROR: unable to save adresses \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);}
	   });
}

function resetPaymentModuleList()
{
	$('#payment_module_list').slideUp('slow', function () {$('#payment_module_list_title').slideUp('slow');});
	$('#payment_module_list').html();
	$('#proceed_to_checkout').show();
}

function closeAccordion()
{
	$('.order-opc_status').show();
	$('.order-opc_block-status').attr('src', imgDir+'icon/more.gif');
	$('.order-opc_block-content').slideUp('slow');
	$('.order-opc_block-content').removeClass('selected');
	$('.first_next_button').slideDown('fast');
}

function getCarrierListAndUpdate()
{
	$.ajax({
        type: 'POST',
        url: baseDir + 'order-opc.php',
        async: true,
        cache: false,
        dataType : "json",
        data: 'ajax=true&method=getCarrierList&token=' + static_token,
        success: function(jsonData)
        {
				if (jsonData.hasError)
				{
					var errors = '';
					for(error in jsonData.errors)
						//IE6 bug fix
						if(error != 'indexOf')
							errors += jsonData.errors[error] + "\n";
					alert(errors);
				}
				else
					updateCarrierList(jsonData.carriers);
        		updateCarrierStatus();
			}
	});
	
	resetPaymentModuleList();
}

function updateAddressesAndCarriersList()
{
	var idAddress_delivery = $('select#id_address_delivery').val();
	var idAddress_invoice = $('input[type=checkbox]#addressesAreEquals:checked').length == 1 ? idAddress_delivery : ($('select#id_address_invoice').length == 1 ? $('select#id_address_invoice').val() : idAddress_delivery);
	   
	$.ajax({
           type: 'POST',
           url: baseDir + 'order-opc.php',
           async: true,
           cache: false,
           dataType : "json",
           data: 'processAddress=true&step=2&ajax=true&id_address_delivery=' + idAddress_delivery + '&id_address_invoice=' + idAddress_invoice + '&token=' + static_token,
           success: function(jsonData)
           {
           		if (jsonData.hasError)
				{
					var errors = '';
					for(error in jsonData.errors)
						//IE6 bug fix
						if(error != 'indexOf')
							errors += jsonData.errors[error] + "\n";
					alert(errors);
				}
				else
				{
					updateCarrierList(jsonData.carriers);
					updateCartSummary(jsonData.summary);
					updateAddressesStatus();
				}
           		updateCarrierStatus();
			},
           error: function(XMLHttpRequest, textStatus, errorThrown) {alert("TECHNICAL ERROR: unable to save adresses \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);}
	});
	
	resetPaymentModuleList();
}

function updateCarrierSelectionAndGift()
{
	var recyclablePackage = 0;
	var gift = 0;
	var giftMessage = '';
	var idCarrier = 0;

	if ($('input#recyclable:checked').length)
		recyclablePackage = 1;
	if ($('input#gift:checked').length)
	{
		gift = 1;
		giftMessage = encodeURI($('textarea#gift_message').val());
	}
	
	if ($('input[name=id_carrier]:checked').length)
		idCarrier = $('input[name=id_carrier]:checked').val();
	
	$.ajax({
       type: 'POST',
       url: baseDir + 'order-opc.php',
       async: true,
       cache: false,
       dataType : "json",
       data: 'ajax=true&method=updateCarrier&id_carrier=' + idCarrier + '&recyclable=' + recyclablePackage + '&gift=' + gift + '&gift_message=' + giftMessage + '&token=' + static_token ,
       success: function(jsonData)
       {
       		if (jsonData.hasError)
    		{
    			var errors = '';
    			for(error in jsonData.errors)
    				//IE6 bug fix
    				if(error != 'indexOf')
    					errors += jsonData.errors[error] + "\n";
    			alert(errors);
    		}
    		else
    			updateCartSummary(jsonData);
    		updateCarrierStatus();
    	},
       error: function(XMLHttpRequest, textStatus, errorThrown) {alert("TECHNICAL ERROR: unable to save carrier \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);}
   });
	resetPaymentModuleList();
}

$(function() {
	// update status
	updateAddressesStatus();
	updateCarrierStatus();
	updateTOSStatus();
	// hide payment module list
	$('#payment_module_list').hide();
	$('#payment_module_list_title').hide();
	// Accordion
	$('.order-opc_block-content').hide();
	$('.order-opc_block-status').attr('src', imgDir+'icon/more.gif');
	$('.order-opc_block').click(function() {
		if (!$(this).next('.order-opc_block-content').hasClass('selected'))
		{
			$('.order-opc_block-status').attr('src', imgDir+'icon/more.gif');
			$('.order-opc_block-content').slideUp('slow');
			$('.order-opc_block-content').next('.order-opc_status').show();
			$('.order-opc_block-content').removeClass('selected');
			$(this).next('.order-opc_block-content').addClass('selected');
			$(this).children('.order-opc_block-status').attr('src', imgDir+'icon/less.gif');
			$('.selected').next('.order-opc_status').hide();
			$('.selected').slideDown('slow');
		}
		else
		{
			$('.order-opc_block-content').removeClass('selected');
			$(this).children('.order-opc_block-status').attr('src', imgDir+'icon/more.gif');
			$(this).next('.order-opc_block-content').next('.order-opc_status').show();
			$(this).next('.order-opc_block-content').slideUp('slow');
		}
		
		if ($('.order-opc_block-content').hasClass('selected'))
			$('.first_next_button').slideUp('fast');
		else
			$('.first_next_button').slideDown('fast');
	});
	
	$('.order-opc_next').click(function() {
		if (!$('#'+$(this).attr('name')).hasClass('selected'))
		{
			$('.first_next_button').slideUp('fast');
			$('.order-opc_block-status').attr('src', imgDir+'icon/more.gif');
			$('.order-opc_block-content').slideUp('slow');
			$('.order-opc_block-content').next('.order-opc_status').show();
			$('.order-opc_block-content').removeClass('selected');
			$('#'+$(this).attr('name')).addClass('selected');
			$('.selected').prev('h2').children('.order-opc_block-status').attr('src', imgDir+'icon/less.gif');
			$('.selected').next('.order-opc_status').hide();
			$('.selected').slideDown('slow');
		}
		return false;
	});
	
	// Order message update
	$('#message').blur(function() {
		$.ajax({
           type: 'POST',
           url: baseDir + 'order-opc.php',
           async: true,
           cache: false,
           dataType : "json",
           data: 'ajax=true&method=updateMessage&message=' + encodeURI($('#message').val()) + '&token=' + static_token ,
           success: function(jsonData)
           {
           		if (jsonData.hasError)
				{
					var errors = '';
					for(error in jsonData.errors)
						//IE6 bug fix
						if(error != 'indexOf')
							errors += jsonData.errors[error] + "\n";
					alert(errors);
				}
			},
           error: function(XMLHttpRequest, textStatus, errorThrown) {alert("TECHNICAL ERROR: unable to save adresses \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);}
       });
	});
	
	// Gift message update
	$('#gift_message').blur(function() {
		updateCarrierSelectionAndGift();
		resetPaymentModuleList();
	});
	
	// TOS
	$('#cgv').click(function() {
		if ($('#cgv:checked').length != 0)
			var checked = 1;
		else
			var checked = 0;
		
		$.ajax({
           type: 'POST',
           url: baseDir + 'order-opc.php',
           async: true,
           cache: false,
           dataType : "json",
           data: 'ajax=true&method=updateTOSStatus&checked=' + checked + '&token=' + static_token,
           success: function(json)
           {
           		updateTOSStatus();
           }
       });
		resetPaymentModuleList();
	});
	
});