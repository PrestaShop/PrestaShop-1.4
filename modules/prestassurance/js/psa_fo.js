var alertClicked = false;
var hrefLink = '';

(function(){
    // remove layerX and layerY
    var all = $.event.props,
        len = all.length,
        res = [];
    while (len--) {
      var el = all[len];
      if (el != 'layerX' && el != 'layerY') res.push(el);
    }
    $.event.props = res;
}());


$(document).ready(function()
{
	if (typeof(ajaxCartActive) != 'undefined' && ajaxCartActive)
	{
		overrideButtonsInThePageTmp = ajaxCart.overrideButtonsInThePage;
		ajaxCart.overrideButtonsInThePage = function () {
			overrideButtonsInThePageTmp();
			clean();
			refresh();
		}
	}	
	else
	{
		clean();
		refresh();
	}
	bindDeleteProductLink();
	alertNoSelectedInsurance();
	$('.cart_last_product').hide();
});

function refresh()
{
	$.ajax({
		type: 'GET',
		url: baseDir + 'modules/prestassurance/psaCart.php',
		async: false,
		cache: false,
		dataType : "html",
		data: 'refreshPsaCart=true&ajax=true' ,
		success: function(htmlData)
		{
			$('body').append(htmlData);
			customizeBlockCart();
			if (order_page)
				customizeCartSummary();
		},
		error: function(XMLHttpRequest, textStatus, errorThrown)
		{
			alert('TECHNICAL ERROR\nDetails:\nError thrown: ' + XMLHttpRequest + '\n' + 'Text status: ' + textStatus);
		}
	});
}

function clean()
{
	//clean html if psa_cart exist
	if ($('#psa_block_cart').length)
		$('#psa_block_cart').each( function () {
			$(this).remove();
	});
	
	//clean html if psa_cart_summary exist
	if ($('#psa_cart_summary').length)
		$('#psa_cart_summary').each( function () {
			$(this).remove();
	});
}

function hideInsuranceProductOnBlockCart()
{
	//remove old insurance line
	$('.block_content dt[id^=psa]').each( function () {
		$(this).remove();
	});
	
	//remove product insurance line
	$("[id^=cart_block_product_"+id_psa_product+"_]").each( function () { 
		$(this).remove();
	});
	
	//remove product combination insurance line
	$("[id^=cart_block_combination_of_"+id_psa_product+"_]").each( function () { 
		$(this).remove();
	});
}

function hideInsuranceProductOnSummary()
{
	$("[id^=product_"+id_psa_product+"_]").each( function () {
		$(this).remove();
	});
	
	$("#cart_summary tr[id^=psa_]").each( function () {
		$(this).remove();
	});
}

function customizeBlockCart()
{
	//if block cart is not present skip this customization
	if (!$('#cart_block').length)
		return;
	
	hideInsuranceProductOnBlockCart();
	
	//replace number of product in cart without psa products
	if ($('.ajax_cart_quantity').length && typeof(cartQty) != 'undefined')
		$('.ajax_cart_quantity').html(cartQty);

	//bind all insurance for each products
	$('#psa_block_cart dt').each( function () {
		id_product = $(this).attr('id').replace('psa_', '');
		if ($('dd#cart_block_combination_of_'+id_product).length)
		{
			$('dd#cart_block_combination_of_'+id_product).after(this);
		}
		else
			$('dt#cart_block_product_'+id_product).after(this);
	});
	
	$('#psa_block_cart').remove();

}

function customizeCartSummary()
{		
	hideInsuranceProductOnSummary();
		
	//bind all insurance for each products
	$('#psa_cart_summary tr').each( function () {
		id_product = $(this).attr('id').replace('psa_', '');
		$('#product_'+id_product).after(this);
	});
	
	$('#psa_cart_summary').remove();
	
}

function bindDeleteProductLink()
{
	//for block cart
	$('.ajax_cart_block_remove_link').each(function() {
		id = $(this).parent().parent('dt').attr('id').replace('cart_block_product_', '').split('_');

		if (id[0] == id_psa_product)
		{
			$(this).attr('href', '#');
			$(this).click(function(){ 
				alert('TODO delete product in cart and in psa cart');
				return false;
			});
		}
	});
	
	//for cart summary
	$('.cart_quantity_delete').each(function() {
		
	});
}

function alertNoSelectedInsurance()
{
	if (!psa_customer_alert)
		return;
	
	if (!$('#cart_summary').length) //check if the current page is cart summary 
		return;
	
	if (alertClicked)//check if box has already open
		return;
	
	//save current click
	hrefLink = $('.cart_navigation').children('a:first').attr('href');
		
	if ($('.cart_navigation').children('a:first').length)
	{
		$('a#psa_customer_alert').fancybox({
			autoDimensions: true,
			margin : 0,
			onClosed: function(elt) {
				alertClicked = true;
				updatePopIn(true);
			}
		});
		
		$('.cart_navigation').children('a:first').click(function ()
		{
			updatePopIn(true);
			if (alertClicked)
				document.location.href = hrefLink;
			else
				$('a#psa_customer_alert').trigger('click');
			return false;
		});
	}
}

function updatePopIn(clicked)
{
	$.ajax({
		type: 'GET',
		url: baseDir + 'modules/prestassurance/ajax_fo.php',
		async: false,
		cache: false,
		data: 'token='+psa_token+'&updatePopIn=true&ajax=true&clicked='+clicked 
	});

}