<!-- Block tags module -->
<div id="tags_block_left" class="block tags_block">
	<h4>{l s='Tags' mod='blocktags'}</h4>
	<p class="block_content">
{if $tags}
	{foreach from=$tags item=tag name=myLoop}
		<a href="{$base_dir}search.php?tag={$tag.name|urlencode}" title="{l s='More about' mod='blocktags'} {$tag.name|escape:htmlall:'UTF-8'}" class="{$tag.class} {if $smarty.foreach.myLoop.last}last_item{elseif $smarty.foreach.myLoop.first}first_item{else}item{/if}">{$tag.name|escape:htmlall:'UTF-8'}</a>
	{/foreach}
{else}
	{l s='No tags specified yet' mod='blocktags'}
{/if}
	</p>
</div>
<!-- /Block tags module -->
