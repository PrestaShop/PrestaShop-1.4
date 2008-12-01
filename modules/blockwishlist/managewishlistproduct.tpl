{if $products}
	{foreach from=$products item=product name=i}
	{if $product.quantity|intval eq 0}
	<div class="wlp_bought">
	{/if}
	<ul class="address {if $smarty.foreach.i.index % 2}alternate_{/if}item" style="margin:5px 0 0 5px;border-bottom:1px solid #ccc;" id="wlp_{$product.id_product}_{$product.id_product_attribute}">
		<li class="address_title">{$product.name|truncate:30|escape:'htmlall':'UTF-8'}</li>
		<li class="address_name">
			<a href="{$link->getProductlink($product.id_product, $product.link_rewrite)}" title="{l s='Product detail' mod='blockwishlist'}">
				<img src="{$img_prod_dir}{$product.cover}-medium.jpg" alt="{$product.name|escape:'htmlall':'UTF-8'}" />
			</a>
		<span class="wishlist_product_detail">
		{if isset($product.attributes_small)}
			<a href="{$link->getProductlink($product.id_product, $product.link_rewrite)}" title="{l s='Product detail' mod='blockwishlist'}">{$product.attributes_small|escape:'htmlall':'UTF-8'}</a>
		{/if}
			<br />{l s='Quantity' mod='blockwishlist'}:<input type="text" id="quantity_{$product.id_product}_{$product.id_product_attribute}" value="{$product.quantity|intval}" size="3"  />
			<br /><br />{l s='Priority' mod='blockwishlist'}: <select id="priority_{$product.id_product}_{$product.id_product_attribute}">
				<option value="0"{if $product.priority eq 0} selected="selected"{/if}>{l s='High' mod='blockwishlist'}</option>
				<option value="1"{if $product.priority eq 1} selected="selected"{/if}>{l s='Medium' mod='blockwishlist'}</option>
				<option value="2"{if $product.priority eq 2} selected="selected"{/if}>{l s='Low' mod='blockwishlist'}</option>
			</select>
		</span>
			<a href="javascript:;" class="clear button" onclick="WishlistProductManage('wlp_{$product.id_product}_{$product.id_product_attribute}', 'delete', '{$id_wishlist}', '{$product.id_product}', '{$product.id_product_attribute}', $('#quantity_{$product.id_product}_{$product.id_product_attribute}').val(), $('#priority_{$product.id_product}_{$product.id_product_attribute}').val());" title="{l s='Delete' mod='blockwishlist'}">{l s='Delete' mod='blockwishlist'}</a>
			<a href="javascript:;" class="exclusive" onclick="WishlistProductManage('wlp_{$product.id_product}_{$product.id_product_attribute}', 'update', '{$id_wishlist}', '{$product.id_product}', '{$product.id_product_attribute}', $('#quantity_{$product.id_product}_{$product.id_product_attribute}').val(), $('#priority_{$product.id_product}_{$product.id_product_attribute}').val());" title="{l s='Save' mod='blockwishlist'}">{l s='Save' mod='blockwishlist'}</a>
		</li>
	</ul>
	{if $product.quantity|intval eq 0}
	</div>
	{/if}
	{/foreach}
{/if}
