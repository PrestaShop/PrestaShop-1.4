{if $datesDelivery|count}
<p id="dateofdelivery">{l s='Approximate date of delivery is between' mod='dateofdelivery'} <b>{$datesDelivery.0}</b> {l s='and' mod='dateofdelivery'} <b>{$datesDelivery.1}</b> <sup>*</sup></p>
<p style="font-size:10px;margin:0padding:0;"><sup>*</sup> {l s='with direct payment methods (e.g: credit card)' mod='dateofdelivery'}</p>
{/if}