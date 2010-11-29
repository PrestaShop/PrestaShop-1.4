<!-- MODULE Block specials -->
<div id="special_block_right" class="block products_block exclusive blockspecials">
	<h4><a href="{$link->getPageLink('prices-drop.php')}" title="{l s='Specials' mod='blockspecials'}">{l s='Specials' mod='blockspecials'}</a></h4>
	<div class="block_content">
{if $special}
		<ul class="products">
			<li class="product_image">
				<a href="{$special.link}"><img src="{$link->getImageLink($special.link_rewrite, $special.id_image, 'medium')}" alt="{$special.legend|escape:html:'UTF-8'}" height="{$mediumSize.height}" width="{$mediumSize.width}" title="{$special.name|escape:html:'UTF-8'}" /></a>
			</li>
			<li>
				<h5><a href="{$special.link}" title="{$special.name|escape:html:'UTF-8'}">{$special.name|escape:html:'UTF-8'}</a></h5>
				<span class="price-discount">{if !$priceDisplay}{displayWtPrice p=$special.price_without_reduction}{else}{displayWtPrice p=$priceWithoutReduction_tax_excl}{/if}</span>
				{if isset($special.reduction_percent) && $special.reduction_percent > 0}<span class="reduction">(-{$special.reduction_percent}%)</span>{/if}
				<span class="price">{if !$priceDisplay}{displayWtPrice p=$special.price}{else}{displayWtPrice p=$special.price_tax_exc}{/if}</span>
			</li>
		</ul>
		<p>
			<a href="{$link->getPageLink('prices-drop.php')}" title="{l s='All specials' mod='blockspecials'}" class="button_large">{l s='All specials' mod='blockspecials'}</a>
		</p>
{else}
		<p>{l s='No specials at this time' mod='blockspecials'}</p>
{/if}
	</div>
</div>
<!-- /MODULE Block specials -->