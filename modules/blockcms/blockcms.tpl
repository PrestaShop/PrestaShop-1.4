<!-- Block CMS module -->
{foreach from=$cms_titles item=cms_title}
	<div id="informations_block_left" class="block">
		<h4><a href="{$cms_title.category_link}">{if !empty($cms_title.name)}{$cms_title.name}{else}{$cms_title.category_name}{/if}</a></h4>
		<ul class="block_content">
			{foreach from=$cms_title.categories item=cms_page}
				{if isset($cms_page.link)}<li class="bullet"><b style="margin-left:2em;">
				<a href="{$cms_page.link}" title="{$cms_page.name|escape:html:'UTF-8'}">{$cms_page.name|escape:html:'UTF-8'}</a>
				</b></li>{/if}
			{/foreach}
			{foreach from=$cms_title.cms item=cms_page}
				{if isset($cms_page.link)}<li><a href="{$cms_page.link}" title="{$cms_page.meta_title|escape:html:'UTF-8'}">{$cms_page.meta_title|escape:html:'UTF-8'}</a></li>{/if}
			{/foreach}
		</ul>
	</div>
{/foreach}
<!-- /Block CMS module -->
