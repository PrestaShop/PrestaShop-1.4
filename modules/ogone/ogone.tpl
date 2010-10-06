<p class="payment_module">
	<a href="javascript:document.ogone_form.submit();" title="{l s='Pay with Ogone' mod='ogone'}" style="height:48px">
		<span style="height:40px;width:86px;float:left"><img src="{$module_dir}ogone.gif" alt="{l s='Ogone logo' mod='ogone'}" /></span>
		<span style="width:350px;float:left;margin-left:10px">{l s='Pay with Ogone' mod='ogone'}<br />{l s='Pay safely and quickly on the next page with IDEAL / Mastercard / Visa / Paypal / Mister Cash / Bancontact.' mod='ogone'}</span>
		<div style="clear:both;height:0;line-height:0">&nbsp;</div>
	</a>
	<div style="clear:both;height:0;line-height:0">&nbsp;</div>
</p>
<form name="ogone_form" action="https://secure.ogone.com/ncol/{if $OGONE_MODE}prod{else}test{/if}/orderstandard_utf8.asp" method="post">
{foreach from=$ogone_params key=ogone_key item=ogone_value}
	<input type="hidden" name="{$ogone_key}" value="{$ogone_value}" />
{/foreach}
</form>




