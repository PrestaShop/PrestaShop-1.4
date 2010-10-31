$(document).ready(function()
{
	$('.cart_quantity_up').unbind('click').click(function(){
			upQuantity($(this).attr('id'));
			return false;
		});
	$('.cart_quantity_down').unbind('click').click(function(){
			downQuantity($(this).attr('id'));
			return false;
		});
	$('.cart_quantity_delete').unbind('click').click(function(){
			deletProductFromSummary($(this).attr('id'));
			return false;
		});
});

function deletProductFromSummary(id){
	var productId = 0;
	var productAttributeId = 0;
	var ids = 0;
	ids = id.split('_');		
	productId = parseInt(ids[0]);
	if (typeof(ids[1]) != 'undefined')
		productAttributeId = parseInt(ids[1]);
	$.ajax({
       type: 'GET',
       url: baseDir + 'cart.php',
       async: true,
       cache: false,
       dataType : "json",
       data: 'ajax=true&delete&summary&id_product='+productId+'&ipa='+productAttributeId+'&token=' + static_token ,
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
    			if (parseInt(jsonData.summary.products.length) == 0)
    			{
	    			$('#center_column').children().each(function() {
					    if ($(this).attr('id') != 'emptyCartWarning' && $(this).attr('class') != 'breadcrumb' && $(this).attr('id') != 'cart_title')
					    {
					         $(this).fadeOut('slow', function () {
					                $(this).remove();
					          });
					    }          
					});
					$('#summary_products_label').remove();
					$('#emptyCartWarning').fadeIn('slow');
				}
				else
				{
					$('#product_'+ id).fadeOut('slow', function() {
						$(this).remove();
					});
				}
				ajaxCart.refresh();
				updateCartSummary(jsonData.summary);
				if (jsonData.carriers != null)
					updateCarrierList(jsonData.carriers);
    		}
    		
    			
    	},
       error: function(XMLHttpRequest, textStatus, errorThrown) {alert("TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);}
   });
	
}

function upQuantity(id){
	var productId = 0;
	var productAttributeId = 0;
	var ids = 0;
	ids = id.split('_');		
	productId = parseInt(ids[0]);
	if (typeof(ids[1]) != 'undefined')
		productAttributeId = parseInt(ids[1]);
	$.ajax({
       type: 'GET',
       url: baseDir + 'cart.php',
       async: true,
       cache: false,
       dataType : "json",
       data: 'ajax=true&add&summary&id_product='+productId+'&ipa='+productAttributeId+'&token=' + static_token ,
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
    			var val = $('input[name=quantity_'+ id +']').val();
				$('input[name=quantity_'+ id +']').val(parseInt(val) + 1);
    			updateCartSummary(jsonData.summary);
	    		if (jsonData.carriers != null)
					updateCarrierList(jsonData.carriers);

    		}
    	},
       error: function(XMLHttpRequest, textStatus, errorThrown) {alert("TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);}
   });
}

function downQuantity(id){
	var productId = 0;
	var productAttributeId = 0;
	var ids = 0;
	var val = $('input[name=quantity_'+ id +']').val();
	var newVal = val - 1;
	if (newVal > 0)
	{
		ids = id.split('_');		
		productId = parseInt(ids[0]);
		if (typeof(ids[1]) != 'undefined')
			productAttributeId = parseInt(ids[1]);
		$.ajax({
	       type: 'GET',
	       url: baseDir + 'cart.php',
	       async: true,
	       cache: false,
	       dataType : "json",
	       data: 'ajax=true&add&summary&id_product='+productId+'&ipa='+productAttributeId+'&op=down&token=' + static_token ,
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
	    			$('input[name=quantity_'+ id +']').val(newVal);
	    			updateCartSummary(jsonData.summary);
	    			if (jsonData.carriers != null)
						updateCarrierList(jsonData.carriers);
	    		}
	    	},
	       error: function(XMLHttpRequest, textStatus, errorThrown) {alert("TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);}
	   });

	}
	else
	{
		deletProductFromSummary(id);
	}
}

function updateCartSummary(json)
{
	// update products prices + discount
	var i;
	var nbrProducts = 0;
	for (i=0;i<json.products.length;i++)
	{
		key_for_blockcart = json.products[i].id_product+'_'+json.products[i].id_product_attribute;
		if (json.products[i].id_product_attribute == 0)
			key_for_blockcart = json.products[i].id_product;
		
		//console.log('#total_product_price_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute+' = '+formatCurrency(json.products[i].total_wt, currencyFormat, currencySign, currencyBlank));
		
		$('#cart_block_product_'+key_for_blockcart+' span.quantity').html(json.products[i].cart_quantity);	
		$('#cart_block_product_'+key_for_blockcart+' span.price').html(formatCurrency(json.products[i].total_wt, currencyFormat, currencySign, currencyBlank));
		$('#product_price_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute).html(formatCurrency(json.products[i].price_wt, currencyFormat, currencySign, currencyBlank));
		$('#total_product_price_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute).html(formatCurrency(json.products[i].total_wt, currencyFormat, currencySign, currencyBlank));
		nbrProducts += parseInt(json.products[i].cart_quantity);
	}
	for (i=0;i<json.discounts.length;i++)
	{
		$('#discount_price_'+json.discounts[i].id_discount).html(formatCurrency(json.discounts[i].value_real * -1, currencyFormat, currencySign, currencyBlank));
		$('#bloc_cart_voucher_'+json.discounts[i].id_discount+' td.price').html(formatCurrency(json.discounts[i].value_real * -1, currencyFormat, currencySign, currencyBlank));
	}
	
	// Block cart
	$('#cart_block_shipping_cost').html(formatCurrency(json.total_shipping, currencyFormat, currencySign, currencyBlank));
	$('#cart_block_wrapping_cost').html(formatCurrency(json.total_wrapping, currencyFormat, currencySign, currencyBlank));
	$('#cart_block_tax_cost').html(formatCurrency(json.total_tax, currencyFormat, currencySign, currencyBlank));
	$('#cart_block_total').html(formatCurrency(json.total_price, currencyFormat, currencySign, currencyBlank));
	$('.ajax_cart_quantity').html(nbrProducts);

	// cart summary
	$('#summary_products_quantity').html(nbrProducts);
	$('#total_product').html(formatCurrency(json.total_products_wt, currencyFormat, currencySign, currencyBlank));
	$('#total_price').html(formatCurrency(json.total_price, currencyFormat, currencySign, currencyBlank));
	$('#total_price_without_tax').html(formatCurrency(json.total_price_without_tax, currencyFormat, currencySign, currencyBlank));
	$('#total_tax').html(formatCurrency(json.total_tax, currencyFormat, currencySign, currencyBlank));
	$('#total_discount').html(formatCurrency(json.total_discounts, currencyFormat, currencySign, currencyBlank));
	$('#total_shipping').html(formatCurrency(json.total_shipping, currencyFormat, currencySign, currencyBlank));
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
}