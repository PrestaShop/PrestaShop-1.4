{if $status == 'ok'}
	<p>{l s='Your order on' mod='cheque'} <span class="bold">{$shop_name}</span> {l s='is complete.' mod='cheque'}
		<br /><br />
		{l s='Please send us a cheque with:' mod='cheque'}
		<br /><br />- {l s='an amount of' mod='cheque'} <span class="price">{$total_to_pay}</span>
		<br /><br />- {l s='payable to the order of' mod='cheque'} <span class="bold">{if $chequeName}{$chequeName}{else}___________{/if}</span>
		<br /><br />- {l s='mail to' mod='cheque'} <span class="bold">{if $chequeAddress}{$chequeAddress}{else}___________{/if}</span>
		<br /><br />{l s='An e-mail has been sent to you with this information.' mod='cheque'}
		<br /><br /><span class="bold">{l s='Your order will be sent as soon as we receive your payment.' mod='cheque'}</span>
		<br /><br />{l s='For any questions or for further information, please contact our' mod='cheque'} <a href="{$base_dir_ssl}contact-form.php">{l s='customer support' mod='cheque'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, you can contact our' mod='cheque'} 
		<a href="{$base_dir_ssl}contact-form.php">{l s='customer support' mod='cheque'}</a>.
	</p>
{/if}
