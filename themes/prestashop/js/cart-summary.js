/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$(document).ready(function()
{
		$('.cart_quantity_up').unbind('click').click(function(){ upQuantity($(this).attr('id').replace('cart_quantity_up_', '')); return false;	});
		$('.cart_quantity_down').unbind('click').click(function(){ downQuantity($(this).attr('id').replace('cart_quantity_down_', '')); return false; });
		$('.cart_quantity_delete' ).unbind('click').click(function(){ deletProductFromSummary($(this).attr('id')); return false; });
		$('.cart_quantity_input').typeWatch({ highlight: true, wait: 600, captureLength: 0, callback: updateQty });
});

function updateQty(val)
{
	var id = $(this.el).attr('name');
	var exp = new RegExp("^[0-9]+$");

	if (exp.test(val) == true)
	{
		var hidden = $('input[name='+ id +'_hidden]').val();
		var input = $('input[name='+ id +']').val();
		var QtyToUp = parseInt(input) - parseInt(hidden);
		if (parseInt(QtyToUp) > 0)
			upQuantity(id.replace('quantity_', ''),QtyToUp);
		else if(parseInt(QtyToUp) < 0)
			downQuantity(id.replace('quantity_', ''),QtyToUp);
	}
	else
		$('input[name='+ id +']').val($('input[name='+ id +'_hidden]').val());
}

function deletProductFromSummary(id)
{
	var customizationId = 0;
	var productId = 0;
	var productAttributeId = 0;
	var ids = 0;
    id = id.replace(/^_/, '');
	ids = id.split('_');
	productId = parseInt(ids[0]);
	if (typeof(ids[1]) != 'undefined')
		productAttributeId = parseInt(ids[1]);
	if (typeof(ids[2]) != 'undefined' && ids[2] !== 'nocustom')
		customizationId = parseInt(ids[2]);
	$.ajax({
       type: 'POST',
	   headers: { "cache-control": "no-cache" },       
       url: baseDir + 'cart.php' + '?rand=' + new Date().getTime(),
       async: true,
       cache: false,
       dataType: 'json',
       data: 'ajax=true&delete=true&summary=true&id_product='+productId+'&ipa='+productAttributeId+ ( (customizationId != 0) ? '&id_customization='+customizationId : '') + '&token=' + static_token ,
       success: function(jsonData)
       {
       		if (jsonData.hasError)
    		{
    			var errors = '';
    			for(error in jsonData.errors)
    				//IE6 bug fix
    				if(error != 'indexOf')
    					errors += jsonData.errors[error] + "\n";
    		}
    		else
    		{
    			if (parseInt(jsonData.summary.products.length) == 0)
    			{
	    			$('#center_column').children().each(function() {
					    if ($(this).attr('id') != 'emptyCartWarning' && $(this).attr('class') != 'breadcrumb' && $(this).attr('id') != 'cart_title')
					         $(this).fadeOut('slow', function () {
					                $(this).remove();
					          });
					});
					$('#summary_products_label').remove();
					$('#emptyCartWarning').fadeIn('slow');
				}
				else
				{
					$('#product_'+ id).fadeOut('slow', function(){
						$(this).remove();
					});

					var exist = false;
					for (i=0;i<jsonData.summary.products.length;i++)
					{
						if (jsonData.summary.products[i].id_product == productId
							&& jsonData.summary.products[i].id_product_attribute == productAttributeId
							&& (parseInt(jsonData.summary.products[i].customization_quantity) > 0))
								exist = true;
					}

					// if all customization remove => delete product line
					if (!exist && customizationId)
 						$('#product_' + productId + '_' + productAttributeId).fadeOut('slow', function() {
 							$(this).remove();
							var line = $('#product_' + productId + '_' + productAttributeId + '_nocustom');
							if (line.length > 0)
							{
								line.find('input[name^=quantity_], .cart_quantity_down, .cart_quantity_up, .cart_quantity_delete').each(function(){
									if (typeof($(this).attr('name')) != 'undefined')
										$(this).attr('name', $(this).attr('name').replace(/nocustom/, ''));
									if (typeof($(this).attr('id')) != 'undefined')
										$(this).attr('id', $(this).attr('id').replace(/nocustom/, ''));
								});
								line.find('span[id^=total_product_price_]').each(function(){
									$(this).attr('id', $(this).attr('id').replace(/_nocustom/, ''));
								});
								line.attr('id', line.attr('id').replace(/nocustom/, ''));
							}
 							refreshOddRow();
 						});
				}
				updateCartSummary(jsonData.summary);
				updateCustomizedDatas(jsonData.customizedDatas);
				updateHookShoppingCart(jsonData.HOOK_SHOPPING_CART);
				updateHookShoppingCartExtra(jsonData.HOOK_SHOPPING_CART_EXTRA);
				if (jsonData.carriers != null)
				{
					if (typeof(orderProcess) != 'undefined')					
						updateCarrierSelectionAndGift();
					else				
						updateCarrierList(jsonData);
				}
    		}
       	},
       error: function(XMLHttpRequest, textStatus, errorThrown)
	   {
	   		alert("TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
	   }
   });
}

function upQuantity(id, qty)
{
	if(typeof(qty)=='undefined' || !qty)
		qty = 1;
	var customizationId = 0;
	var productId = 0;
	var productAttributeId = 0;
	var ids = 0;
    id = id.replace(/^_/, '');
	ids = id.split('_');
	productId = parseInt(ids[0]);
	if (typeof(ids[1]) != 'undefined')
		productAttributeId = parseInt(ids[1]);
	if (typeof(ids[2]) != 'undefined' && ids[2] !== 'nocustom')
		customizationId = parseInt(ids[2]);
	$.ajax({
       type: 'POST',
	   headers: { "cache-control": "no-cache" },       
       url: baseDir + 'cart.php' + '?rand=' + new Date().getTime(),
       async: true,
       cache: false,
       dataType: 'json',
       data: 'ajax=true&add=true&summary=true&id_product='+productId+'&ipa='+productAttributeId + ( (customizationId != 0) ? '&id_customization='+customizationId : '') + '&qty='+qty+'&token=' + static_token ,
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
    			$('input[name=quantity_'+ id +']').val($('input[name=quantity_'+ id +'_hidden]').val());
       		}
    		else
    		{
    			updateCartSummary(jsonData.summary);
    			updateCustomizedDatas(jsonData.customizedDatas);
    			updateHookShoppingCart(jsonData.HOOK_SHOPPING_CART);
				updateHookShoppingCartExtra(jsonData.HOOK_SHOPPING_CART_EXTRA);
	    		if (jsonData.carriers != null)
					updateCarrierList(jsonData);						
				// if we are in one page checkout
				if (typeof(orderProcess) != 'undefined')
					updateCarrierSelectionAndGift();
				else
					updateCartMinQuantity();					
    		}
    	},
       error: function(XMLHttpRequest, textStatus, errorThrown)
	   {
	   		alert("TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
	   }
   });
}

function downQuantity(id, qty)
{
	var val = $('input[name=quantity_'+id+']').val();
	var newVal = val;
	if(typeof(qty)=='undefined' || !qty)
	{
		qty = 1;
		newVal = val - 1;
	}
	else if (qty < 0)
    	qty = -qty;
	var customizationId = 0;
	var productId = 0;
	var productAttributeId = 0;
	var ids = 0;
	if (newVal > 0)
	{
		ids = id.replace(/^_/, '').split('_');
		productId = parseInt(ids[0]);
		if (typeof(ids[1]) != 'undefined')
			productAttributeId = parseInt(ids[1]);
		if (typeof(ids[2]) != 'undefined' && ids[2] !== 'nocustom')
			customizationId = parseInt(ids[2]);
		$.ajax({
	       type: 'POST',
		   headers: { "cache-control": "no-cache" },	       
	       url: baseDir + 'cart.php' + '?rand=' + new Date().getTime(),
	       async: true,
	       cache: false,
	       dataType: 'json',
	       data: 'ajax=true&add=true&summary=true&id_product='+productId+'&ipa='+productAttributeId+'&op=down' + ( (customizationId != 0) ? '&id_customization='+customizationId : '') + '&qty='+qty+'&token=' + static_token ,
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
	    			$('input[name=quantity_'+ id +']').val($('input[name=quantity_'+ id +'_hidden]').val());
	    		}
	    		else
	    		{
	    			updateCartSummary(jsonData.summary);
	    			updateCustomizedDatas(jsonData.customizedDatas);
	    			updateHookShoppingCart(jsonData.HOOK_SHOPPING_CART);
					updateHookShoppingCartExtra(jsonData.HOOK_SHOPPING_CART_EXTRA);
	    			if (jsonData.carriers != null)
						updateCarrierList(jsonData);						
					// if we are in one page checkout
					if (typeof(orderProcess) != 'undefined')
						updateCarrierSelectionAndGift();
					else
						updateCartMinQuantity();						
	    		}
	    	},
	       error: function(XMLHttpRequest, textStatus, errorThrown)
		   {
		   		alert("TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
		   }
	   });
	}
	else
		deletProductFromSummary(id);
}

function updateCartSummary(json)
{
	// Update products prices + discount
	var i;
	var nbrProducts = 0;

	if (typeof json == 'undefined')
		return;		

	for (i=0;i<json.products.length;i++)
	{
		var key_for_blockcart = json.products[i].id_product + '_'+ json.products[i].id_product_attribute;
		if (json.products[i].id_product_attribute == 0)
			key_for_blockcart = json.products[i].id_product;

		$('#cart_block_product_' + key_for_blockcart + ' span.quantity').html(json.products[i].cart_quantity);

		if (priceDisplayMethod != 0)
		{
    		$('#cart_block_product_' + key_for_blockcart + ' span.price').html(formatCurrency(json.products[i].total, currencyFormat, currencySign, currencyBlank));
			$('#product_price_' + json.products[i].id_product + '_' + json.products[i].id_product_attribute).html(formatCurrency(json.products[i].price, currencyFormat, currencySign, currencyBlank));
    		$('#total_product_price_' + json.products[i].id_product + '_' + json.products[i].id_product_attribute).html(formatCurrency(json.products[i].total, currencyFormat, currencySign, currencyBlank));
		}
		else
		{
    		$('#cart_block_product_' + key_for_blockcart + ' span.price').html(formatCurrency(json.products[i].total_wt, currencyFormat, currencySign, currencyBlank));
			$('#product_price_' + json.products[i].id_product + '_' + json.products[i].id_product_attribute).html(formatCurrency(json.products[i].price_wt, currencyFormat, currencySign, currencyBlank));
    		$('#total_product_price_' + json.products[i].id_product + '_' + json.products[i].id_product_attribute).html(formatCurrency(json.products[i].total_wt, currencyFormat, currencySign, currencyBlank));
		}
		
		nbrProducts += parseInt(json.products[i].cart_quantity);
		
		if(json.products[i].id_customization == null)
		{
			$('input[name=quantity_' + json.products[i].id_product + '_' + json.products[i].id_product_attribute +' ]').val(json.products[i].cart_quantity);
			$('input[name=quantity_' + json.products[i].id_product+'_' + json.products[i].id_product_attribute + '_hidden]').val(json.products[i].cart_quantity);
		}
		else // TODO : need elseif(is a custom product without custom) here or must have correct customization_quantity (and total) from json and not only first customization quantity
		{
            $('input[name=quantity_' + json.products[i].id_product + '_' + json.products[i].id_product_attribute + ((json.products[i].customizationQuantityTotal != json.products[i].cart_quantity)? '_nocustom' : '')  + ']').val(parseInt(json.products[i].cart_quantity) - parseInt($('#cart_quantity_custom_' + json.products[i].id_product + '_' + json.products[i].id_product_attribute).html()));
            $('input[name=quantity_' + json.products[i].id_product + '_' + json.products[i].id_product_attribute + ((json.products[i].customizationQuantityTotal != json.products[i].cart_quantity)? '_nocustom' : '')  + '_hidden]').val(parseInt(json.products[i].cart_quantity) - parseInt($('#cart_quantity_custom_' + json.products[i].id_product + '_' + json.products[i].id_product_attribute).html()));
		}

		// Show / hide quantity button if minimal quantity
		if (parseInt(json.products[i].minimal_quantity) == parseInt(json.products[i].cart_quantity) && json.products[i].minimal_quantity != 1)
			$('#cart_quantity_down_' + json.products[i].id_product + '_' + json.products[i].id_product_attribute + (json.products[i].id_customization != null ? '_'+json.products[i].id_customization : '')).fadeTo('slow',0.3);
		else
			$('#cart_quantity_down_' + json.products[i].id_product + '_' + json.products[i].id_product_attribute+(json.products[i].id_customization != null ? '_'+json.products[i].id_customization : '')).fadeTo('slow',1);
	}

	// Update discounts
	if (json.discounts.length == 0)
	{
		$('.cart_discount').each(function(){$(this).remove()});
		$('.cart_total_voucher').remove();
	}
	else
	{
		if (priceDisplayMethod != 0)
			$('#total_discount').html(formatCurrency(json.total_discounts_tax_exc, currencyFormat, currencySign, currencyBlank));
		else
			$('#total_discount').html(formatCurrency(json.total_discounts, currencyFormat, currencySign, currencyBlank));

		$('.cart_discount').each(function(){
			var idElmt = $(this).attr('id').replace('cart_discount_','');
			var toDelete = true;

			for (i=0;i<json.discounts.length;i++)
			{
				if (json.discounts[i].id_discount == idElmt)
				{
					if (json.discounts[i].value_real != '!')
					{
						if (priceDisplayMethod != 0)
							$('#cart_discount_' + idElmt + ' td.cart_discount_price span.price-discount').html(formatCurrency(json.discounts[i].value_tax_exc * -1, currencyFormat, currencySign, currencyBlank));
						else
							$('#cart_discount_' + idElmt + ' td.cart_discount_price span.price-discount').html(formatCurrency(json.discounts[i].value_real * -1, currencyFormat, currencySign, currencyBlank));

					}
					toDelete = false;
				}
			}
			if (toDelete)
				$('#cart_discount_' + idElmt + ', #cart_total_voucher').fadeTo('fast', 0, function(){ $(this).remove(); });
		});
	}
	// Block cart
	var cart_total_dom = '#total_product';
	if(typeof(ajaxCart) == 'undefined')
		cart_total_dom += ',.ajax_cart_total';
	if (priceDisplayMethod != 0)
	{
		$('#cart_block_shipping_cost').html(formatCurrency(json.total_shipping_tax_exc, currencyFormat, currencySign, currencyBlank));
		$('#cart_block_wrapping_cost').html(formatCurrency(json.total_wrapping_tax_exc, currencyFormat, currencySign, currencyBlank));
		$('#cart_block_total').html(formatCurrency(json.total_price_without_tax, currencyFormat, currencySign, currencyBlank));
		$(cart_total_dom).html(formatCurrency(json.total_products, currencyFormat, currencySign, currencyBlank));    	
	}
	else
	{
		$('#cart_block_shipping_cost').html(formatCurrency(json.total_shipping, currencyFormat, currencySign, currencyBlank));
		$('#cart_block_wrapping_cost').html(formatCurrency(json.total_wrapping, currencyFormat, currencySign, currencyBlank));
		$('#cart_block_total').html(formatCurrency(json.total_price, currencyFormat, currencySign, currencyBlank));
		$(cart_total_dom).html(formatCurrency(json.total_products_wt, currencyFormat, currencySign, currencyBlank));    	
	}

	$('#cart_block_tax_cost').html(formatCurrency(json.total_tax, currencyFormat, currencySign, currencyBlank));
	$('.ajax_cart_quantity').html(nbrProducts);

	// Cart summary
	$('#summary_products_quantity').html(nbrProducts+' '+(nbrProducts > 1 ? txtProducts : txtProduct));       
	$('#total_price').html(formatCurrency(json.total_price, currencyFormat, currencySign, currencyBlank));
	$('#total_price_without_tax').html(formatCurrency(json.total_price_without_tax, currencyFormat, currencySign, currencyBlank));
	$('#total_tax').html(formatCurrency(json.total_tax, currencyFormat, currencySign, currencyBlank));

	if (json.total_shipping <= 0)
		$('.cart_total_delivery').fadeOut();
	else
	{
		$('.cart_total_delivery').fadeIn();
		if (priceDisplayMethod != 0)
		    $('#total_shipping').html(formatCurrency(json.total_shipping_tax_exc, currencyFormat, currencySign, currencyBlank));
		else
		    $('#total_shipping').html(formatCurrency(json.total_shipping, currencyFormat, currencySign, currencyBlank));
	}

	if (json.free_ship > 0 && !json.is_virtual_cart)
	{
		$('.cart_free_shipping').fadeIn();
		$('#free_shipping').html(formatCurrency(json.free_ship, currencyFormat, currencySign, currencyBlank));
	}
	else
		$('.cart_free_shipping').hide();

	if (json.total_wrapping > 0)
	{
		$('#total_wrapping').html(formatCurrency(json.total_wrapping, currencyFormat, currencySign, currencyBlank));
		$('#total_wrapping').parent().show();
	}
	else
	{
		$('#total_wrapping').html(formatCurrency(json.total_wrapping, currencyFormat, currencySign, currencyBlank));
		$('#total_wrapping').parent().hide();
	}
	if (window.ajaxCart !== undefined)
		ajaxCart.refresh();
}

function updateCustomizedDatas(json)
{
	for(i in json)
		for(j in json[i])
        {
            var total = 0;
			for(k in json[i][j])
			{
				$('input[name=quantity_'+i+'_'+j+'_'+k+'_hidden]').val(json[i][j][k]['quantity']);
				$('input[name=quantity_'+i+'_'+j+'_'+k+']').val(json[i][j][k]['quantity']);
                total += parseInt(json[i][j][k]['quantity']);
			}
            if (total > 0)
                $('#cart_quantity_custom_' + i + '_' + j).html(parseInt(total));
        }
}

function updateHookShoppingCart(html)
{
	$('#HOOK_SHOPPING_CART').html(html);
}

function updateHookShoppingCartExtra(html)
{
	$('#HOOK_SHOPPING_CART_EXTRA').html(html);
}

function updateCartMinQuantity()
{
	/* Display errors only if necessary */
	$('.error').hide();

	$.ajax({
       type: 'POST',
	   headers: { "cache-control": "no-cache" },       
       url: baseDir + 'order.php' + '?rand=' + new Date().getTime(),
       async: false,
       cache: false,
       dataType : "json",
       data: 'ajax=true&checkMinQuantity=true&token=' + static_token ,
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
    			if (jsonData.data)
    			{
    				var html = $(jsonData.data);
    				// Hide 
    				html.hide();
    				$('#order_step').after(html);
    				html.slideDown('slow');
    			}
    			else
    				$('.error').slideUp('slow', function(){
    					$(this).remove();
    				});
    	},
		error: function(XMLHttpRequest, textStatus, errorThrown)
		{
			alert("TECHNICAL ERROR: unable to check minimal quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
		}
   });	
}