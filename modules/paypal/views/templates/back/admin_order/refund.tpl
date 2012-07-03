<br />
<fieldset style="width:400px;">
	<legend><img src="{$base_url}modules/{$module_name}/logo.gif" alt="" />{l s='PayPal Refund' m='paypal'}</legend>
	<p><b>{l s='Information:' m='paypal'}</b> {l s='Payment accepted' m='paypal'}</p>
	<p><b>{l s='Information:' m='paypal'}</b> {l s='When you refund a product, a partial refund is made unless you select "Generate a voucher".' m='paypal'}</p>
	<form method="post" action="{$smarty.server.REQUEST_URI|escape:htmlall}">
		<input type="hidden" name="id_order" value="{$params.id_order}" />
		<p class="center">
			<input type="submit" class="button" name="submitPayPalRefund" value="{l s='Refund total transaction' m='paypal'}" onclick="if (!confirm('{l s='Are you sure?' m='paypal'}'))return false;" />
		</p>
	</form>
