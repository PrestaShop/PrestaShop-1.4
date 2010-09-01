<script type="text/javascript">
{literal}
	
	function change_action_form() {
			if ($('#id_carrier{/literal}{$id_carrier}{literal}').is(':not(:checked)'))
			{
				$('#form').attr("action", 'order.php');
			}
			else
			{
				$('#form').attr("action", '{/literal}{$urlSo}{literal}');
			}
		}

	$(document).ready(function() 
	{
		$('input[name=id_carrier]').change(function() {
			change_action_form();	
		});
		change_action_form();
	});
{/literal}
</script>
<!--
				<tr>
					<td class="carrier_action radio socolissimo">
						<input type="hidden" name="socolissimo_id_carrier" value="{$id_carrier|intval}"/>
						<input type="radio" name="id_carrier" value="{$id_carrier|intval}" id="id_carrier{$id_carrier|intval}" {if $id_carrier == $checked}checked="checked"{/if}/>
					</td>
					<td class="carrier_name">
						<label for="id_carrier{$id_carrier|intval}">
							{if $img}<img src="{$img|escape:'htmlall':'UTF-8'}" alt="{$name|escape:'htmlall':'UTF-8'}" />{else}{$name|escape:'htmlall':'UTF-8'}{/if}
						</label>
					</td>
					<td class="carrier_infos">{$delay|escape:'htmlall':'UTF-8'}</td>
					<td class="carrier_price">
						{if $price}
							<span class="price">
								{if $priceDisplay == 1}{convertPrice price=$price_tax_exc}{else}{convertPrice price=$price}{/if}
							</span>
							{if $priceDisplay == 1} {l s='(tax excl.)' mod='socolissimo'}{else} {l s='(tax incl.)' mod='socolissimo'}{/if}
						{else}
							{l s='Free !' mod='socolissimo'}
						{/if}
					</td>
				</tr>
-->
{foreach from=$inputs item=input key=name name=myLoop}
		<input type="hidden" name="{$name|escape:'htmlall':'UTF-8'}" value="{$input|strip_tags|addslashes}"/>
{/foreach}
