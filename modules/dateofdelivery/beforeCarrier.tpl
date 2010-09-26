{if $datesDelivery|count}
<script type="text/javascript">
{literal}
var datesDelivery = new Array();
{/literal}{foreach from=$datesDelivery item=date key=k}
{if $date}
datesDelivery[{$k}] = new Array();
datesDelivery[{$k}]['minimal'] = "{$date.0}";
datesDelivery[{$k}]['maximal'] = "{$date.1}";
{/if}
{/foreach}{literal}

$(function(){
	if (datesDelivery[{/literal}{$id_carrier}{literal}] != undefined)
	{
		$('span#minimal').html('<b>'+datesDelivery[{/literal}{$id_carrier}{literal}]['minimal']+'</b>');
		$('span#maximal').html('<b>'+datesDelivery[{/literal}{$id_carrier}{literal}]['maximal']+'</b>');
	}
	else
		$('p#dateofdelivery').hide();
	
	$('input[name=id_carrier]').click(function(){
		if (datesDelivery[$(this).val()] != undefined)
		{
			$('p#dateofdelivery').show();
			$('span#minimal').html('<b>'+datesDelivery[$(this).val()]['minimal']+'</b>');
			$('span#maximal').html('<b>'+datesDelivery[$(this).val()]['maximal']+'</b>');
		}
		else
			$('p#dateofdelivery').hide();
	});
});
{/literal}
</script>

<br />
<p id="dateofdelivery">{l s='Approximate date of delivery with this carrier is between' mod='dateofdelivery'} <span id="minimal"></span> {l s='and' mod='dateofdelivery'} <span id="maximal"></span> <sup>*</sup></p>
<p style="font-size:10px;margin:0padding:0;"><sup>*</sup> {l s='with direct payment methods (e.g: credit card)' mod='dateofdelivery'}</p>
{/if}