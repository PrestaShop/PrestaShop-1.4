<br />
<fieldset style="width: 400px">
			<legend><img src="../img/admin/tab-customers.gif" />{$payment_name}</legend>
			{if $error}
				<span style="color: red; font-weight: bold;">{$error}</span>
			{/if}
			
			<p style="font-weight: bold;">{l s='Payment has not been accepted yet:' mod='cashticket'}</p>
			<p>
			<form action="{$action}" method="POST">
				<input type="text" name="ps_amount" size="8" value="{$amount}" />{$currency} 
				<input type="submit" class="button" name="acceptPayment" value="{l s='Accept Payment' mod='cashticket'}" />
				<input type="submit" class="button" name="releasePayment" value="{l s='Release amount' mod='cashticket'}" />				
			</form>
			</p>
</fieldset>
			
