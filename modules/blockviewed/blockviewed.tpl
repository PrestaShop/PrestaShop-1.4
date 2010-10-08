<!-- Block Viewed products -->
<div id="viewed-products_block_left" class="block products_block">
	<h4>{l s='Viewed products' mod='blockviewed'}</h4>
	<div class="block_content">
		<ul class="products clearfix">
			{foreach from=$productsViewedObj item=viewedProduct name=myLoop}
				<li class="clearfix{if $smarty.foreach.myLoop.last} last_item{elseif $smarty.foreach.myLoop.first} first_item{else} item{/if}">
					<a href="{$link->getProductLink($viewedProduct->id, $viewedProduct->link_rewrite, $viewedProduct->category_rewrite)}" title="{l s='More about' mod='blockviewed'} {$viewedProduct->name|escape:html:'UTF-8'}"><img src="{$link->getImageLink($viewedProduct->link_rewrite, $viewedProduct->cover, 'medium')}" height="{$mediumSize.height}" width="{$mediumSize.width}" alt="{$viewedProduct->legend|escape:html:'UTF-8'}" /></a>
					<h5><a href="{$link->getProductLink($viewedProduct->id, $viewedProduct->link_rewrite, $viewedProduct->category_rewrite)}" title="{l s='More about' mod='blockviewed'} {$viewedProduct->name|escape:html:'UTF-8'}">{$viewedProduct->name|truncate:14:'...'|escape:html:'UTF-8'}</a></h5>
					<p>{m s=$viewedProduct->description_short|strip_tags:'UTF-8'|truncate:44 n=12}<a href="{$link->getProductLink($viewedProduct->id, $viewedProduct->link_rewrite, $viewedProduct->category_rewrite)}" title="{l s='More about' mod='blockviewed'} {$viewedProduct->name|escape:html:'UTF-8'}"><img src="{$img_dir}bullet.gif" alt="&gt;&gt;"  /></a></p>
				</li>
			{/foreach}
		</ul>
	</div>
</div>
