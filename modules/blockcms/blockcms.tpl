{if $block == 1}
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
{else}
	<!-- MODULE Block footer -->
	<ul class="block_various_links" id="block_various_links_footer">
		<li class="first_item"><a href="{$link->getPageLink('prices-drop.php')}" title="">{l s='Specials' mod='blockcms'}</a></li>
		<li class="item"><a href="{$link->getPageLink('new-products.php')}" title="">{l s='New products' mod='blockcms'}</a></li>
		<li class="item"><a href="{$link->getPageLink('best-sales.php')}" title="">{l s='Top sellers' mod='blockcms'}</a></li>
		<li class="item"><a href="{$link->getPageLink('contact-form.php', true)}" title="">{l s='Contact us' mod='blockcms'}</a></li>
		{foreach from=$cmslinks item=cmslink}
			<li class="item"><a href="{$cmslink.link|addslashes}" title="{$cmslink.meta_title|escape:'htmlall':'UTF-8'}">{$cmslink.meta_title|escape:'htmlall':'UTF-8'}</a></li>
		{/foreach}
		<li class="last_item">{l s='Powered by' mod='blockcms'} <a href="http://www.prestashop.com">PrestaShop</a>&trade;</li>
	</ul>
	<!-- /MODULE Block footer -->
{/if}
