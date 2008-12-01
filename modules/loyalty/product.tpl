<p id="loyalty" class="align_justify">
	<img src="{$module_template_dir}loyalty.gif" alt="{l s='Loyalty program' mod='loyalty'}" />
{if $points}
	{l s='By buying this product you can collect up to' mod='loyalty'} {$points} {l s='reward points as a voucher of' mod='loyalty'} {convertPrice price=$voucher}.
{else}
	{l s='No reward points for this product.' mod='loyalty'}
{/if}
	<a href="{$module_template_dir}loyalty-program.php" title="{l s='Loyalty program' mod='loyalty'}">{l s='Your reward points.' mod='loyalty'}</a>
</p>
<br class="clear" />