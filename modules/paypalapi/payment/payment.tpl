<p class="payment_module" height="">
	<a href="{$base_dir_ssl}modules/paypalapi/payment/submit.php" title="{l s='Pay with PayPal' mod='paypalapi'}">
			<img src="{$logo}" alt="{l s='Pay with PayPal' mod='paypalapi'}" style="float:left;" />
		<br />
		{if $integral}
			{l s='Pay with your account PayPal, credit card (CB, Visa, Mastercard...), or private credit card' mod='paypalapi'}
		{else}
			{l s='Pay with your account PayPal' mod='paypalapi'}
		{/if}
	<br style="clear:both" />
	</a>
</p>