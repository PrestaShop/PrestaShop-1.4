{*
* Copyright (C) 2007-2010 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  Copyright (c) 2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*}

{capture name=path}{l s='Sitemap'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h1>{l s='Sitemap'}</h1>
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
<div>
	<div class="categTree">
		<h3>{l s='Categories'}</h3>
		<div class="tree_top"><a href="{$base_dir_ssl}">{$categoriesTree.name|escape:'htmlall':'UTF-8'}</a></div>
		<ul class="tree">
		{foreach from=$categoriesTree.children item=child name=sitemapTree}
			{if $smarty.foreach.sitemapTree.last}
				{include file="$tpl_dir./category-tree-branch.tpl" node=$child last='true'}
			{else}
				{include file="$tpl_dir./category-tree-branch.tpl" node=$child}
			{/if}
		{/foreach}
		</ul>
	</div>
	<div class="categTree">
		<h3>{l s='Pages'}</h3>
		<div class="tree_top"><a href="{$categoriescmsTree.link}">{$categoriescmsTree.name|escape:'htmlall':'UTF-8'}</a></div>
		<ul class="tree">
			{if isset($categoriescmsTree.children)}
				{foreach from=$categoriescmsTree.children item=child name=sitemapCmsTree}
					{if $child.children|@count > 0 || $child.cms|@count > 0}
						{include file="$tpl_dir./category-cms-tree-branch.tpl" node=$child}
					{/if}
				{/foreach}
			{/if}
			{foreach from=$categoriescmsTree.cms item=cms name=cmsTree}
				<li><a href="{$cms.link|escape:'htmlall':'UTF-8'}" title="{$cms.meta_title|escape:'htmlall':'UTF-8'}">{$cms.meta_title|escape:'htmlall':'UTF-8'}</a></li>
			{/foreach}
			<li><a href="{$link->getPageLink('contact-form.php', true)}">{l s='Contact'}</a></li>
			<li class="last"><a href="{$link->getPageLink('stores.php')}" title="{l s='Our stores'}">{l s='Our stores'}</a></li>
		</ul>
	</div>
</div>
