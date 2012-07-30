<script type="text/javascript">
	var psa_customer_alert = {if $psa_customer_alert != ''}true{else}false{/if};
	var psa_token = '{$psa_token}';
</script>
{if $psa_customer_alert != ''}
	<a href="#psa_customer_alert_content" id="psa_customer_alert" style="display:none"></a>
	<div style="display:none">
		<div id="psa_customer_alert_content" style="width:800px">
			{$alert_psa_cms->content}
			<div >
				<a style="float:left" href="#" onclick="$.fancybox.close();" class="button_large" title="{l s='Add Insurance' mod='prestassurance'}">
					{l s='Add Insurance' mod='prestassurance'}
				</a>
				<a style="float:right" href="{$link->getPageLink('order.php', true)}?step=1" class="button_large" title="{l s='Continue without Insurance' mod='prestassurance'}">
					{l s='Continue without Insurance' mod='prestassurance'}</a>
			</div>
		</div>
	</div>
{/if}

<div id="psa_cart_summary" style="display:none">
	{if isset($psa_products)}
		<table>
			{foreach from=$psa_products item=product name=psa_products}
				<tr id="psa_{$product.id_product}_{$product.id_product_attribute}" class="cart_item psa_{$product.id_psa_product}" style="background:{if $product.deleted}{$psa_not_added_bg}{else}{$psa_added_bg}{/if};color:{if $product.deleted}{$psa_not_added_txt}{else}{$psa_added_txt}{/if}">
					<td class="cart_description" colspan="4" style="padding-left:10px">
						<h5>{l s='Inssurance' mod='prestassurance'}</h5>
						{$product.name}
						{if $link_conditions != ''}
							<span style="float:right">
								<a href="{$link_conditions}" class="iframe" style="color:#FFF">
									{l s='More infos' mod='prestassurance'}
								</a>
								<script type="text/javascript">
									if(typeof($('a.iframe').fancybox) != 'undefined')
										$('a.iframe').fancybox();
								</script>
							</span>
						{/if}
					</td>
					<td class="cart_unit">
						<span class="price" id="product_price_{$product.id_product}" style="color:{if $product.deleted}{$psa_not_added_price}{else}{$psa_added_price}{/if}">{$product.unit_price}</span>
					</td>
					<td class="cart_total">
						<span class="price" id="product_price_{$product.id_product}" style="color:{if $product.deleted}{$psa_not_added_price}{else}{$psa_added_price}{/if}">{$product.price}</span>
					</td>
					<td class="cart_delete" align="center">
						{if $product.deleted}
							<a href="{$add_to_cart_url}?deleted=0&id_psa_product={$product.id_psa_product}&id_cart={$id_cart}&id_product={$product.id_product}&id_product_attribute={$product.id_product_attribute}&qty={$product.qty}&token={$product.token}" class="exclusive" title="">
								{l s='Add to cart' mod='prestassurance'}
							</a>
						{else}
							<a href="{$add_to_cart_url}?deleted=1&id_cart={$id_cart}&id_psa_product={$product.id_psa_product}&id_product={$product.id_product}&id_product_attribute={$product.id_product_attribute}&qty={$product.qty}&token={$product.token}" style="padding-right:30px">
								<img src="{$img_dir}icon/delete.gif" alt="Delete" class="icon">
							</a>
						{/if}
					</td>
				</tr>
			{/foreach}
		</table>
	{/if}
</div>
