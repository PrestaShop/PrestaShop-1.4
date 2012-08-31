{literal}

$(document).ready( function() {

	$('#payment_paypal_express_checkout').click(function() {
		var nb = $('#quantity_wanted').val();
		var id = $('#idCombination').val();

		$('#paypal_payment_form input[name=quantity]').val(nb);
		$('#paypal_payment_form input[name=id_p_attr]').val(id);
		$('#paypal_payment_form').submit();
	});

	if ($('form[target="hss_iframe"]').length == 0) {
		return false;
	} else {
		var hostname = 'http://' + window.location.hostname + '{/literal}{$base_uri}{literal}';
		var modulePath = 'modules/paypal';
		var subFolder = '/integral_evolution';
		var fullPath = hostname + modulePath + subFolder;

		var confirmTimer = setInterval(getOrdersCount, 1000);
	}

	function getOrdersCount() {
		$.get(
			fullPath + '/confirm.php',
			{ id_cart: '{/literal}{$id_cart}{literal}' },
			function (data) {
				if (data && (data > 0)) {
					clearInterval(confirmTimer);
					window.location.replace(fullPath + '/submit.php?id_cart={/literal}{$id_cart}{literal}');
					$('p.payment_module, p.cart_navigation').hide();
				}
			}
		);
	}
});

{/literal}
