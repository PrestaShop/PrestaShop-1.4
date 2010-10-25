{if $status == 'ok'}
	<p>{l s='Your order on' mod='ogone'} <span class="bold">{$shop_name}</span> {l s='is complete.' mod='ogone'}
		<br /><br /><span class="bold">{l s='Your order will be sent as soon as possible.' mod='ogone'}</span>
		<br /><br />{l s='For any questions or for further information, please contact our' mod='ogone'} <a href="{$link->getPageLink('contact-form.php', true)}">{l s='customer support' mod='ogone'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, you can contact our' mod='ogone'} 
		<a href="{$link->getPageLink('contact-form.php', true)}">{l s='customer support' mod='ogone'}</a>.
	</p>
{/if}
