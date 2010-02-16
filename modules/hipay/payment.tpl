<p class="payment_module">
	<a href="{$this_path_ssl}redirect.php" title="{l s='Pay with Hipay' mod='hipay'}">
		<img src="{$this_path}hipay.png" alt="{l s='Pay with Hipay' mod='hipay'}" />
		{l s='Pay with Hipay' mod='hipay'} {if !$hipay_prod}{l s='(sandbox / test mode)' mod='hipay'}{/if}
	</a>
</p>