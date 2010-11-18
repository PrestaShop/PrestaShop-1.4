$(document).ready(function()
{
	$('.cart_quantity_up').unbind('click').click(function(){
			upQuantity($(this).attr('id').replace('cart_quantity_up_', ''));
			return false;
		});
	$('.cart_quantity_down').unbind('click').click(function(){
			downQuantity($(this).attr('id').replace('cart_quantity_down_', ''));
			return false;
		});
	$('.cart_quantity_delete' ).unbind('click').click(function(){
			deletProductFromSummary($(this).attr('id'));
			return false;
		});

	$(".cart_quantity_input").typeWatch({ 
		highlight:true,
		wait:600,
		captureLength:0,
		callback:updateQty
	});

});

function updateQty(val) {
		
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


function deletProductFromSummary(id){
	
	var customizationId = 0;
	var productId = 0;
	var productAttributeId = 0;
	var ids = 0;
	ids = id.split('_');		
	productId = parseInt(ids[0]);
	if (typeof(ids[1]) != 'undefined')
		productAttributeId = parseInt(ids[1]);
	if (typeof(ids[2]) != 'undefined')
		customizationId = parseInt(ids[2]);
	$.ajax({
       type: 'GET',
       url: baseDir + 'cart.php',
       async: true,
       cache: false,
       dataType : "json",
       data: 'ajax=true&delete&summary&id_product='+productId+'&ipa='+productAttributeId+ ( (customizationId != 0) ? '&id_customization='+customizationId : '') + '&token=' + static_token ,
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
					
					var exist = false;
					for (i=0;i<jsonData.summary.products.length;i++)
						if (jsonData.summary.products[i].id_product == productId)
							exist = true;
				
					// if all customization remove => delete product line
					if (!exist)
						$('#product_'+ productId+'_'+productAttributeId).fadeOut('slow', function() {
							$(this).remove();
						});
				}
				ajaxCart.refresh();
				updateCartSummary(jsonData.summary);
				updateCustomizedDatas(jsonData.customizedDatas);
				if (jsonData.carriers != null)
					updateCarrierList(jsonData.carriers);
				
    		}
       	},
       error: function(XMLHttpRequest, textStatus, errorThrown) {alert("TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);}
   });
	
}

function upQuantity(id, qty){

	if(typeof(qty)=='undefined' || !qty)
		qty = 1;
	var customizationId = 0;
	var productId = 0;
	var productAttributeId = 0;
	var ids = 0;
	ids = id.split('_');		
	productId = parseInt(ids[0]);
	if (typeof(ids[1]) != 'undefined')
		productAttributeId = parseInt(ids[1]);
	if (typeof(ids[2]) != 'undefined')
		customizationId = parseInt(ids[2]);
	$.ajax({
       type: 'GET',
       url: baseDir + 'cart.php',
       async: true,
       cache: false,
       dataType : "json",
       data: 'ajax=true&add&summary&id_product='+productId+'&ipa='+productAttributeId + ( (customizationId != 0) ? '&id_customization='+customizationId : '') + '&qty='+qty+'&token=' + static_token ,
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
    			updateCustomizedDatas(jsonData.customizedDatas);
    			updateCartSummary(jsonData.summary);
	    		if (jsonData.carriers != null)
					updateCarrierList(jsonData.carriers);
    		}
    	},
       error: function(XMLHttpRequest, textStatus, errorThrown) {alert("TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);}
   });
}

function downQuantity(id, qty){
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
		ids = id.split('_');		
		productId = parseInt(ids[0]);
		if (typeof(ids[1]) != 'undefined')
			productAttributeId = parseInt(ids[1]);
		if (typeof(ids[2]) != 'undefined')
			customizationId = parseInt(ids[2]);
		$.ajax({
	       type: 'GET',
	       url: baseDir + 'cart.php',
	       async: true,
	       cache: false,
	       dataType : "json",
	       data: 'ajax=true&add&summary&id_product='+productId+'&ipa='+productAttributeId+'&op=down' + ( (customizationId != 0) ? '&id_customization='+customizationId : '') + '&qty='+qty+'&token=' + static_token ,
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
	    			updateCustomizedDatas(jsonData.customizedDatas);
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
							
		$('#cart_block_product_'+key_for_blockcart+' span.quantity').html(json.products[i].cart_quantity);	
		$('#cart_block_product_'+key_for_blockcart+' span.price').html(formatCurrency(json.products[i].total_wt, currencyFormat, currencySign, currencyBlank));
		$('#product_price_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute).html(formatCurrency(json.products[i].price_wt, currencyFormat, currencySign, currencyBlank));
		$('#total_product_price_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute).html(formatCurrency(json.products[i].total_wt, currencyFormat, currencySign, currencyBlank));
		nbrProducts += parseInt(json.products[i].cart_quantity);
		
		if(json.products[i].id_customization == null)
		{
		$('input[name=quantity_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute+(json.products[i].id_customization != null ? '_'+json.products[i].id_customization : '')+']').val(json.products[i].cart_quantity);
		$('input[name=quantity_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute+(json.products[i].id_customization != null ? '_'+json.products[i].id_customization : '')+'_hidden]').val(json.products[i].cart_quantity);
		}
		else
		{
			$('#cart_quantity_custom_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute).html(json.products[i].cart_quantity);
		}
		
		//show / hidden	quantity button if minimal quantity				
		if (parseInt(json.products[i].minimal_quantity) == parseInt(json.products[i].cart_quantity) && json.products[i].minimal_quantity != 1)
			$('#cart_quantity_down_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute+(json.products[i].id_customization != null ? '_'+json.products[i].id_customization : '')).fadeTo('slow',0.3);
		else
			$('#cart_quantity_down_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute+(json.products[i].id_customization != null ? '_'+json.products[i].id_customization : '')).fadeTo('slow',1);


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

function updateCustomizedDatas(json)
{
	for(i in json)
		for(j in json[i])
			for(k in json[i][j])
			{
				$('input[name=quantity_'+i+'_'+j+'_'+k+'_hidden]').val(json[i][j][k]['quantity']);
				$('input[name=quantity_'+i+'_'+j+'_'+k+']').val(json[i][j][k]['quantity']);
			}
}
