{*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<script type="text/javascript">
	// Global JS Value
	var PS_MRPreSelectedRelay = '{$preSelectedRelay}';
</script>

{if $MR_PS_VERSION < '1.5'}
<script type="text/javascript">
	$(document).ready(function()
		{literal}{{/literal}
		// Bind id_carrierX to an ajax call
		{foreach from=$carriersextra item=carrier name=myLoop}
			$('#id_carrier' + {$carrier.id_carrier}).click(function()
				{literal}{{/literal}
				PS_MRCarrierSelectedProcess($(this), {$carrier.id_carrier}, '{$carrier.dlv_mode}');
				{literal}}{/literal});
			PS_MRCarrierMethodList[{$carrier.id_carrier}] = {$carrier.id_mr_method};
			if ($('#id_carrier' + {$carrier.id_carrier}).attr('checked'))
			{literal}{{/literal}
			PS_MRCarrierSelectedProcess($('#id_carrier' + {$carrier.id_carrier}), {$carrier.id_carrier}, '{$carrier.dlv_mode}');
			{literal}}{/literal}
		{/foreach}
		// Handle input click of the other input to hide the previous relay point list displayed
		$('input[name=id_carrier]').click(function()
			{literal}{{/literal}
			// Hide MR input if one of them is not selected
			if (PS_MRCarrierMethodList[$(this).val()] == undefined)
				PS_MRHideLastRelayPointList();
			{literal}}{/literal})
		{literal}}{/literal});
</script>
{* 1.5 way *}
	{elseif $MR_carrier}
<script type="text/javascript">
	$(document).ready(function() {literal}{{/literal}
		var carrier_block = $('input[class=delivery_option_radio]:checked').parent('div.delivery_option');

		// Simulate 1.4 table to store the relay point fetched
		$(carrier_block).append(
			'<div><table width="' + $(carrier_block).width() + '"><tr>'
				+	  '<td><input type="hidden" id="id_carrier' + {$MR_carrier.id_carrier} + '" value="{$MR_carrier.id_carrier}" /></td>'
				+ '</tr></table></div>');

		PS_MRCarrierMethodList[{$MR_carrier.id_carrier}] = {$MR_carrier.id_mr_method};
		PS_MRCarrierSelectedProcess($('#id_carrier' + {$MR_carrier.id_carrier}), {$MR_carrier.id_carrier}, "{$MR_dlv_mode}");
		{literal}}{/literal});
</script>
	{else}
	{l s='Mondial relay can\'t fetch any replay point due to prestashop error' mod='mondialrelay'}
{/if}
