<!-- Block informations module -->
<div id="informations_block_left" class="block">
	<h4>{l s='Information' mod='blockinfos'}</h4>
	<ul class="block_content">
		{foreach from=$cmslinks item=cmslink}
			<li><a href="{$cmslink.link}" title="{$cmslink.meta_title|escape:htmlall:'UTF-8'}">{$cmslink.meta_title|escape:htmlall:'UTF-8'}</a></li>
		{/foreach}
	</ul>
</div>
<!-- /Block informations module -->
