<br />
<fieldset style="width:400px;">
	<legend><img src="{$base_url}modules/{$module_name}/logo.gif" alt="" />{l s='PayPal Validation' m='paypal'}</legend>
	<p><b>{l s='Information:' m='paypal'}</b> {if $order_state == $authorization}{l s='Pending Capture - No shipping' m='paypal'}{else}{l s='Pending Payment - No shipping' m='paypal'}{/if}</p>
	<form method="post" action="{$smarty.server.REQUEST_URI|escape:htmlall}">
		<input type="hidden" name="id_order" value="{$params.id_order}" />
		<p class="center"><input type="submit" class="button" name="submitPayPalValidation" value="{l s='Get payment status' m='paypal'}" /></p>
	</form>