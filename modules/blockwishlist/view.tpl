<div id="view_wishlist">
<h2>{l s='Wishlist' mod='blockwishlist'}</h2>
{if $wishlists}
<p>
	{l s='Other wishlists of' mod='blockwishlist'} {$current_wishlist.firstname} {$current_wishlist.lastname}: 
	{foreach from=$wishlists item=wishlist name=i}
	<a href="{$base_dir_ssl}modules/blockwishlist/view.php?token={$wishlist.token}">{$wishlist.name}</a>
		{if !$smarty.foreach.i.last}
			/
		{/if}
	{/foreach}
</p>
{/if}
{if $products}
<div class="addresses" id="featured-products_block_center">
	<h3>{l s='Welcome to the wishlist of' mod='blockwishlist'} {$current_wishlist.firstname} {$current_wishlist.lastname}: {$current_wishlist.name}</h3>
	<p /> 
	{foreach from=$products item=product name=i}
	<ul class="address {if $smarty.foreach.i.last}last_item{elseif $smarty.foreach.i.first}first_item{/if} {if $smarty.foreach.i.index % 2}alternate_item{else}item{/if}" id="block_{$product.id_product}_{$product.id_product_attribute}">
	<div class="ajax_block_product">
		<li class="address_title"><a href="{$link->getProductLink($product.id_product, $product.link_rewrite)}" title="{l s='View' mod='blockwishlist'}">{$product.name|truncate:30:'...'|escape:'htmlall':'UTF-8'}</a></li>
		<li class="address_name">
			<a	href="{$link->getProductlink($product.id_product, $product.link_rewrite)}" title="{l s='Product detail' mod='blockwishlist'}">
				<img src="{$img_prod_dir}{$product.cover}-medium.jpg" alt="{$product.name|escape:'htmlall':'UTF-8'}" />
			</a>
		<span class="wishlist_product_detail">
		{if isset($product.attributes_small)}
			<br /><a href="{$link->getProductlink($product.id_product, $product.link_rewrite)}" title="{l s='Product detail' mod='blockwishlist'}">{$product.attributes_small|escape:'htmlall':'UTF-8'}</a>
		{/if}
			<br />{l s='Quantity:' mod='blockwishlist'}<input type="text" id="{$product.id_product}_{$product.id_product_attribute}" size="3" value="{$product.quantity|intval}" />
			<br />{l s='Priority:' mod='blockwishlist'}
			{if $product.priority eq 0}
				<span style="color:darkred; float:right;">{l s='High' mod='blockwishlist'}</span>
			{elseif $product.priority eq 1}
				<span style="color:darkorange; float:right;">{l s='Medium' mod='blockwishlist'}</span>
			{else}
				<span style="color:green; float:right;">{l s='Low' mod='blockwishlist'}</span>
			{/if}
		</span>
		</li>
		<li class="address_address1 clear">
			<a class="button_small clear" href="{$link->getProductLink($product.id_product, $product.link_rewrite)}" title="{l s='View' mod='blockwishlist'}">{l s='View' mod='blockwishlist'}</a>
			{if $product.attribute_quantity AND $product.attribute_quantity >= 1}
			{if !$ajax}
			<form id="addtocart_{$product.id_product|intval}_{$product.id_product_attribute|intval}" action="{$base_dir}cart.php" method="post">
			<p class="hidden">
				<input type="hidden" name="id_product" value="{$product.id_product|intval}" id="product_page_product_id"  />
				<input type="hidden" name="add" value="1" />
				<input type="hidden" name="token" value="{$token}" />
				<input type="hidden" name="id_product_attribute" id="idCombination" value="{$product.id_product_attribute|intval}" />
			</p>
			</form>
			{/if}
			<a href="javascript:;" class="exclusive" onclick="WishlistBuyProduct('{$token|escape:'htmlall':'UTF-8'}', '{$product.id_product}', '{$product.id_product_attribute}', '{$product.id_product}_{$product.id_product_attribute}', this, {$ajax});" title="{l s='Add to cart' mod='homefeatured'}">{l s='Add to cart' mod='blockwishlist'}</a>
			{else}
			<span class="exclusive">{l s='Add to cart' mod='blockwishlist'}</span>
			{/if}
		</li>
	</div>
	</ul>
	{/foreach}
	<p class="clear" />
</div>
{else}
	{l s='No products' mod='blockwishlist'}
{/if}
</div>
