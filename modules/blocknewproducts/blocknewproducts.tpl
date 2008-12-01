<!-- MODULE Block new products -->
<div id="new-products_block_right" class="block products_block">
	<h4><a href="{$base_dir}new-products.php" title="{l s='New products' mod='blocknewproducts'}">{l s='New products' mod='blocknewproducts'}</a></h4>
	<div class="block_content">
	{if $new_products}
		<ul class="product_images">
			<li><a href="{$new_products.0.link}" title="{$new_products.0.legend|escape:htmlall:'UTF-8'}"><img src="{$img_prod_dir}{$new_products.0.id_image}-medium.jpg" alt="{$new_products.0.legend|escape:htmlall:'UTF-8'}" /></a></li>
			<li><a href="{$new_products.1.link}" title="{$new_products.1.legend|escape:htmlall:'UTF-8'}"><img src="{$img_prod_dir}{$new_products.1.id_image}-medium.jpg" alt="{$new_products.1.legend|escape:htmlall:'UTF-8'}" /></a></li>
		</ul>
		<dl class="products">
		{foreach from=$new_products item=product name=myLoop}
			<dt class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if}"><a href="{$product.link}" title="{$product.name|escape:htmlall:'UTF-8'}">{$product.name|escape:htmlall:'UTF-8'}</a></dt>
			{if $product.description_short}<dd class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if}"><a href="{$product.link}">{$product.description_short|strip_tags:htmlall:'UTF-8'|truncate:50}</a>&nbsp;<a href="{$product.link}"><img alt=">>" src="{$img_dir}bullet.gif"/></a></dd>{/if}
		{/foreach}
		</dl>
		<p><a href="{$base_dir}new-products.php" title="{l s='All new products' mod='blocknewproducts'}" class="button_large">{l s='All new products' mod='blocknewproducts'}</a></p>
	{else}
		<p>{l s='No new product at this time' mod='blocknewproducts'}</p>
	{/if}
	</div>
</div>
<!-- /MODULE Block new products -->
