{literal}
jQuery.noConflict(true);

jQuery(document).ready( function() {

	jQuery('#payment_paypal_express_checkout').click(function() {
		var nb = jQuery('#quantity_wanted').val();
		var id = jQuery('#idCombination').val();

		jQuery('#paypal_payment_form input[name=quantity]').val(nb);
		jQuery('#paypal_payment_form input[name=id_p_attr]').val(id);
		jQuery('#paypal_payment_form').submit();
	});

	if (jQuery('form[target="hss_iframe"]').length == 0) {
		return false;
	} else {
		var hostname = 'http://' + window.location.hostname + '{/literal}{$base_uri}{literal}';
		var modulePath = 'modules/paypal';
		var subFolder = '/integral_evolution';
		var fullPath = hostname + modulePath + subFolder;

		var confirmTimer = setInterval(getOrdersCount, 1000);
	}

	function getOrdersCount() {
		jQuery.get(
			fullPath + '/confirm.php',
			{ id_cart: '{/literal}{$id_cart}{literal}' },
			function (data) {
				if (data && (data > 0)) {
					clearInterval(confirmTimer);
					window.location.replace(fullPath + '/submit.php?id_cart={/literal}{$id_cart}{literal}');
					jQuery('p.payment_module, p.cart_navigation').hide();
				}
			}
		);
	}
});
{/literal}