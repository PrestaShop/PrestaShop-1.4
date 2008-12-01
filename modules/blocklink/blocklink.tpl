<!-- Block links module -->
<div id="links_block_left" class="block">
	<h4>
	{if $url}
		<a href="{$url}">{$title}</a>
	{else}
		{$title}
	{/if}
	</h4>
	<ul class="block_content bullet">
	{foreach from=$blocklink_links item=blocklink_link}
		<li><a href="{$blocklink_link.url|htmlentities}"{if $blocklink_link.newWindow} onclick="window.open(this.href);return false;"{/if}>{$blocklink_link.$lang}</a></li>
	{/foreach}
	</ul>
</div>
<!-- /Block links module -->
