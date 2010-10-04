{if $display_mode == 0}
	<form action="https://www.moneybookers.com/app/payment.pl" method="post">
	<p class="payment_module" style="border: 1px solid #595A5E; display: block; text-decoration: none; margin-left: 7px; padding: 0.6em;">
		<input type="hidden" name="pay_to_email" value="{$pay_to_email}" />
		<input type="hidden" name="recipient_description" value="{$recipient_description}" />
		<input type="hidden" name="transaction_id" value="{$transaction_id}" />
		<input type="hidden" name="return_url" value="{$return_url}" />
		<input type="hidden" name="return_url_text" value="{$return_url}" />
		<input type="hidden" name="cancel_url" value="{$return_url}" />
		<input type="hidden" name="status_url" value="{$status_url}" />
		<input type="hidden" name="status_url2" value="{$pay_to_email}" />
		<input type="hidden" name="language" value="{$language}" />
		<input type="hidden" name="hide_login" value="{$hide_login}" />
		<input type="hidden" name="pay_from_email" value="{$pay_from_email}" />
		<input type="hidden" name="firstname" value="{$firstname}" />
		<input type="hidden" name="lastname" value="{$lastname}" />
		{if (!empty($date_of_birth))}<input type="hidden" name="date_of_birth" value="{$date_of_birth}" />{/if}
		<input type="hidden" name="address" value="{$address}" />
		{if (!empty($address2))}<input type="hidden" name="address2" value="{$address2}" />{/if}
		{if (!empty($phone_number))}<input type="hidden" name="phone_number" value="{$phone_number}" />{/if}
		<input type="hidden" name="postal_code" value="{$postal_code}" />
		<input type="hidden" name="city" value="{$city}" />
		{if (!empty($state))}<input type="hidden" name="state" value="{$state}" />{/if}
		<input type="hidden" name="country" value="{$country}" />
		<input type="hidden" name="amount" value="{$amount}" />
		<input type="hidden" name="currency" value="{$currency}" />
		<input type="hidden" name="amount2_description" value="{$amount2_description}" />
		<input type="hidden" name="amount2" value="{$amount2}" />
		<input type="hidden" name="amount3_description" value="{$amount3_description}" />
		<input type="hidden" name="amount3" value="{$amount3}" />
		<input type="hidden" name="amount4_description" value="{$amount4_description}" />
		<input type="hidden" name="amount4" value="{$amount4}" />
		<input type="hidden" name="return_url_target" value="2">
		<input type="hidden" name="cancel_url_target" value="2">
		<input type="hidden" class="payment_methods" name="payment_methods" value="ACC">
		<input type="hidden" name="merchant_fields" value="platform">
		<input type="hidden" name="platform" value="prestashop">
		{foreach from=$inter item=i}
			<input type="image" src="{$base_dir}modules/moneybookers/logos/international/{$inter_logos[$i].file}.gif" value="{$inter_logos[$i].code}" name="Submit" style="margin-right: 10px; border: none;" onclick="$('input.payment_methods').val($(this).val());" />
		{/foreach}
		{foreach from=$local item=i}
			<input type="image" src="{$base_dir}modules/moneybookers/logos/local/{$local_logos[$i].file}.gif" value="{$local_logos[$i].code}" name="Submit" style="margin-right: 10px; border: none;" onclick="$('input.payment_methods').val($(this).val());" />
		{/foreach}
	</p>
	</form>
	<div class="clear"></div>
{else}
	{foreach from=$inter item=i}
	<form action="https://www.moneybookers.com/app/payment.pl" method="post">
	<p class="payment_module" style="border: 1px solid #595A5E; display: block; text-decoration: none; height: 50px; margin-left: 7px; padding: 0.6em;">
		<input type="hidden" name="pay_to_email" value="{$pay_to_email}" />
		<input type="hidden" name="recipient_description" value="{$recipient_description}" />
		<input type="hidden" name="transaction_id" value="{$transaction_id}" />
		<input type="hidden" name="return_url" value="{$return_url}" />
		<input type="hidden" name="return_url_text" value="{$return_url}" />
		<input type="hidden" name="cancel_url" value="{$return_url}" />
		<input type="hidden" name="status_url" value="{$status_url}" />
		<input type="hidden" name="status_url2" value="{$pay_to_email}" />
		<input type="hidden" name="language" value="{$language}" />
		<input type="hidden" name="hide_login" value="{$hide_login}" />
		<input type="hidden" name="pay_from_email" value="{$pay_from_email}" />
		<input type="hidden" name="firstname" value="{$firstname}" />
		<input type="hidden" name="lastname" value="{$lastname}" />
		{if (!empty($date_of_birth))}<input type="hidden" name="date_of_birth" value="{$date_of_birth}" />{/if}
		<input type="hidden" name="address" value="{$address}" />
		{if (!empty($address2))}<input type="hidden" name="address2" value="{$address2}" />{/if}
		{if (!empty($phone_number))}<input type="hidden" name="phone_number" value="{$phone_number}" />{/if}
		<input type="hidden" name="postal_code" value="{$postal_code}" />
		<input type="hidden" name="city" value="{$city}" />
		{if (!empty($state))}<input type="hidden" name="state" value="{$state}" />{/if}
		<input type="hidden" name="country" value="{$country}" />
		<input type="hidden" name="amount" value="{$amount}" />
		<input type="hidden" name="currency" value="{$currency}" />
		<input type="hidden" name="amount2_description" value="{$amount2_description}" />
		<input type="hidden" name="amount2" value="{$amount2}" />
		<input type="hidden" name="amount3_description" value="{$amount3_description}" />
		<input type="hidden" name="amount3" value="{$amount3}" />
		<input type="hidden" name="amount4_description" value="{$amount4_description}" />
		<input type="hidden" name="amount4" value="{$amount4}" />
		<input type="hidden" class="payment_methods" name="payment_methods" value="ACC">
		<input type="hidden" name="return_url_target" value="2">
		<input type="hidden" name="cancel_url_target" value="2">
		<input type="hidden" name="merchant_fields" value="platform">
		<input type="hidden" name="platform" value="prestashop">
		<input type="image" src="{$base_dir}modules/moneybookers/logos/international/{$inter_logos[$i].file}.gif" name="Submit" value="{$inter_logos[$i].code}" style="float: left; margin-right: 10px; border: none;" onclick="$('input.payment_methods').val($(this).val());" />
		<span style="margin-top: 25px; display: block;">{l s='Pay by' mod='moneybookers'} {$inter_logos[$i].name}</span>
	</p>
	</form>
	{/foreach}
	{foreach from=$local item=i}
	<form action="https://www.moneybookers.com/app/payment.pl" method="post">
	<p class="payment_module" style="border: 1px solid #595A5E; display: block; text-decoration: none; height: 50px; margin-left: 7px; padding: 0.6em;">
		<input type="hidden" name="pay_to_email" value="{$pay_to_email}" />
		<input type="hidden" name="recipient_description" value="{$recipient_description}" />
		<input type="hidden" name="transaction_id" value="{$transaction_id}" />
		<input type="hidden" name="return_url" value="{$return_url}" />
		<input type="hidden" name="return_url_text" value="{$return_url}" />
		<input type="hidden" name="cancel_url" value="{$return_url}" />
		<input type="hidden" name="status_url" value="{$status_url}" />
		<input type="hidden" name="status_url2" value="{$pay_to_email}" />
		<input type="hidden" name="language" value="{$language}" />
		<input type="hidden" name="hide_login" value="{$hide_login}" />
		<input type="hidden" name="pay_from_email" value="{$pay_from_email}" />
		<input type="hidden" name="firstname" value="{$firstname}" />
		<input type="hidden" name="lastname" value="{$lastname}" />
		{if (!empty($date_of_birth))}<input type="hidden" name="date_of_birth" value="{$date_of_birth}" />{/if}
		<input type="hidden" name="address" value="{$address}" />
		{if (!empty($address2))}<input type="hidden" name="address2" value="{$address2}" />{/if}
		{if (!empty($phone_number))}<input type="hidden" name="phone_number" value="{$phone_number}" />{/if}
		<input type="hidden" name="postal_code" value="{$postal_code}" />
		<input type="hidden" name="city" value="{$city}" />
		{if (!empty($state))}<input type="hidden" name="state" value="{$state}" />{/if}
		<input type="hidden" name="country" value="{$country}" />
		<input type="hidden" name="amount" value="{$amount}" />
		<input type="hidden" name="currency" value="{$currency}" />
		<input type="hidden" name="amount2_description" value="{$amount2_description}" />
		<input type="hidden" name="amount2" value="{$amount2}" />
		<input type="hidden" name="amount3_description" value="{$amount3_description}" />
		<input type="hidden" name="amount3" value="{$amount3}" />
		<input type="hidden" name="amount4_description" value="{$amount4_description}" />
		<input type="hidden" name="amount4" value="{$amount4}" />
		<input type="hidden" class="payment_methods" name="payment_methods" value="ACC">
		<input type="hidden" name="return_url_target" value="2">
		<input type="hidden" name="cancel_url_target" value="2">
		<input type="hidden" name="merchant_fields" value="platform">
		<input type="hidden" name="platform" value="prestashop">
		<input type="image" src="{$base_dir}modules/moneybookers/logos/local/{$local_logos[$i].file}.gif" name="Submit" value="{$local_logos[$i].code}" style="float: left; margin-right: 10px; border: none;" onclick="$('input.payment_methods').val($(this).val());" />
		<span style="margin-top: 25px; display: block;">{l s='Pay by' mod='moneybookers'} {$local_logos[$i].name}</span>
		<br style="clear: both;" />
	</p>
	</form>
	{/foreach}
{/if}

