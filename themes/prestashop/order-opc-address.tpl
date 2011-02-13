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
*  International Registered Trademark & Property of PrestaShop SA
*}

<!-- Address block (only for logged customer) -->
<h2>1. {l s='Addresses'}</h2>
<div id="opc_block_1" class="opc_block_content">
		<div class="addresses">
		<script type="text/javascript">
		 // <![CDATA[
			{foreach from=$addresses key=k item=address}
				addresses[{$address.id_address|intval}] = new Array('{$address.company|addslashes}', '{$address.firstname|addslashes}', '{$address.lastname|addslashes}', '{$address.address1|addslashes}', '{$address.address2|addslashes}', '{$address.postcode|addslashes}', '{$address.city|addslashes}', '{$address.country|addslashes}', '{$address.state|default:''|addslashes}');
			{/foreach}
		//]]>
		</script>
		<p class="address_delivery select">
			<label for="id_address_delivery">{l s='Choose a delivery address:'}</label>
			<select name="id_address_delivery" id="id_address_delivery" class="address_select" onchange="updateAddressesDisplay();">
			{foreach from=$addresses key=k item=address}
				<option value="{$address.id_address|intval}" {if $address.id_address == $cart->id_address_delivery}selected="selected"{/if}>{$address.alias|escape:'htmlall':'UTF-8'}</option>
			{/foreach}
			</select>
		</p>
		<p class="checkbox">
			<input type="checkbox" name="same" id="addressesAreEquals" value="1" onclick="updateAddressesDisplay();" {if $cart->id_address_invoice == $cart->id_address_delivery || $addresses|@count == 1}checked="checked"{/if} />
			<label for="addressesAreEquals">{l s='Use the same address for billing.'}</label>
		</p>
		<p id="address_invoice_form" class="select" {if $cart->id_address_invoice == $cart->id_address_delivery}style="display: none;"{/if}>
		{if $addresses|@count > 1}
			<label for="id_address_invoice" class="strong">{l s='Choose a billing address:'}</label>
			<select name="id_address_invoice" id="id_address_invoice" class="address_select" onchange="updateAddressesDisplay();">
			{section loop=$addresses step=-1 name=address}
				<option value="{$addresses[address].id_address|intval}" {if $addresses[address].id_address == $cart->id_address_invoice && $cart->id_address_delivery != $cart->id_address_invoice}selected="selected"{/if}>{$addresses[address].alias|escape:'htmlall':'UTF-8'}</option>
			{/section}
			</select>
			{else}
				<a style="margin-left: 221px;" href="{$link->getPageLink('address.php', true)}?back=order-opc.php{if isset($back) && $back}&mod={$back}{/if}" title="{l s='Add'}" class="button_large">{l s='Add a new address'}</a>
			{/if}
		</p>
		<div class="clear"></div>
		<ul class="address item" id="address_delivery">
			<li class="address_title">{l s='Your delivery address'}</li>
			<li class="address_company"></li>
			<li class="address_name"></li>
			<li class="address_address1"></li>
			<li class="address_address2"></li>
			<li class="address_city"></li>
			<li class="address_country"></li>
			<li class="address_update"><a href="{$link->getPageLink('address.php', true)}?id_address={$address.id_address|intval}&amp;back=order-opc.php{if isset($back) && $back}&mod={$back}{/if}" title="{l s='Update'}">{l s='Update'}</a></li>
		</ul>
		<ul class="address alternate_item" id="address_invoice">
			<li class="address_title">{l s='Your billing address'}</li>
			<li class="address_company"></li>
			<li class="address_name"></li>
			<li class="address_address1"></li>
			<li class="address_address2"></li>
			<li class="address_city"></li>
			<li class="address_country"></li>
			<li class="address_update"><a href="{$link->getPageLink('address.php', true)}?id_address={$address.id_address|intval}&amp;back=order-opc.php{if isset($back) && $back}&mod={$back}{/if}" title="{l s='Update'}">{l s='Update'}</a></li>
		</ul>
		<br class="clear" />
		<p class="address_add submit">
			<a href="{$link->getPageLink('address.php', true)}?back=order-opc.php{if isset($back) && $back}&mod={$back}{/if}" title="{l s='Add'}" class="button_large">{l s='Add a new address'}</a>
		</p>
	</div>
	
	<div>
		<div style="float:right;"><a href="{if $isVirtualCart}#opc_block_3{else}#opc_block_2{/if}" class="exclusive opc_button">{l s='Continue'}</a></div>
	</div>
	<div class="clear"></div>
</div>
<div id="opc_block_1_status" class="opc_status">
	<p>{l s='Delivery address:'} "<span id="opc_status-address_delivery">{l s='My address'}</span>" {l s='has been selected'}</p>
	<p>{l s='Invoice address:'} "<span id="opc_status-address_invoice">{l s='My address'}</span>" {l s='has been selected'}</p>
</div>
<!-- END Address block -->