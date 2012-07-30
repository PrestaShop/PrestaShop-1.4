<div id="psa_block_cart" style="display:none">
    {if isset($psa_products)}
    	<dl>
			{foreach from=$psa_products item=product name=psa_products}</dt>
	        <dt id="psa_{$product.id_product}{if $product.id_product_attribute != 0}_{$product.id_product_attribute}{/if}">
	        	<a class="cart_block_product_name" href="#" title="{$product.name}">{l s='Inssurance' mod='prestassurance'}</a>
	        		{if $product.deleted}
	        			<span style="float:right">
	        				<a rel="nofollow" class="add_psa_link" href="{$add_to_cart_url}?deleted=0&id_psa_product={$product.id_psa_product}&id_cart={$id_cart}&id_product={$product.id_product}&id_product_attribute={$product.id_product_attribute}&qty={$product.token}&token={$product.token}" title="">&nbsp;</a>
	        			</span>
	        		{else}
	        			<span class="remove_link">
	        				<a rel="nofollow" class="ajax_cart_block_remove_link" href="{$add_to_cart_url}?deleted=1&id_cart={$id_cart}&id_psa_product={$product.id_psa_product}&id_product={$product.id_product}&id_product_attribute={$product.id_product_attribute}&qty={$product.token}&token={$product.token}" title="">&nbsp;</a>
	        			</span>
	        		{/if}
	        	<span class="price">{$product.price}</span></dt>
	        <dd>
	            <div class="clear"></div>
	        </dd>
	    	{/foreach}
		</dl>
    {/if}
</div>
