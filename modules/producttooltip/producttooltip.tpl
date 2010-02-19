<script type="text/javascript" src="{$base_dir}js/jquery/jquery.jgrowl-1.2.1.min.js"></script>
<link href="{$base_dir}css/jquery.jgrowl.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">
$(document).ready(function() {ldelim}
	{if isset($nb_people)}$.jGrowl('{$nb_people} {if $nb_people == 1}{l s='person is currently watching' mod='producttooltip'}{else}{l s='people are currently watching' mod='producttooltip'}{/if} {l s='this product' mod='producttooltip'}', {literal}{ life: 3500 }{/literal});{/if}
	{if isset($date_last_order)}$.jGrowl('{l s='This product was bought last' mod='producttooltip'} {dateFormat date=$date_last_order full=1}', {literal}{ life: 3500 }{/literal});{/if}
	{if isset($date_last_cart)}$.jGrowl('{l s='This product was added to cart last' mod='producttooltip'} {dateFormat date=$date_last_cart full=1}', {literal}{ life: 3500 }{/literal});{/if}
{rdelim});
</script>
