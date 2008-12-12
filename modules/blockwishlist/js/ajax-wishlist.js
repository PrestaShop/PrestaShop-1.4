/**
 * Update WishList Cart by adding, deleting, updating objects
 *
 * @return void
 */
function WishlistCart(id, action, id_product, id_product_attribute, quantity)
{
	$.get(baseDir + 'modules/blockwishlist/cart.php',
	{ action: action,
	  id_product: id_product,
	  quantity: quantity,
	  token: static_token,
	  id_product_attribute: id_product_attribute },
	function(data)
	{
		$('#' + id).slideUp('normal');
		document.getElementById(id).innerHTML = data;
		$('#' + id).slideDown('normal');
	});
}

/**
 * Change customer default wishlist
 *
 * @return void
 */
function WishlistChangeDefault(id, id_wishlist)
{
	$.get(baseDir + 'modules/blockwishlist/cart.php',
	{ id_wishlist: id_wishlist,
	  token: static_token },
	function(data)
	{
		$('#' + id).slideUp('normal');
		document.getElementById(id).innerHTML = data;
		$('#' + id).slideDown('normal');
	});
}

/**
 * Buy Product
 *
 * @return void
 */
function WishlistBuyProduct(token, id_product, id_product_attribute, id_quantity, button, ajax)
{
	if(ajax)
		ajaxCart.add(id_product, id_product_attribute, false, button, 1, [token, id_quantity]);
	else
	{

		WishlistAddProductCart(token, id_product, id_product_attribute, id_quantity)
		document.forms['addtocart' + '_' + id_product  + '_' + id_product_attribute].method='POST';
		document.forms['addtocart' + '_' + id_product  + '_' + id_product_attribute].action=baseDir + 'cart.php';
		document.forms['addtocart' + '_' + id_product  + '_' + id_product_attribute].elements['token'].value = static_token;
		document.forms['addtocart' + '_' + id_product  + '_' + id_product_attribute].submit();
	}
	return (true);
}

function WishlistAddProductCart(token, id_product, id_product_attribute, id_quantity)
{
	if ($('#' + id_quantity).val() <= 0)
		return (false);
	$.get(baseDir + 'modules/blockwishlist/buywishlistproduct.php',
	{ 
		token: token,
	  static_token: static_token,
	  id_product: id_product,
	  id_product_attribute: id_product_attribute 
	 },
	function(data)
	{
		if (data)
			alert(data);
		else
		{
			$('#' + id_quantity).val($('#' + id_quantity).val() - 1);
		}
	});
	return (true);
}

/**
 * Show wishlist managment page
 *
 * @return void
 */
function WishlistManage(id, id_wishlist)
{
	$.get(baseDir + 'modules/blockwishlist/managewishlist.php',
	{ id_wishlist: id_wishlist,
		refresh: false
	 },
	function(data)
	{
		$('#' + id).hide();
		document.getElementById(id).innerHTML = data;
		$('#' + id).fadeIn('slow');
	});
}

/**
 * Show wishlist product managment page
 *
 * @return void
 */
function WishlistProductManage(id, action, id_wishlist, id_product, id_product_attribute, quantity, priority)
{
	$.get(baseDir + 'modules/blockwishlist/managewishlist.php',
	{ action: action,
	  id_wishlist: id_wishlist,
	  id_product: id_product,
	  id_product_attribute: id_product_attribute,
	  quantity: quantity,
	  priority: priority,
	  refresh: true },
	function(data)
	{
		if (action == 'delete')
			$('#wlp_' + id_product + '_' + id_product_attribute).fadeOut('fast');
		else if (action == 'update')
		{
			$('#wlp_' + id_product + '_' + id_product_attribute).fadeOut('fast');
			$('#wlp_' + id_product + '_' + id_product_attribute).fadeIn('fast');
		}
	});
}

/**
 * Delete wishlist
 *
 * @return boolean succeed
 */
function WishlistDelete(id, id_wishlist, msg)
{
	var res = confirm(msg);
	if (res == false)
		return (false);
	$.get(baseDir + 'modules/blockwishlist/mywishlist.php',
	{ deleted: '',
	  id_wishlist: id_wishlist },
	function(data)
	{
		$('#' + id).fadeOut('slow');
	});
}

/**
 * Hide/Show bought product
 *
 * @return void
 */
function WishlistVisibility(bought_class, id_button)
{
	if ($('#hide' + id_button).css('display') == 'none')
	{
		$('.' + bought_class).slideDown('fast');
		$('#show' + id_button).hide();
		$('#hide' + id_button).fadeIn('fast');
	}
	else
	{
		$('.' + bought_class).slideUp('fast');
		$('#hide' + id_button).hide();
		$('#show' + id_button).fadeIn('fast');
	}
}

/**
 * Send wishlist by email
 *
 * @return void
 */
function WishlistSend(id, id_wishlist, id_email)
{
	$.post(baseDir + 'modules/blockwishlist/sendwishlist.php',
	{ token: static_token,
	  id_wishlist: id_wishlist,
	  email1: $('#' + id_email + '1').val(),
	  email2: $('#' + id_email + '2').val(),
	  email3: $('#' + id_email + '3').val(),
	  email4: $('#' + id_email + '4').val(),
	  email5: $('#' + id_email + '5').val(),
	  email6: $('#' + id_email + '6').val(),
	  email7: $('#' + id_email + '7').val(),
	  email8: $('#' + id_email + '8').val(),
	  email9: $('#' + id_email + '9').val(),
	  email10: $('#' + id_email + '10').val() },
	function(data)
	{
		if (data)
			alert(data);
		else
			WishlistVisibility(id, 'hideSendWishlist');
	});
}
