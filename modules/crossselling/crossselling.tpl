{if count($orderProducts) > 0}
<script type="text/javascript">var middle = {$middlePosition_crossselling};</script>
<script type="text/javascript" src="{$content_dir}modules/crossselling/js/crossselling.js"></script>

<h2>{l s='Customers who bought this product also bought:' mod='crossselling'}</h2>
<div id="{if count($orderProducts) > 5}crossselling{else}crossselling_noscroll{/if}">
{if count($orderProducts) > 5}<a id="crossselling_scroll_left" title="{l s='Previous' mod='crossselling'}" href="javascript:{ldelim}{rdelim}">{l s='Previous' mod='crossselling'}</a>{/if}
<div id="crossselling_list">
	<ul {if count($orderProducts) > 5}style="width: {math equation="width * nbImages" width=107 nbImages=$orderProducts|@count}px"{/if}>
		{foreach from=$orderProducts item='orderProduct' name=orderProduct}
		<li {if count($orderProducts) < 6}style="width: {math equation="width / nbImages" width=94 nbImages=$orderProducts|@count}%"{/if}>
			<a href="{$orderProduct.link}" title="{$orderProduct.name|htmlspecialchars}">
				<img src="{$orderProduct.image}" alt="{$orderProduct.name|htmlspecialchars}" />
			</a><br/>
			<a href="{$orderProduct.link}" title="{$orderProduct.name|htmlspecialchars}">
			{$orderProduct.name|truncate:15:'...'|escape:'htmlall':'UTF-8'}
			</a>
		</li>
		{/foreach}
	</ul>
</div>
{if count($orderProducts) > 5}<a id="crossselling_scroll_right" title="{l s='Next' mod='crossselling'}" href="javascript:{ldelim}{rdelim}">{l s='Next' mod='crossselling'}</a>{/if}
</div>
{/if}
