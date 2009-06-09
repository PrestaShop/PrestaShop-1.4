<script type="text/javascript" src="{$content_dir}modules/blockwishlist/js/ajax-wishlist.js"></script>
<div id="wishlist_block" class="block account">
	<h4>
		<a href="{$base_dir_ssl}/modules/blockwishlist/mywishlist.php">{l s='Wishlist' mod='blockwishlist'}</a>
	</h4>
	<div class="block_content">
		<div id="wishlist_block_list" class="expanded">
		{if $products}
			<dl class="products">
			{foreach from=$products item=product name=i}
				<dt class="clear {if $smarty.foreach.i.first}first_item{elseif $smarty.foreach.i.last}last_item{else}item{/if}">
					<span class="quantity-formated"><span class="quantity">{$product.quantity|intval}</span>x</span>
					<a class="cart_block_product_name" href="{$link->getProductLink($product.id_product, $product.link_rewrite)}" title="{$product.name|escape:'htmlall':'UTF-8'}">{$product.name|truncate:30:'...'|escape:'htmlall':'UTF-8'}</a>
					<a class="ajax_cart_block_remove_link" href="javascript:;" onclick="javascript:WishlistCart('wishlist_block_list', 'delete', '{$product.id_product}', {$product.id_product_attribute}, '0', '{$token}');" title="{l s='remove this product from my wishlist' mod='blockwishlist'}"><img src="{$img_dir}icon/delete.gif" alt="{l s='Delete'}" class="icon" /></a>
				</dt>
				{if isset($product.attributes_small)}
				<dd class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if}">
					<a href="{$link->getProductLink($product.id_product, $product.link_rewrite)}" title="{l s='Product detail'}">{$product.attributes_small|escape:'htmlall':'UTF-8'}</a>
				</dd>
				{/if}
			{/foreach}
			</dl>
		{else}
			<dl class="products">
				<dt>{l s='No products' mod='blockwishlist'}</dt>
			</dl>
		{/if}
		</div>
		<p class="align_center">
		{if $wishlists}
			<select name="wishlists" id="wishlists" onchange="WishlistChangeDefault('wishlist_block_list', $('#wishlists').val());">
			{foreach from=$wishlists item=wishlist name=i}
				<option value="{$wishlist.id_wishlist}"{if $id_wishlist eq $wishlist.id_wishlist or ($id_wishlist == false and $smarty.foreach.i.first)} selected="selected"{/if}>{$wishlist.name|truncate:22:'...'|escape:'htmlall':'UTF-8'}</option>
			{/foreach}
			</select>
		{/if}
			<a href="{$base_dir_ssl}modules/blockwishlist/mywishlist.php" class="exclusive" title="{l s='My wishlists' mod='blockwishlist'}">{l s='My wishlists' mod='blockwishlist'}</a>
		</p>
	</div>
</div>
