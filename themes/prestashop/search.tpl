{*
* 2007-2010 PrestaShop 
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
*  @copyright  2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*}

{capture name=path}{l s='Search'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h1 {if isset($instantSearch) && $instantSearch}id="instant_search_results"{/if}>
{l s='Search'}&nbsp;{if $nbProducts > 0}"{if isset($search_query) && $search_query}{$search_query|escape:'htmlall':'UTF-8'}{elseif $search_tag}{$search_tag|escape:'htmlall':'UTF-8'}{elseif $ref}{$ref|escape:'htmlall':'UTF-8'}{/if}"{/if}
{if isset($instantSearch) && $instantSearch}<a href="#" class="close">{l s='Back to the last page'}</a>{/if}
</h1>

{include file="$tpl_dir./errors.tpl"}
{if !$nbProducts}
	<p class="warning">
		{if isset($search_query) && $search_query}
			{l s='No results found for your search'}&nbsp;"{if isset($query)}{$query|escape:'htmlall':'UTF-8'}{/if}"
		{elseif isset($search_tag) && $search_tag}
			{l s='No results found for your search'}&nbsp;"{$tag|escape:'htmlall':'UTF-8'}"
		{else}
			{l s='Please type a search keyword'}
		{/if}
	</p>
{else}
	<h3><span class="big">{$nbProducts|intval}</span>&nbsp;{if $nbProducts == 1}{l s='result has been found.'}{else}{l s='results have been found.'}{/if}</h3>
	{if !isset($instantSearch) || (isset($instantSearch) && !$instantSearch)}{include file="$tpl_dir./product-sort.tpl"}{/if}
	{include file="$tpl_dir./product-list.tpl" products=$products}
	{if !isset($instantSearch) || (isset($instantSearch) && !$instantSearch)}{include file="$tpl_dir./pagination.tpl"}{/if}
{/if}