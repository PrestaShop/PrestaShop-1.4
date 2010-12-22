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

<!-- Block layered navigation module -->
<div id="layered_block_left" class="block">
	<h4>{l s='Catalog' mod='blocklayered'}</h4>
	<div class="block_content">
		<form action="#" id="layered_form">
			{if isset($layered_subcategories)}
			<span class="layered_subtitle">{l s='Shop by category:' mod='blocklayered'}</span>
			<ul id="layered_subcategories">
			{foreach from=$layered_subcategories item=layered_subcategory}
				<li{if $layered_use_checkboxes} class="nomargin"{/if}>
				{if $layered_use_checkboxes}<input type="checkbox" class="checkbox" name="layered_category_{$layered_subcategory.id_category}" id="layered_category_{$layered_subcategory.id_category}" value="{$layered_subcategory.id_category}" /> {/if}
				{$layered_subcategory.name|escape:html:'UTF-8'} ({$layered_subcategory.n})</li>
			{/foreach}
			</ul>
			{/if}
			<p><input type="hidden" name="id_category_layered" value="{$id_category_layered}" /></p>
		</form>
	</div>
</div>
<!-- /Block layered navigation module -->