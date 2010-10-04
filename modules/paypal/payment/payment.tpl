<p class="payment_module" height="">
	<a href="{$base_dir_ssl}modules/paypal/payment/submit.php" title="{l s='Pay with PayPal' mod='paypal'}">
			<img src="{$logo}" alt="{l s='Pay with PayPal' mod='paypalapi'}" style="float:left;" />
		<br />
		{if $integral}
			{l s='Pay with your account PayPal, credit card (CB, Visa, Mastercard...), or private credit card' mod='paypal'}
		{else}
			{l s='Pay with your account PayPal' mod='paypal'}
		{/if}
	<br style="clear:both" />
	</a>
</p>