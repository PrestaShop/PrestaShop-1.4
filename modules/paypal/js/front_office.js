{literal}

var js_paypal = jQuery.noConflict(true);

js_paypal(document).ready( function() {

	js_paypal('#payment_paypal_express_checkout').click(function() {
		var nb = js_paypal('#quantity_wanted').val();
		var id = js_paypal('#idCombination').val();

		js_paypal('#paypal_payment_form input[name=quantity]').val(nb);
		js_paypal('#paypal_payment_form input[name=id_p_attr]').val(id);
		js_paypal('#paypal_payment_form').submit();
	});

	if (js_paypal('form[target="hss_iframe"]').length == 0) {
		return false;
	} else {
		var hostname = 'http://' + window.location.hostname + '{/literal}{$base_uri}{literal}';
		var modulePath = 'modules/paypal';
		var subFolder = '/integral_evolution';
		var fullPath = hostname + modulePath + subFolder;

		var confirmTimer = setInterval(getOrdersCount, 1000);
	}

	function getOrdersCount() {
		js_paypal.get(
			fullPath + '/confirm.php',
			{ id_cart: '{/literal}{$id_cart}{literal}' },
			function (data) {
				if (data && (data > 0)) {
					clearInterval(confirmTimer);
					window.location.replace(fullPath + '/submit.php?id_cart={/literal}{$id_cart}{literal}');
					js_paypal('p.payment_module, p.cart_navigation').hide();
				}
			}
		);
	}
});

{/literal}

