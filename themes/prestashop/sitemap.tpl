{capture name=path}{l s='Sitemap'}{/capture}
{include file=$tpl_dir./breadcrumb.tpl}

<h2>{l s='Sitemap'}</h2>
<div id="sitemap_content">
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
		{if $smarty.foreach.sitemapTree.last}
			{include file=$tpl_dir./category-tree-branch.tpl node=$child last='true'}
		{else}
			{include file=$tpl_dir./category-tree-branch.tpl node=$child}
		{/if}
	{/foreach}
	</ul>
</div>
<div class="categTree">
	<h3>{l s='Pages'}</h3>
	<div class="tree_top"><a href="{$categoriescmsTree.link}">{$categoriescmsTree.name|escape:'htmlall':'UTF-8'}</a></div>
	<ul class="tree">
		{foreach from=$categoriescmsTree.children item=child name=sitemapCmsTree}
			{if $child.children|@count > 0 || $child.cms|@count > 0}
				{include file=$tpl_dir./category-cms-tree-branch.tpl node=$child}
			{/if}
		{/foreach}
		{foreach from=$categoriescmsTree.cms item=cms name=cmsTree}
			<li><a href="{$cms.link|escape:'htmlall':'UTF-8'}" title="{$cms.meta_title|escape:'htmlall':'UTF-8'}">{$cms.meta_title|escape:'htmlall':'UTF-8'}</a></li>
		{/foreach}
		<li><a href="{$link->getPageLink('contact-form.php', true)}">{l s='Contact'}</a></li>
		<li class="last"><a href="{$link->getPageLink('stores.php')}" title="{l s='Our stores'}">{l s='Our stores'}</a></li>
	</ul>
</div>
