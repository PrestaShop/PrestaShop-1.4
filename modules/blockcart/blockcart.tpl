{*************************************************************************************************************************************}
{* IMPORTANT : If you change some data here, you have to report these changes in the ./blockcart-json.js (to let ajaxCart available) *}
{*************************************************************************************************************************************}

{if $ajax_allowed}
<script type="text/javascript" src="{$content_dir}js/jquery/iutil.prestashop-modifications.js"></script>
{* to perfectly play the tranfert animation, the script ifx.js has to be called here, but it creates a method conflict with jquery.serialScroll.js file *}
<script type="text/javascript" src="{$content_dir}js/jquery/ifxtransfer.js"></script>
<script type="text/javascript">
var CUSTOMIZE_TEXTFIELD = {$CUSTOMIZE_TEXTFIELD};
var customizationIdMessage = '{l s='Customization #' mod='blockcart' js=1}';
var removingLinkText = '{l s='remove this product from my cart' mod='blockcart' js=1}';
</script>
<script type="text/javascript" src="{$content_dir}modules/blockcart/ajax-cart.js"></script>
{/if}

<!-- MODULE Block cart -->
<div id="cart_block" class="block exclusive">
	<h4>
		<a href="{$base_dir_ssl}order.php">{l s='Cart' mod='blockcart'}</a>
		{if $ajax_allowed}
		<span id="block_cart_expand" {if $colapseExpandStatus eq 'expanded'}class="hidden"{/if}>&nbsp;</span>
		<span id="block_cart_collapse" {if $colapseExpandStatus eq 'collapsed' || !isset($colapseExpandStatus)}class="hidden"{/if}>&nbsp;</span>
		{/if}
	</h4>
	<div class="block_content">
	<!-- block summary -->
	<div id="cart_block_summary" class="{if $colapseExpandStatus eq 'expanded' || !$ajax_allowed}collapsed{else}expanded{/if}">
		<span class="ajax_cart_quantity">{if $cart_qties > 0}{$cart_qties}{/if}</span>
		<span class="ajax_cart_product_txt_s{if $cart_qties < 2} hidden{/if}">{l s='products' mod='blockcart'}</span>
		<span class="ajax_cart_product_txt{if $cart_qties != 1} hidden{/if}">{l s='product' mod='blockcart'}</span>
		<span class="ajax_cart_total">{if $cart_qties > 0}{if $priceDisplay == 1}{convertPrice price=$cart->getOrderTotal(false)}{else}{convertPrice price=$cart->getOrderTotal(true)}{/if}{/if}</span>
		<span class="ajax_cart_no_product">{if $cart_qties == 0}{l s='(empty)' mod='blockcart'}{/if}</span>
	</div>
	<!-- block list of products -->
	<div id="cart_block_list" class="{if $colapseExpandStatus eq 'expanded' || !$ajax_allowed}expanded{else}collapsed{/if}">
	{if $products}
		<dl class="products">
		{foreach from=$products item='product' name='myLoop'}
			{assign var='productId' value=$product.id_product}
			{assign var='productAttributeId' value=$product.id_product_attribute}
			<dt id="cart_block_product_{$product.id_product}{if $product.id_product_attribute}_{$product.id_product_attribute}{/if}" class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if}">
				<span class="quantity-formated"><span class="quantity">{$product.cart_quantity}</span>x</span>
				<a class="cart_block_product_name" href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category)}" title="{$product.name|escape:htmlall:'UTF-8'}">{t text=$product.name length='16' encode='true'}</a>
				<span class="remove_link">{if !isset($customizedDatas.$productId.$productAttributeId)}<a class="ajax_cart_block_remove_link" href="{$base_dir}cart.php?delete&amp;id_product={$product.id_product}&amp;ipa={$product.id_product_attribute}&amp;token={$static_token}" title="{l s='remove this product from my cart' mod='blockcart'}">&nbsp;</a>{/if}</span>
				<span class="price">{displayWtPrice p="`$product.real_price`"}</span>
			</dt>
			{if isset($product.attributes_small)}
			<dd id="cart_block_combination_of_{$product.id_product}{if $product.id_product_attribute}_{$product.id_product_attribute}{/if}" class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if}">
				<a href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category)}" title="{l s='Product detail'}">{$product.attributes_small}</a>
			{/if}

			<!-- Customizable datas -->
			{if isset($customizedDatas.$productId.$productAttributeId)}
				{if !isset($product.attributes_small)}<dd id="cart_block_combination_of_{$product.id_product}{if $product.id_product_attribute}_{$product.id_product_attribute}{/if}" class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if}">{/if}
				<ul class="cart_block_customizations" id="customization_{$productId}_{$productAttributeId}">
					{foreach from=$customizedDatas.$productId.$productAttributeId key='id_customization' item='customization' name='customizations'}
						<li name="customization">
							<div class="deleteCustomizableProduct" id="deleteCustomizableProduct_{$id_customization|intval}_{$product.id_product|intval}_{$product.id_product_attribute|intval}"><a class="ajax_cart_block_remove_link" href="{$base_dir}cart.php?delete&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;token={$static_token}"> </a></div>
							<span class="quantity-formated"><span class="quantity">{$customization.quantity}</span>x</span>{if isset($customization.datas.$CUSTOMIZE_TEXTFIELD.0)}{t text=$customization.datas.$CUSTOMIZE_TEXTFIELD.0.value length='28' encode='true'}
							{else}
							{l s='Customization #' mod='blockcart'}{$id_customization|intval}{l s=':' mod='blockcart'}
							{/if}
						</li>
					{/foreach}
				</ul>
				{if !isset($product.attributes_small)}</dd>{/if}
			{/if}

			{if isset($product.attributes_small)}</dd>{/if}

		{/foreach}
		</dl>
	{/if}
		<p {if $products}class="hidden"{/if} id="cart_block_no_products">{l s='No products' mod='blockcart'}</p>
		
		{if $discounts|@count > 0}<table id="vouchers">
			<tbody>
			{foreach from=$discounts item=discount}
				<tr id="bloc_cart_voucher_{$discount.id_discount}">
					<td class="name" title="{$discount.description}">{$discount.name|cat:' : '|cat:$discount.description|truncate:18:'...'|escape:'htmlall':'UTF-8'}</td>
					<td class="price">-{if $priceDisplay == 1}{convertPrice price=$discount.value_tax_exc}{else}{convertPrice price=$discount.value_real}{/if}</td>
					<td class="delete"><a href="{$base_dir_ssl}order.php?deleteDiscount={$discount.id_discount}" title="{l s='Delete'}"><img src="{$img_dir}icon/delete.gif" alt="{l s='Delete'}" class="icon" /></a></td>
				</tr>
			{/foreach}
			</tbody>
		</table>
		{/if}
		
		<p id="cart-prices">
			<span>{l s='Shipping' mod='blockcart'}</span>
			<span id="cart_block_shipping_cost" class="price ajax_cart_shipping_cost">{$shipping_cost}</span>
			<br/>
			{if $show_wrapping}
				<span>{l s='Wrapping' mod='blockcart'}</span>
				<span id="cart_block_wrapping_cost" class="price cart_block_wrapping_cost">{if $priceDisplay == 1}{convertPrice price=$cart->getOrderTotal(false, 6)}{else}{convertPrice price=$cart->getOrderTotal(true, 6)}{/if}</span>
				<br/>
			{/if}
			<span>{l s='Total' mod='blockcart'}</span>
			<span id="cart_block_total" class="price ajax_block_cart_total">{$total}</span>
		</p>
		{if $priceDisplay == 2}
			<p id="cart-price-precisions">
				{l s='Prices are tax included' mod='blockcart'}
			</p>
		{/if}
		{if $priceDisplay == 1}
			<p id="cart-price-precisions">
				{l s='Prices are tax excluded' mod='blockcart'}
			</p>
		{/if}
		<p id="cart-buttons">
			<a href="{$base_dir_ssl}order.php" class="button_small" title="{l s='Cart' mod='blockcart'}">{l s='Cart' mod='blockcart'}</a>
			<a href="{$base_dir_ssl}order.php?step=1" id="button_order_cart" class="exclusive" title="{l s='Check out' mod='blockcart'}">{l s='Check out' mod='blockcart'}</a>
		</p>
	</div>
	</div>
</div>
<!-- /MODULE Block cart -->
