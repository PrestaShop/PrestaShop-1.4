$(document).ready( function() {

	// Submit the paypal expresss form
	$('#payment_paypal_express_checkout').click(function() {
		var nb = $('#quantity_wanted').val();
		var id = $('#idCombination').val();

		$('#paypal_express_checkout_form input[name=quantity]').val(nb)
		$('#paypal_express_checkout_form input[name=id_p_attr]').val(id);
		$('#paypal_express_checkout_form').submit();
	});

	if ($('form[target="hss_iframe"]').length == 0) {
		return false;
	} else {
		var hostname = 'http://' + window.location.hostname + '{$base_uri}';
		var modulePath = 'modules/paypal';
		var subFolder = '/integral_evolution';
		var fullPath = hostname + modulePath + subFolder;

		var confirmTimer = setInterval(getOrdersCount, 200);
	}

	function getOrdersCount() {
		$.get(
			fullPath + '/confirm.php',
			{ id_cart: '{$id_cart}' },
			function (data) {
				if (data && (data > 0)) {
					clearInterval(confirmTimer);
					window.location.replace(fullPath + '/submit.php?id_cart={$id_cart}');
					$('p.payment_module, p.cart_navigation').hide();
				}
			}
		);
	}

});
