{if !$content_only}
	{capture name=path}{l s=$cms->meta_title}{/capture}
	{include file=$tpl_dir./breadcrumb.tpl}
{/if}
{if $cms}
	<div class="rte{if $content_only} content_only{/if}">
		{$cms->content}
	</div>
{else}
	{l s='This page does not exist.'}
{/if}
<br />
{if !$content_only}
	<p><a href="{$base_dir}" title="{l s='Home'}"><img src="{$img_dir}icon/home.gif" alt="{l s='Home'}" class="icon" /></a><a href="{$base_dir}" title="{l s='Home'}">{l s='Home'}</a></p>
{/if}