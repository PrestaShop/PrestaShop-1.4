<div id="container_express_checkout" class="clearfix" style="float:right; text-align: right; padding: 0 40px;">
	<img id="payment_paypal_express_checkout" src="https://www.paypal.com/{$PayPal_lang_code}/i/btn/btn_xpressCheckout.gif" />
	<form id="paypal_payment_form" action="{$base_dir_ssl}modules/paypal/express_checkout/submit.php" title="{l s='Pay with PayPal' mod='paypal'}" method="post">

		<input type="hidden" name="id_product" value="{$smarty.get.id_product}" />
		
		<!-- Change dynamicaly when the form is submitted -->
		<input type="hidden" name="quantity" value="1" />
		<input type="hidden" name="id_p_attr" value="" />
		<input type="hidden" name="express_checkout" value="{$PayPal_payment_type}"/>
		<input type="hidden" name="current_shop_url" value="{$PayPal_current_shop_url}" />
        <input type="hidden" name="bn" value="{$PayPal_tracking_code}" />
</div>