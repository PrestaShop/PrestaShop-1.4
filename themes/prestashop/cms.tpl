{if $cms}
	{if $content_only}
	<div style="text-align:left; padding:10px;">
		{$cms->content}
	</div>
	{else}
		{$cms->content}
	{/if}
{else}
	{l s='This page does not exist.'}
{/if}
<br />
{if !$content_only}
<p><a href="{$base_dir}" title="{l s='Home'}"><img src="{$img_dir}icon/home.gif" alt="{l s='Home'}" class="icon" /></a><a href="{$base_dir}" title="{l s='Home'}">{l s='Home'}</a></p>
{/if}

