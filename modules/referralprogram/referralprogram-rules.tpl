<h3>{l s='Referral program rules' mod='referralprogram'}</h3>

{if isset($xml)}
<div id="referralprogram_rules">
	{if isset($xml->body->$paragraph)}{$xml->body->$paragraph|replace:"\'":"'"|replace:'\"':'"'}{/if}
</div>
{/if}
