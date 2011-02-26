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
			<div>
				{if isset($layered_subcategories) && sizeof($layered_subcategories)}
				<span class="layered_subtitle">{l s='Category:' mod='blocklayered'}</span>
				<ul id="layered_subcategories">
				{foreach from=$layered_subcategories item=layered_subcategory}
					<li{if $layered_use_checkboxes} class="nomargin"{/if}>
					{if $layered_use_checkboxes}<input type="checkbox" class="checkbox" name="layered_category_{$layered_subcategory.id_category}" id="layered_category_{$layered_subcategory.id_category}" value="{$layered_subcategory.id_category}"{if isset($layered_subcategory.checked) && $layered_subcategory.checked} checked="checked"{/if}{if !$layered_subcategory.n} disabled="disabled"{/if} /> {/if}
					<label for="layered_category_{$layered_subcategory.id_category}"{if !$layered_subcategory.n} class="disabled"{/if}>{$layered_subcategory.name|escape:html:'UTF-8'} ({$layered_subcategory.n})</label></li>
				{/foreach}
				</ul>
				{/if}
				{if isset($layered_manufacturers) && sizeof($layered_manufacturers)}
				<span class="layered_subtitle">{l s='Manufacturer:' mod='blocklayered'}</span>
				<ul id="layered_manufacturers">
				{foreach from=$layered_manufacturers item=layered_manufacturer}
					<li{if $layered_use_checkboxes} class="nomargin"{/if}>
					{if $layered_use_checkboxes}<input type="checkbox" class="checkbox" name="layered_manufacturer_{$layered_manufacturer.id_manufacturer}" id="layered_manufacturer_{$layered_manufacturer.id_manufacturer}" value="{$layered_manufacturer.id_manufacturer}"{if isset($layered_manufacturer.checked) && $layered_manufacturer.checked} checked="checked"{/if}{if !$layered_manufacturer.n} disabled="disabled"{/if} /> {/if}
					<label for="layered_manufacturer_{$layered_manufacturer.id_manufacturer}"{if !$layered_manufacturer.n} class="disabled"{/if}>{$layered_manufacturer.name|escape:html:'UTF-8'} ({$layered_manufacturer.n})</label></li>
				{/foreach}
				</ul>
				{/if}
				{if isset($layered_features) && sizeof($layered_features)}
				{foreach from=$layered_features item=layered_feature}
					<span class="layered_subtitle">{$layered_feature.name|escape:html:'UTF-8'}{l s=':' mod='blocklayered'}</span>
					<ul id="layered_features">
					{foreach from=$layered_feature.values key=id_feature_value item=layered_feature_value}
						<li{if $layered_use_checkboxes} class="nomargin"{/if}>
						{if $layered_use_checkboxes}<input type="checkbox" class="checkbox" name="layered_feature_{$layered_feature_value.id_feature_value}" id="layered_feature_{$layered_feature_value.id_feature_value}" value="{$layered_feature_value.id_feature_value}"{if isset($layered_feature_value.checked) && $layered_feature_value.checked} checked="checked"{/if}{if !$layered_feature_value.n} disabled="disabled"{/if} /> {/if}
						<label for="layered_feature_{$layered_feature_value.id_feature_value}"{if !$layered_feature_value.n} class="disabled"{/if}>{$layered_feature_value.name|escape:html:'UTF-8'} ({$layered_feature_value.n})</label></li>
					{/foreach}
				{/foreach}
				</ul>
				{/if}
				{if isset($layered_conditions) && sizeof($layered_conditions)}
				<span class="layered_subtitle">{l s='Condition:' mod='blocklayered'}</span>
				<ul id="layered_conditions">
				{foreach from=$layered_conditions key=layered_value item=layered_condition}
					<li{if $layered_use_checkboxes} class="nomargin"{/if}>
					{if $layered_use_checkboxes}<input type="checkbox" class="checkbox" name="layered_condition_{$layered_value}" id="layered_condition_{$layered_value}" value="{$layered_value}"{if isset($layered_condition.checked) && $layered_condition.checked} checked="checked"{/if}{if !$layered_condition.n} disabled="disabled"{/if} /> {/if}
					<label for="layered_condition_{$layered_value}"{if !$layered_condition.n} class="disabled"{/if}>{$layered_condition.name|escape:html:'UTF-8'} ({$layered_condition.n})</label></li>
				{/foreach}
				</ul>
				{/if}
				<p><input type="hidden" name="id_category_layered" value="{$id_category_layered}" /></p>
			</div>
		</form>
	</div>
	<div id="layered_ajax_loader" style="display: none;">
		<p style="margin: 20px 0; text-align: center;"><img src="{$img_ps_dir}loader.gif" alt="" /><br />{l s='Loading...' mod='blocklayered'}</p>
	</div>
</div>
<!-- /Block layered navigation module -->