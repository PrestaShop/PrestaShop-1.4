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

<!-- MODULE Block best sellers -->
<div id="best-sellers_block_right" class="block products_block">
	<h4><a href="{$base_dir}best-sales.php">{l s='Top sellers' mod='blockbestsellers'}</a></h4>
	<div class="block_content">
	{if $best_sellers|@count > 0}
		<ul class="product_images">
			<li><a href="{$best_sellers.0.link}" title="{$best_sellers.0.legend}"><img src="{$link->getImageLink($best_sellers.0.link_rewrite, $best_sellers.0.id_image, 'medium')}" height="{$mediumSize.height}" width="{$mediumSize.width}" alt="{$best_sellers.0.legend}" /></a></li>
			{if $best_sellers|@count > 1}<li><a href="{$best_sellers.1.link}" title="{$best_sellers.1.legend}"><img src="{$link->getImageLink($best_sellers.1.link_rewrite, $best_sellers.1.id_image, 'medium')}" height="{$mediumSize.height}" width="{$mediumSize.width}" alt="{$best_sellers.1.legend}" /></a></li>{/if}
		</ul>
		<dl>
		{foreach from=$best_sellers item=product name=myLoop}
			<dt class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if}"><a href="{$product.link}" title="{$product.name|escape:'htmlall':'UTF-8'}">{$product.name|escape:'htmlall':'UTF-8'}</a></dt>
		{/foreach}
		</dl>
		<p><a href="{$base_dir}best-sales.php" title="{l s='All best sellers' mod='blockbestsellers'}" class="button_large">{l s='All best sellers' mod='blockbestsellers'}</a></p>
	{else}
		<p>{l s='No best sellers at this time' mod='blockbestsellers'}</p>
	{/if}
	</div>
</div>
<!-- /MODULE Block best sellers -->
