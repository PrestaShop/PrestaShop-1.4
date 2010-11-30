{if isset($products)}
	<!-- Products list -->
	<ul id="product_list" class="clear">
	{foreach from=$products item=product name=products}
		<li class="ajax_block_product {if $smarty.foreach.products.first}first_item{elseif $smarty.foreach.products.last}last_item{/if} {if $smarty.foreach.products.index % 2}alternate_item{else}item{/if} clearfix">
			<div class="center_block">
				<a href="{$product.link|escape:'htmlall':'UTF-8'}" class="product_img_link" title="{$product.name|escape:'htmlall':'UTF-8'}"><img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home')}" alt="{$product.legend|escape:'htmlall':'UTF-8'}" {if isset($homeSize)} width="{$homeSize.width}" height="{$homeSize.height}"{/if} /></a>
				<h3>{if $product.new == 1}<span class="new">{l s='new'}</span>{/if}<a href="{$product.link|escape:'htmlall':'UTF-8'}" title="{$product.name|escape:'htmlall':'UTF-8'}">{$product.name|truncate:35:'...'|escape:'htmlall':'UTF-8'}</a></h3>
				<p class="product_desc"><a href="{$product.link|escape:'htmlall':'UTF-8'}" title="{$product.description_short|truncate:360:'...'|escape:'htmlall':'UTF-8'|strip_tags:'UTF-8'}">{$product.description_short|truncate:360:'...'|strip_tags:'UTF-8'}</a></p>
			</div>																				 
			<div class="right_block">
				{if $product.on_sale && $product.show_price}
					<span class="on_sale">{l s='On sale!'}</span>
				{elseif $product.reduction && $product.show_price}
					<span class="discount">{l s='Price lowered!'}</span>
				{/if}
				{if $product.online_only}
					<span class="online_only">{l s='Online only!'}</span>
				{/if}
				{if $product.show_price OR $product.available_for_order}
				<div>
					{if $product.show_price AND !isset($restricted_country_mode)}<span class="price" style="display: inline;">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}</span><br />{/if}
					{if $product.available_for_order AND !isset($restricted_country_mode)}<span class="availability">{if ($product.allow_oosp OR $product.quantity > 0)}{l s='Available'}{else}{l s='Out of stock'}{/if}</span>{/if}
				</div>
				{/if}
				{if ($product.id_product_attribute == 0 OR (isset($add_prod_display) AND ($add_prod_display == 1))) AND $product.available_for_order AND !isset($restricted_country_mode)}
					{if ($product.allow_oosp OR $product.quantity > 0) && $product.customizable != 2}
						<a class="button ajax_add_to_cart_button exclusive" rel="ajax_id_product_{$product.id_product|intval}" href="{$link->getPageLink('cart.php')}?add&amp;id_product={$product.id_product|intval}&amp;token={$static_token}" title="{l s='Add to cart'}">{l s='Add to cart'}</a>
					{else}
							<span class="exclusive">{l s='Add to cart'}</span>
					{/if}
				{/if}
				<a class="button" href="{$product.link|escape:'htmlall':'UTF-8'}" title="{l s='View'}">{l s='View'}</a>
				{if isset($comparator_max_item) && $comparator_max_item}
					<p><input type="checkbox" onclick="checkForComparison({$comparator_max_item})" class="comparator" id="comparator_item_{$product.id_product}" value="{$product.id_product}" /><label for="comparator_item_{$product.id_product}">{l s='Select to compare'}</label></p>
				{/if}				
			</div>
		</li>
	{/foreach}
	</ul>
	<!-- /Products list -->
{/if}
