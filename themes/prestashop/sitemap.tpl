{capture name=path}{l s='Sitemap'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Sitemap'}</h2>
<div id="sitemap_content">
	{if $blockCMSInstalled}
	{foreach from=$blocks item=block}
		<div class="sitemap_block">
			<h3>{$block.block_name}</h3>
			{assign var='id_block_cms' value=$block.id_block_cms}
			<ul>
				{foreach from=$pages[$id_block_cms] item=page}
					{foreach from=$page item=p}
						<li><a href="{$p.link}" title="{$p.meta_title}">{$p.meta_title}</a></li>
					{/foreach}
				{/foreach}
			</ul>
		</div>
	{/foreach}
	{else}
		<div class="sitemap_block">
			<h3>{l s='Information'}</h3>
			<ul>
				<li><a href="{$link->getPageLink('contact-form.php', true)}">{l s='Contact'}</a></li>
				{foreach from=$cmslinks item=cmslink}
					<li><a href="{$cmslink.link}" title="{$cmslink.meta_title}">{$cmslink.meta_title}</a></li>
				{/foreach}
			</ul>
		</div>
	{/if}
	<div class="sitemap_block">
		<h3>{l s='Our offers'}</h3>
		<ul>
			<li><a href="{$link->getPageLink('new-products.php')}">{l s='New products'}</a></li>
			<li><a href="{$link->getPageLink('best-sales.php')}">{l s='Top sellers'}</a></li>
			<li><a href="{$link->getPageLink('prices-drop.php')}">{l s='Specials'}</a></li>
			<li><a href="{$link->getPageLink('manufacturer.php')}">{l s='Manufacturers'}</a></li>
			<li><a href="{$link->getPageLink('supplier.php')}">{l s='Suppliers'}</a></li>
		</ul>
	</div>
	<div class="sitemap_block">
		<h3>{l s='Your Account'}</h3>
		<ul>
			<li><a href="{$link->getPageLink('my-account.php', true)}">{l s='Your Account'}</a></li>
			<li><a href="{$link->getPageLink('identity.php', true)}">{l s='Personal information'}</a></li>
			<li><a href="{$link->getPageLink('addresses.php', true)}">{l s='Addresses'}</a></li>
			{if $voucherAllowed}<li><a href="{$link->getPageLink('discount.php', true)}">{l s='Discount'}</a></li>{/if}
			<li><a href="{$link->getPageLink('history.php', true)}">{l s='Orders history'}</a></li>
		</ul>
	</div>
	<br class="clear" />
</div>
<div class="categTree">
	<h3>{l s='Categories'}</h3>
	<div class="tree_top"><a href="{$base_dir_ssl}">{$categoriesTree.name|escape:'htmlall':'UTF-8'}</a></div>
	<ul class="tree">
	{foreach from=$categoriesTree.children item=child name=sitemapTree}
		{if isset($smarty.foreach.sitemapTree.last) && $smarty.foreach.sitemapTree.last}
			{include file="$tpl_dir./category-tree-branch.tpl" node=$child last='true'}
		{else}
			{include file="$tpl_dir./category-tree-branch.tpl" node=$child}
		{/if}
	{/foreach}
	</ul>
</div>
