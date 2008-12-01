{if $products}
	<br />
	<a href="javascript:;" id="hideBoughtProducts" class="button_account_large"  onclick="WishlistVisibility('wlp_bought', 'BoughtProducts');">{l s='Hide bought products' mod='blockwishlist'}</a>
	<a href="javascript:;" id="showBoughtProducts" class="button_account_large"  onclick="WishlistVisibility('wlp_bought', 'BoughtProducts');">{l s='Show bought products' mod='blockwishlist'}</a>
	{if count($productsBoughts)}
	<a href="javascript:;" id="hideBoughtProductsInfos" class="button_account_large" onclick="WishlistVisibility('wlp_bought_infos', 'BoughtProductsInfos');">{l s='Hide bought product\'s infos' mod='blockwishlist'}</a>
	<a href="javascript:;" id="showBoughtProductsInfos" class="button_account_large"  onclick="WishlistVisibility('wlp_bought_infos', 'BoughtProductsInfos');">{l s='Show bought product\'s infos' mod='blockwishlist'}</a>
	{/if}
	<a href="javascript:;" id="showSendWishlist" class="button_account" onclick="WishlistVisibility('wl_send', 'SendWishlist');">{l s='Send this wishlist' mod='blockwishlist'}</a>
	<a href="javascript:;" id="hideSendWishlist" class="button_account" onclick="WishlistVisibility('wl_send', 'SendWishlist');">{l s='Close send this wishlist' mod='blockwishlist'}</a>
	<span class="clear"></span>
	<br />
	<form class="wl_send std hidden" method="post" class="hidden" onsubmit="return (false);">
		<fieldset>
			<p class="required">
				<label for="email1">{l s='Email' mod='blockwishlist'}1</label>
				<input type="text" name="email1" id="email1" />
				<sup>*</sup>
			</p>
			{section name=i loop=11 start=2}
			<p>
				<label for="email{$smarty.section.i.index}">{l s='Email' mod='blockwishlist'}{$smarty.section.i.index}</label>
				<input type="text" name="email{$smarty.section.i.index}" id="email{$smarty.section.i.index}" />
			</p>
			{/section}
			<p class="submit">
				<input class="button" type="submit" value="{l s='Send' mod='blockwishlist'}" name="submitWishlist" onclick="WishlistSend('wl_send', '{$id_wishlist}', 'email');" />
			</p>
			<p class="required">
				<sup>*</sup>
				{l s='Required field'}
			</p>
		</fieldset>
	</form>
	{if count($productsBoughts)}
	<table class="wlp_bought_infos hidden std">
		<thead>
			<tr>
				<th class="first_item">{l s='Product' mod='blockwishlist'}</td>
				<th class="item">{l s='Quantity' mod='blockwishlist'}</td>
				<th class="item">{l s='Offered by' mod='blockwishlist'}</td>
				<th class="last_item">{l s='Date' mod='blockwishlist'}</td>
			</tr>
		</thead>
		<tbody>
		{foreach from=$productsBoughts item=product name=i}
			{foreach from=$product.bought item=bought name=j}
			<tr>
				<td class="first_item">
				<span style="float:left;"><img src="{$img_prod_dir}{$product.cover}-small.jpg" alt="{$product.name|escape:'htmlall':'UTF-8'}" /></span>
				<span style="float:left;">{$product.name|truncate:40|escape:'htmlall':'UTF-8'}
				{if isset($product.attributes_small)}
					<br /><i>{$product.attributes_small|escape:'htmlall':'UTF-8'}</i>
				{/if}</span>
				</td>
				<td class="item align_center">{$bought.quantity|intval}</td>
				<td class="item align_center">{$bought.firstname} {$bought.lastname}</td>
				<td class="last_item align_center">{$bought.date_add|date_format:"%Y-%m-%d"}</td>
			</tr>
			{/foreach}
		{/foreach}
		</tbody>
	</table>
	{/if}
	{include file="managewishlistproduct.tpl" title="managewishlistproduct"}
{else}
	{l s='No products' mod='blockwishlist'}
{/if}
