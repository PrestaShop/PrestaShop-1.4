{capture name=path}{l s='Product Comparison'}{/capture}

{include file="$tpl_dir./breadcrumb.tpl"}
<h2>{l s='Product Comparison'}</h2>

{if $hasProduct}
<div class="products_block">
	<table id="product_comparison">
			<td width="20%"></td>
			{assign var='taxes_behavior' value=false}
			{if $use_taxes && (!$priceDisplay  || $priceDisplay == 2)}			
				{assign var='taxes_behavior' value=true}
			{/if}			
		{foreach from=$products item=product name=for_products}
			{assign var='replace_id' value=$product->id|cat:'|'}

			<td width="{$width}%" class="ajax_block_product comparison_infos">
				<h5><a href="{$product->getLink()}" title="{$product->name|truncate:32:'...'|escape:'htmlall':'UTF-8'}">{$product->name|truncate:27:'...'|escape:'htmlall':'UTF-8'}</a></h5>
				<div class="product_desc"><a href="{$product->getLink()}" title="{l s='More'}">{$product->description_short|strip_tags|truncate:130:'...'}</a></div>
				<div class="comparison_product_infos">
				<a href="{$product->getLink()}" title="{$product->name|escape:html:'UTF-8'}" >
					<img src="{$link->getImageLink($product->link_rewrite, $product->id_image, 'home')}" alt="{$product->name|escape:html:'UTF-8'}"/>
					</a>

					<p class="price_container"><span class="price">{convertPrice price=$product->getPrice($taxes_behavior)}</span></p>
					<div class="product_discount">
					{if $product->on_sale}
						<span class="on_sale">{l s='On sale!'}</span>
					{elseif ($product->reduction_price != 0 || $product->reduction_percent != 0) && ($product->reduction_from == $product->reduction_to OR ($smarty.now|date_format:'%Y-%m-%d %H:%M:%S' <= $product->reduction_to && $smarty.now|date_format:'%Y-%m-%d %H:%M:%S' >= $product->reduction_from))}
						<span class="discount">{l s='Price lowered!'}</span>
					{/if}
					</div>
					
					<p class="comparison_unit_price">{if !empty($product->unity) && $product->unit_price > 0.000000}{convertPrice price=$product->unit_price} {l s='per'} {$product->unity|escape:'htmlall':'UTF-8'}{else}&nbsp;{/if}</p>
					
				<!-- availability -->
				<p class="comparison_availability_statut">
					{if !(($product->quantity == 0 && !$product->available_later) OR ($product->quantity != 0 && !$product->available_now) OR !$product->available_for_order)}				
						<span id="availability_label">{l s='Availability:'}</span>
						<span id="availability_value"{if $product->quantity == 0} class="warning-inline"{/if}>
							{if $product->quantity == 0}
								{if $allow_oosp}
									{$product->available_later|escape:'htmlall':'UTF-8'}
								{else}
									{l s='This product is no longer in stock'}
								{/if}
							{else}
								{$product->available_now|escape:'htmlall':'UTF-8'}
							{/if}
						</span>
					{/if}
				</p>								
					<a class="cmp_remove" href="{$request_uri|replace:$replace_id:''}">{l s='Remove'}</a>
					<a class="button" href="{$product->getLink()}" title="{l s='View'}">{l s='View'}</a>
					{if $product->id_product_attribute == 0 OR (isset($add_prod_display) AND ($add_prod_display == 1))}
						{if ($product->quantity > 0 OR $product->allow_oosp) AND $product->customizable != 2}
							<a class="exclusive ajax_add_to_cart_button" rel="ajax_id_product_{$product->id}" href="{$base_dir}cart.php?qty=1&amp;id_product={$product->id}&amp;token={$static_token}&amp;add" title="{l s='Add to cart'}">{l s='Add to cart'}</a>
						{else}
							<span class="exclusive">{l s='Add to cart'}</span>
						{/if}
					{else}
						<div style="height:23px;"></div>
					{/if}
				</div>
			</td>
		{/foreach}
		</tr>
		
		<tr class="comparison_header">
			<td>
				{l s='Features'}
			</td>
			{section loop=$products|count step=1 start=0 name=td}
			<td></td>
			{/section}
		</tr>
		
		{if $ordered_features}		
		{foreach from=$ordered_features item=feature}
		<tr>		
			{cycle values='comparison_feature_odd,comparison_feature_even' assign='classname'}
			<td class="{$classname}" >
				{$feature.name|escape:'htmlall':'UTF-8'}
			</td>

				{foreach from=$products item=product name=for_products}
					{assign var='product_id' value=$product->id}
					{assign var='feature_id' value=$feature.id_feature}
					{assign var='tab' value=$product_features[$product_id]}
					<td  width="{$width}%" class="{$classname} comparison_infos">{$tab[$feature_id]|escape:'htmlall':'UTF-8'}</td>
				{/foreach}		
		</tr>				
		{/foreach}
		{else}
			<tr>
				<td></td>
				<td colspan="{$products|@count + 1}">{l s='No features to compare'}</td>
			</tr>
		{/if}
	
		{$HOOK_EXTRA_PRODUCT_COMPARISON}
	</table>
</div>
{else}	
	<p class="warning">{l s='There is no product in the comparator'}</p>
{/if}
