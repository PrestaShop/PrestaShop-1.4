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
*  @copyright  2007-2010 Prestashop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*}

{if count($categoryProducts) > 0}
<script type="text/javascript">var middle = {$middlePosition};</script>
<script type="text/javascript" src="{$content_dir}modules/productscategory/js/productscategory.js"></script>
<ul class="idTabs">
	<li><a href="#idTab3">{l s='In the same category' mod='productscategory'}</a></li>
</ul>

<div id="{if count($categoryProducts) > 5}productscategory{else}productscategory_noscroll{/if}">
{if count($categoryProducts) > 5}<a id="productscategory_scroll_left" title="{l s='Previous' mod='productscategory'}" href="javascript:{ldelim}{rdelim}">{l s='Previous' mod='productscategory'}</a>{/if}
<div id="productscategory_list">
	<ul {if count($categoryProducts) > 5}style="width: {math equation="width * nbImages" width=107 nbImages=$categoryProducts|@count}px"{/if}>
		{foreach from=$categoryProducts item='categoryProduct' name=categoryProduct}
		<li {if count($categoryProducts) < 6}style="width: {math equation="width / nbImages" width=94 nbImages=$categoryProducts|@count}%"{/if}>
			<a href="{$link->getProductLink($categoryProduct.id_product, $categoryProduct.link_rewrite, $categoryProduct.category)}" title="{$categoryProduct.name|htmlspecialchars}">
				<img src="{$link->getImageLink($categoryProduct.link_rewrite, $categoryProduct.id_image, 'medium')}" alt="{$categoryProduct.name|htmlspecialchars}" />
			</a><br/>
			<a href="{$link->getProductLink($categoryProduct.id_product, $categoryProduct.link_rewrite, $categoryProduct.category, $categoryProduct.ean13)}" title="{$categoryProduct.name|htmlspecialchars}">
			{$categoryProduct.name|truncate:15:'...'|escape:'htmlall':'UTF-8'}
			</a><br />
			{if $ProdDisplayPrice AND $categoryProduct.show_price == 1 AND !isset($restricted_country_mode)}
				<span class="price_display">
					<span class="price">{convertPrice price=$categoryProduct.displayed_price}</span>
				</span><br />
			{else}
				<br />
			{/if}
			<a title="{l s='View' mod='productscategory'}" href="{$link->getProductLink($categoryProduct.id_product, $categoryProduct.link_rewrite, $categoryProduct.category, $categoryProduct.ean13)}" class="button_small">{l s='View' mod='productscategory'}</a><br />
		</li>
		{/foreach}
	</ul>
</div>
{if count($categoryProducts) > 5}<a id="productscategory_scroll_right" title="{l s='Next' mod='productscategory'}" href="javascript:{ldelim}{rdelim}">{l s='Next' mod='productscategory'}</a>{/if}
</div>
{/if}
