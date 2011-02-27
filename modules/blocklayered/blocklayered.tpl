{*
* 2007-2011 PrestaShop 
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
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
				{if isset($layered_filters) && sizeof($layered_filters)}
				<div id="enabled_filters">
					<span class="layered_subtitle" style="float: none;">{l s='Enabled filters:' mod='blocklayered'}</span>
					<ul>					
					{foreach from=$layered_filters item=layered_filter}
						{foreach from=$layered_filter key=layered_dom_id item=layered_filter_value}
							<li><a href="#" rel="{$layered_dom_id}" title="{l s='Cancel this filter' mod='blocklayered'}">x</a> {$layered_filter_value|escape:html:'UTF-8'}</li>
						{/foreach}
					{/foreach}
					</ul>
				</div>
				{/if}
				{if isset($layered_subcategories) && sizeof($layered_subcategories)}
				<div>
					<span class="layered_subtitle">{l s='Category' mod='blocklayered'}</span>
					<span class="layered_close"><a href="#" rel="layered_subcategories">v</a></span>
					<div class="clear"></div>
					<ul id="layered_subcategories">
					{foreach from=$layered_subcategories item=layered_subcategory}
						<li{if $layered_use_checkboxes} class="nomargin"{/if}>
						{if $layered_use_checkboxes}<input type="checkbox" class="checkbox" name="layered_category_{$layered_subcategory.id_category}" id="layered_category_{$layered_subcategory.id_category}" value="{$layered_subcategory.id_category}"{if isset($layered_subcategory.checked) && $layered_subcategory.checked} checked="checked"{/if}{if !$layered_subcategory.n} disabled="disabled"{/if} /> {/if}
						<label for="layered_category_{$layered_subcategory.id_category}"{if !$layered_subcategory.n} class="disabled"{/if}>{$layered_subcategory.name|escape:html:'UTF-8'}<span> ({$layered_subcategory.n})</span></label></li>
					{/foreach}
					</ul>
				</div>
				{/if}
				{if isset($layered_manufacturers) && sizeof($layered_manufacturers)}
				<div>
					<span class="layered_subtitle">{l s='Manufacturer' mod='blocklayered'}</span>
					<span class="layered_close"><a href="#" rel="layered_manufacturers">v</a></span>
					<div class="clear"></div>
					<ul id="layered_manufacturers">
					{foreach from=$layered_manufacturers item=layered_manufacturer}
						<li{if $layered_use_checkboxes} class="nomargin"{/if}>
						{if $layered_use_checkboxes}<input type="checkbox" class="checkbox" name="layered_manufacturer_{$layered_manufacturer.id_manufacturer}" id="layered_manufacturer_{$layered_manufacturer.id_manufacturer}" value="{$layered_manufacturer.id_manufacturer}"{if isset($layered_manufacturer.checked) && $layered_manufacturer.checked} checked="checked"{/if}{if !$layered_manufacturer.n} disabled="disabled"{/if} /> {/if}
						<label for="layered_manufacturer_{$layered_manufacturer.id_manufacturer}"{if !$layered_manufacturer.n} class="disabled"{/if}>{$layered_manufacturer.name|escape:html:'UTF-8'}<span> ({$layered_manufacturer.n})</span></label></li>
					{/foreach}
					</ul>
				</div>
				{/if}
				{if isset($layered_attributes) && sizeof($layered_attributes)}
				<div>
				{foreach from=$layered_attributes key=id_attribute_group item=layered_attribute_group}				
					<span class="layered_subtitle">{$layered_attribute_group.name|escape:html:'UTF-8'}</span>
					<span class="layered_close"><a href="#" rel="layered_attributes_{$id_attribute_group}">v</a></span>
					<div class="clear"></div>
					<ul id="layered_attributes_{$id_attribute_group}">
					{foreach from=$layered_attribute_group.values key=id_attribute item=layered_attribute_value}
						<li{if $layered_use_checkboxes} class="nomargin"{/if}>
						{if $layered_attribute_group.is_color_group}
							<input type="button" name="layered_attribute_{$id_attribute}" rel="{$id_attribute}" id="layered_attribute_{$id_attribute}" {if !$layered_attribute_value.n} value="X" disabled="disabled"{/if} style="background: {$layered_attribute_value.color}; margin-left: 0; width: 16px; height: 16px; padding:0; border: 1px solid {if isset($layered_attribute_value.checked) && $layered_attribute_value.checked}red{else}#666{/if};" />
						{else}
							{if $layered_use_checkboxes}<input type="checkbox" class="checkbox" name="layered_attribute_{$id_attribute}" id="layered_attribute_{$id_attribute}" value="{$id_attribute}"{if isset($layered_attribute_value.checked) && $layered_attribute_value.checked} checked="checked"{/if}{if !$layered_attribute_value.n} disabled="disabled"{/if} /> {/if}							
						{/if}
						<label for="layered_attribute_{$id_attribute}"{if !$layered_attribute_value.n} class="disabled"{/if}>{$layered_attribute_value.name|escape:html:'UTF-8'}<span> ({$layered_attribute_value.n})</span></label></li>
					{/foreach}
					</ul>
				{/foreach}
				</div>
				{/if}
				{if isset($layered_features) && sizeof($layered_features)}
				<div>
				{foreach from=$layered_features key=id_feature item=layered_feature}
					<span class="layered_subtitle">{$layered_feature.name|escape:html:'UTF-8'}</span>
					<span class="layered_close"><a href="#" rel="layered_features_{$id_feature}">v</a></span>
					<div class="clear"></div>
					<ul id="layered_features_{$id_feature}">
					{foreach from=$layered_feature.values key=id_feature_value item=layered_feature_value}
						<li{if $layered_use_checkboxes} class="nomargin"{/if}>
						{if $layered_use_checkboxes}<input type="checkbox" class="checkbox" name="layered_feature_{$id_feature_value}" id="layered_feature_{$id_feature_value}" value="{$id_feature_value}"{if isset($layered_feature_value.checked) && $layered_feature_value.checked} checked="checked"{/if}{if !$layered_feature_value.n} disabled="disabled"{/if} /> {/if}
						<label for="layered_feature_{$id_feature_value}"{if !$layered_feature_value.n} class="disabled"{/if}>{$layered_feature_value.name|escape:html:'UTF-8'}<span> ({$layered_feature_value.n})</span></label></li>
					{/foreach}
					</ul>
				{/foreach}
				</div>
				{/if}
				{if isset($layered_conditions) && sizeof($layered_conditions)}
				<div>
					<span class="layered_subtitle">{l s='Condition' mod='blocklayered'}</span>
					<span class="layered_close"><a href="#" rel="layered_conditions">v</a></span>
						<div class="clear"></div>
					<ul id="layered_conditions">
					{foreach from=$layered_conditions key=layered_value item=layered_condition}
						<li{if $layered_use_checkboxes} class="nomargin"{/if}>
						{if $layered_use_checkboxes}<input type="checkbox" class="checkbox" name="layered_condition_{$layered_value}" id="layered_condition_{$layered_value}" value="{$layered_value}"{if isset($layered_condition.checked) && $layered_condition.checked} checked="checked"{/if}{if !$layered_condition.n} disabled="disabled"{/if} /> {/if}
						<label for="layered_condition_{$layered_value}"{if !$layered_condition.n} class="disabled"{/if}>{$layered_condition.name|escape:html:'UTF-8'}<span> ({$layered_condition.n})</span></label></li>
					{/foreach}
					</ul>
				</div>
				{/if}
				<p>					
					<input type="hidden" name="id_category_layered" value="{$id_category_layered}" />
					{foreach from=$layered_attributes key=id_attribute_group item=layered_attribute_group}
						{foreach from=$layered_attribute_group.values key=id_attribute item=layered_attribute_value}
							{if $layered_attribute_group.is_color_group && $layered_attribute_value.n && isset($layered_attribute_value.checked) && $layered_attribute_value.checked}
								<input type="hidden" name="layered_attribute_{$id_attribute}" value="{$id_attribute}" />
							{/if}
						{/foreach}
						</ul>
					{/foreach}
				</p>
			</div>
		</form>
	</div>
	<div id="layered_ajax_loader" style="display: none;">
		<p style="margin: 20px 0; text-align: center;"><img src="{$img_ps_dir}loader.gif" alt="" /><br />{l s='Loading...' mod='blocklayered'}</p>
	</div>
</div>
<!-- /Block layered navigation module -->