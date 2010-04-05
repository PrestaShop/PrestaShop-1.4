<!-- MODULE Loyalty -->
<p id="loyalty">
	<img src="{$module_template_dir}loyalty.gif" alt="{l s='loyalty' mod='loyalty'}" class="icon" />{if $points > 0}{l s='By check out this shopping cart you can collect up to' mod='loyalty'} <b>{$points} {if $points > 1}{l s='loyalty points' mod='loyalty'}{else}{l s='loyalty point' mod='loyalty'}{/if}</b> {l s='that can be converted into a voucher of' mod='loyalty'} {convertPrice price=$voucher}.{else}{l s='Add some products to your shopping cart to collect some loyalty points.' mod='loyalty'}{/if}
</p>
<!-- END : MODULE Loyalty -->