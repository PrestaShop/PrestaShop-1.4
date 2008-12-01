<!-- Block RSS module-->
<div id="rss_block_left" class="block">
	<h4>{$title}</h4>
	<div class="block_content">
		{if $content}
		<ul>
			{$content}
		</ul>
		{else}
			{l s='No RSS feed added' mod='blockrss'}
		{/if}
	</div>
</div>
<!-- /Block RSS module-->
