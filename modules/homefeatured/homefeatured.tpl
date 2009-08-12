<!-- MODULE Home Featured Products -->
<div id="featured-products_block_center" class="block products_block">
	<h4>{l s='featured products' mod='homefeatured'}</h4>
	{if isset($products) AND $products}
		<div class="block_content">
			{assign var='liHeight' value=360}
			{assign var='nbItemsPerLine' value=4}
			{assign var='nbLi' value=$products|@count}
			{assign var='nbLines' value=$nbLi/$nbItemsPerLine|ceil}
			{assign var='ulHeight' value=$nbLines*$liHeight}
			<ul style="height:{$ulHeight}px;">
			{foreach from=$products item=product name=homeFeaturedProducts}
				<li class="ajax_block_product {if $smarty.foreach.homeFeaturedProducts.first}first_item{elseif $smarty.foreach.homeFeaturedProducts.last}last_item{else}item{/if} {if $smarty.foreach.homeFeaturedProducts.iteration%$nbItemsPerLine == 0}last_item_of_line{elseif $smarty.foreach.homeFeaturedProducts.iteration%$nbItemsPerLine == 1}first_item_of_line{/if} {if $smarty.foreach.homeFeaturedProducts.iteration > ($smarty.foreach.homeFeaturedProducts.total - ($smarty.foreach.homeFeaturedProducts.total % $nbItemsPerLine))}last_line{/if}">
					<h5><a href="{$product.link}" title="{$product.name|truncate:32:'...'|escape:'htmlall':'UTF-8'}">{$product.name|truncate:27:'...'|escape:'htmlall':'UTF-8'}</a></h5>
					<p class="product_desc"><a href="{$product.link}" title="{l s='More' mod='homefeatured'}">{$product.description_short|strip_tags|truncate:130:'...'}</a></p>
					<a href="{$product.link}" title="{$product.legend|escape:htmlall:'UTF-8'}" class="product_image"><img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home')}" height="{$homeSize.height}" width="{$homeSize.width}" alt="{$product.legend|escape:htmlall:'UTF-8'}" /></a>
					<div>
						{if !$priceDisplay || $priceDisplay == 2}<p class="price_container"><span class="price">{convertPrice price=$product.price}</span>{if $priceDisplay == 2} {l s='+Tx' mod='homefeatured'}{/if}</p>{/if}
						{if $priceDisplay}<p class="price_container"><span class="price">{convertPrice price=$product.price_tax_exc}</span>{if $priceDisplay == 2} {l s='-Tx' mod='homefeatured'}{/if}</p>{/if}
						<a class="button" href="{$product.link}" title="{l s='View' mod='homefeatured'}">{l s='View' mod='homefeatured'}</a>
						{if ($product.quantity > 0 OR $product.allow_oosp) AND $product.customizable != 2}
						<a class="exclusive ajax_add_to_cart_button" rel="ajax_id_product_{$product.id_product}" href="{$base_dir}cart.php?qty=1&amp;id_product={$product.id_product}&amp;token={$static_token}&amp;add" title="{l s='Add to cart' mod='homefeatured'}">{l s='Add to cart' mod='homefeatured'}</a>
						{else}
						<span class="exclusive">{l s='Add to cart' mod='homefeatured'}</span>
						{/if}
					</div>
				</li>
			{/foreach}
			</ul>
		</div>
	{else}
		<p>{l s='No featured products' mod='homefeatured'}</p>
	{/if}
</div>
<!-- /MODULE Home Featured Products -->
