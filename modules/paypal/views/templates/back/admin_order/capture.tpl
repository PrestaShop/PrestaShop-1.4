<br />
<fieldset style="width:400px;">
	<legend><img src="{$base_url}modules/{$module_name}/logo.gif" alt="" />{l s='PayPal Capture' m='paypal'}</legend>
	<p><b>{l s='Information:' m='paypal'}</b> {l s='Funds ready to be captured before shipping' m='paypal'}</p>
	<form method="post" action="{$smarty.server.REQUEST_URI|escape:htmlall}">
		<input type="hidden" name="id_order" value="{$params.id_order}" />
		<p class="center"><input type="submit" class="button" name="submitPayPalCapture" value="{l s='Get the money' m='paypal'}" /></p>
	</form>
