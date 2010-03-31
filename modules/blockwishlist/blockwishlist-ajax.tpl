{if $products}
	<dl class="products" style="{if $products}border-bottom:1px solid #fff;{/if}">
	{foreach from=$products item=product name=i}
		<dt class="{if $smarty.foreach.i.first}first_item{elseif $smarty.foreach.i.last}last_item{else}item{/if}">
			<span class="quantity-formated"><span class="quantity">{$product.quantity|intval}</span>x</span>
			<a class="cart_block_product_name" href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category_rewrite)}" title="{$product.name|escape:'htmlall':'UTF-8'}" style="font-weight:bold;">{$product.name|truncate:13:'...'|escape:'htmlall':'UTF-8'}</a>
			<a class="ajax_cart_block_remove_link" href="javascript:;" onclick="javascript:WishlistCart('wishlist_block_list', 'delete', '{$product.id_product}', {$product.id_product_attribute}, '0', '{$static_token}');" title="{l s='remove this product from my wishlist' mod='blockwishlist'}"><img src="{$img_dir}icon/delete.gif" alt="{l s='Delete'}" class="icon" /></a>
		</dt>
		{if isset($product.attributes_small)}
		<dd class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if}" style="font-style:italic;margin:0 0 0 10px;">
			<a href="{$link->getProductLink($product.id_product, $product.link_rewrite)}" title="{l s='Product detail'}">{$product.attributes_small|escape:'htmlall':'UTF-8'}</a>
		</dd>
		{/if}
	{/foreach}
	</dl>
{else}
	<dl class="products" style="font-size:10px;border-bottom:1px solid #fff;">
	{if $error}
		<dt>{l s='You must create a wishlist before adding products' mod='blockwishlist'}</dt>
	{else}
		<dt>{l s='No products' mod='blockwishlist'}</dt>
	{/if}
	</dl>
{/if}
