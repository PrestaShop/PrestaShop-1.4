<!-- MODULE Block best sellers -->
<div id="best-sellers_block_right" class="block products_block">
	<h4><a href="{$base_dir}best-sales.php">{l s='Top sellers' mod='blockbestsellers'}</a></h4>
	<div class="block_content">
	{if $best_sellers|@count > 0}
		<ul class="product_images">
			<li><a href="{$best_sellers.0.link}" title="{$best_sellers.0.legend}"><img src="{$link->getImageLink($best_sellers.0.link_rewrite, $best_sellers.0.id_image, 'medium')}" height="{$mediumSize.height}" width="{$mediumSize.width}" alt="{$best_sellers.0.legend}" /></a></li>
			{if $best_sellers|@count > 1}<li><a href="{$best_sellers.1.link}" title="{$best_sellers.1.legend}"><img src="{$link->getImageLink($best_sellers.1.link_rewrite, $best_sellers.1.id_image, 'medium')}" height="{$mediumSize.height}" width="{$mediumSize.width}" alt="{$best_sellers.1.legend}" /></a></li>{/if}
		</ul>
		<dl>
		{foreach from=$best_sellers item=product name=myLoop}
			<dt class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if}"><a href="{$product.link}" title="{$product.name}">{$product.name}</a></dt>
			{if $product.description_short}<dd class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if}"></dd>{/if}
		{/foreach}
		</dl>
		<p><a href="{$base_dir}best-sales.php" title="{l s='All best sellers' mod='blockbestsellers'}" class="button_large">{l s='All best sellers' mod='blockbestsellers'}</a></p>
	{else}
		<p>{l s='No best sellers at this time' mod='blockbestsellers'}</p>
	{/if}
	</div>
</div>
<!-- /MODULE Block best sellers -->
