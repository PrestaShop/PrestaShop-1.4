<!-- MODULE Block specials -->
<div id="special_block_right" class="block products_block exclusive blockspecials">
	<h4><a href="{$base_dir}prices-drop.php" title="{l s='Specials' mod='blockspecials'}">{l s='Specials' mod='blockspecials'}</a></h4>
	<div class="block_content">
{if $special}
		<ul class="products">
			<li class="product_image">
				<a href="{$special.link}"><img src="{$link->getImageLink($special.link_rewrite, $special.id_image, 'medium')}" alt="{$special.legend|escape:htmlall:'UTF-8'}" height="{$mediumSize.height}" width="{$mediumSize.width}" title="{$special.name|escape:htmlall:'UTF-8'}" /></a>
			</li>
			<li>
				<h5><a href="{$special.link}" title="{$special.name|escape:htmlall:'UTF-8'}">{$special.name|escape:htmlall:'UTF-8'}</a></h5>
				<span class="price-discount">{displayWtPrice p=$special.price_without_reduction}</span>
				{if $special.reduction_percent}<span class="reduction">(-{$special.reduction_percent}%)</span>{/if}
				{if !$priceDisplay || $priceDisplay == 2}<span class="price">{displayWtPrice p=$special.price}</span>{if $priceDisplay == 2} {l s='+Tx'}{/if}{/if}
				{if $priceDisplay == 2}<br />{/if}
				{if $priceDisplay}<span class="price">{displayWtPrice p=$special.price_tax_exc}</span>{if $priceDisplay == 2} {l s='-Tx'}{/if}{/if}
			</li>
		</ul>
		<p>
			<a href="{$base_dir}prices-drop.php" title="{l s='All specials' mod='blockspecials'}" class="button">{l s='All specials' mod='blockspecials'}</a>
		</p>
{else}
		<p>{l s='No specials at this time' mod='blockspecials'}</p>
{/if}
	</div>
</div>
<!-- /MODULE Block specials -->