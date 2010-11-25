<script type="text/javascript">var url_appel="{$base_dir}";</script>
<script type="text/javascript" src="{$base_dir}modules/mondialrelay/kit_mondialrelay/js/include_MR.js"></script>
<script type="text/javascript" src="{$base_dir}modules/mondialrelay/kit_mondialrelay/js/ressources_MR.js"></script>
<script type="text/javascript">
	var one_page_checkout = {$one_page_checkout};
	var server_error = "{l s='Problem getting addresses from MondialRelay Webservice : Mondial Relay servers\' maybe down' mod='mondialrelay'}";
	var address_error = "{l s='There is no Relay Point close to your address' mod='mondialrelay'}";
</script>

{foreach from=$carriersextra item=carrier name=myLoop}

	<script type="text/javascript">{literal}
	$(document).ready(function() 
	{
		$('input[name$=id_carrier]').click(function() {
			{/literal}affiche_mydiv_mr({$carrier.id_carrier|intval}, 'relativ_base_dir={$base_dir}&Pays={$input_pays}&Ville={$input_ville}&CP={$input_cp}&Taille=&Poids={$input_poids}&Action={$carrier.liv|escape:'htmlall':'UTF-8'}&num={$carrier.id_carrier|intval}');{literal}
		});
	});
	{/literal}</script>
	
	<tr class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{/if} {if ($nbcarriers+$smarty.foreach.myLoop.index) % 2}alternate_item{else}item{/if}">

					<td class="carrier_action radio">
						<input type="radio" name="id_carrier" value="{$carrier.id_carrier|intval}" id="id_carrier_mr{$carrier.id_carrier|intval}" onchange="affiche_mydiv_mr({$carrier.id_carrier|intval}, 
   'relativ_base_dir={$base_dir}&Pays={$input_pays}&Ville={$input_ville}&CP={$input_cp}&Taille=&Poids={$input_poids}&Action={$carrier.liv|escape:'htmlall':'UTF-8'}&num={$carrier.id_carrier|intval}'
						);" {if ($carrier.id_carrier == $checked)} checked="checked" {/if} {if $one_page_checkout}onclick="updateCarrierSelectionAndGift();" {/if} />{$carrierextra}
					</td>
					<td class="carrier_name">
						<label for="id_carrier_mr{$carrier.id_carrier|intval}">
						{if $carrier.img}<img src="{$carrier.img|escape:'htmlall':'UTF-8'}" alt="{$carrier.name|escape:'htmlall':'UTF-8'}">{else}{$carrier.name|escape:'htmlall':'UTF-8'}{/if}
							
						</label>
					</td>
					<td class="carrier_infos">{$carrier.delay|escape:'htmlall':'UTF-8'}</td>
					<td class="carrier_price">
						{if $carrier.price}
							<span class="price">
								{if $priceDisplay == 1}{convertPrice price=$carrier.price_tax_exc}{else}{convertPrice price=$carrier.price}{/if}
							</span>
							{if $priceDisplay == 1} {l s='(tax excl.)' mod='mondialrelay'}{else} {l s='(tax incl.)' mod='mondialrelay'}{/if}
						{else}
							{l s='Free!' mod='mondialrelay'}
						{/if}
					</td>
			</tr>		
	{if $carrier.liv !='LDR' && $carrier.liv != 'LD1' && $carrier.liv != 'LDS'}
				<tr class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{/if} {if ($nbcarriers+$smarty.foreach.myLoop.index) % 2}alternate_item{else}item{/if}">
					<td colspan="4" align="center">
						<div style="display:none;" id="form_mondialrelay_{$carrier.id_carrier|intval}"></div>
						{if ($google_api_key)}
						<iframe style="display:none;" id="all_mondialrelay_map_{$carrier.id_carrier|intval}" width="517px" height="317px" frameborder="0" scrolling="no" src="{$base_dir}modules/mondialrelay/googlemap.php?relativ_base_dir={$base_dir}&Pays={$input_pays}&Ville={$input_ville}&CP={$input_cp}&Taille=&Poids={$input_poids}&Action={$carrier.liv|escape:'htmlall':'UTF-8'}&num={$carrier.id_carrier|intval}&address={$address_map}"></iframe>
						{/if}
						<img src="{$base_dir}modules/mondialrelay/kit_mondialrelay/loading.gif" style="display:none;" id="loading_mr"/>
						<div style="display:none;" id="mondialrelay_{$carrier.id_carrier|intval}"></div>		 
					</td>
				</tr>
	{/if}
	<script type="text/javascript" >
		if ($("#id_carrier{$carrier.id_carrier|intval}").attr('checked') == true)
			$("#id_carrier_mr{$carrier.id_carrier|intval}").attr('checked', "checked");
		$("#id_carrier{$carrier.id_carrier|intval}").parent().parent().remove();
		include_mondialrelay({$carrier.id_carrier|intval});
		affiche_mydiv_mr({$carrier.id_carrier|intval}, 'relativ_base_dir={$base_dir}&Pays={$input_pays}&Ville={$input_ville}&CP={$input_cp}&Taille=&Poids={$input_poids}&Action={$carrier.liv|escape:'htmlall':'UTF-8'}&num={$carrier.id_carrier|intval}');
	</script>
{/foreach}
